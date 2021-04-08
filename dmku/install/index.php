<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);

define("CONFIG_PATH", '../config.inc.php');
define("INDEX_PATH", dirname(__DIR__));

$config = include_once(CONFIG_PATH);
$config_text = file_get_contents(CONFIG_PATH);
$type = ['pdo', 'mysqli', 'pdo', 'sqlite3'];
if (empty($config)) {
    die("读取配置文件失败");
}

if ($config['安装']) {
    die("非法访问");
}

session_start();

if (empty($_SESSION['sign'])) {
    $_SESSION['sign'] = get_hash();
}

$openssl = 0;

if (function_exists('openssl_random_pseudo_bytes')) {
    $openssl = 1;
}

$pdo = 0;

if (class_exists('PDO')) {
    $pdo = 1;
}

$mysqli = 0;
if (class_exists('mysqli')) {
    $mysqli = 1;
}

$sqlite3 = 0;
if (class_exists('SQLite3')) {
    $sqlite3 = 1;
}
//----------------------------------------------------
$query = <<<EOF

SET NAMES utf8;
SET time_zone = '+08:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE IF NOT EXISTS `danmaku_report` (
  `cid` int(8) NOT NULL COMMENT '弹幕ID',
  `id` varchar(128) NOT NULL COMMENT '弹幕池id',
  `text` varchar(128) NOT NULL COMMENT '举报内容',
  `type` varchar(128) NOT NULL COMMENT '举报类型',
  `time` varchar(128) NOT NULL COMMENT '举报时间',
  `ip` varchar(12) NOT NULL COMMENT '发送弹幕的IP地址',
  PRIMARY KEY (`text`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `danmaku_ip` (
  `ip` varchar(12) NOT NULL COMMENT '发送弹幕的IP地址',
  `c` int(1) NOT NULL DEFAULT '1' COMMENT '规定时间内的发送次数',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `danmaku_list` (
  `id` varchar(32) NOT NULL COMMENT '弹幕池id',
  `cid` int(8) NOT NULL AUTO_INCREMENT COMMENT '弹幕id',
  `type` varchar(128) NOT NULL COMMENT '弹幕类型',
  `text` varchar(128) NOT NULL COMMENT '弹幕内容',
  `color` varchar(128) NOT NULL COMMENT '弹幕颜色',
  `size` varchar(128) NOT NULL COMMENT '弹幕大小',
  `videotime` float(24,3) NOT NULL COMMENT '时间点',
  `ip` varchar(128) NOT NULL COMMENT '用户ip',
  `time` int(10) NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`cid`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

EOF;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_SESSION['sign'] != $_GET['hash']) {
        die("非法请求");
    }

    if ($_POST['dbtype'] == 3 or $_POST['dbtype'] == 4) {
        zq_sqlite($type[$_POST['dbtype'] - 1]);
        session_destroy();
        die("安装成功，请访问你的域名/admin进入后台！");
    }

    check();
    zq_mysql($type[$_POST['dbtype'] - 1]);
    session_destroy();
    die("安装成功，请访问你的域名/admin进入后台！");
}


function get_hash()
{
    global $openssl;
    if ($openssl) {
        $bytes = openssl_random_pseudo_bytes(20);
        $hash = md5(bin2hex($bytes));
        return md5($hash);
    } else {
        return md5(hash('sha256', md5(mt_rand(100000000, 999999999999))));
    }
}

function zq_mysql($type)
{
    global $config_text, $query;
    $hostname = $_POST['host'];
    $db = $_POST['dbname'];
    $username = $_POST['user'];
    $password = $_POST['pwd'];

    if ($type == 'pdo') {
        try {

            $sql = new PDO("mysql:host=$hostname;dbname=$db;", $username, $password);
            $sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql->exec($query);
            $sql = null;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    if ($type == 'mysqli') {
        $sql = new mysqli($hostname, $username, $password, $db);
        if ($sql->connect_error) {
            die("连接失败: " . $sql->connect_error);
        }

        if ($sql->query($query) !== TRUE) {
            die("创建数据表错误: " . $sql->error);
        }
        $sql->close();
    }


    $hostname = addslashes($hostname);
    $db = addslashes($db);
    $username =  addslashes($username);
    $password = addslashes($password);

    $config_text = preg_replace("/'方式' \=\> '(.*?)'/", "'方式' => '$type'", $config_text);
    $config_text = preg_replace("/'类型' \=\> '(mysql|sqlite)'/", "'类型' => 'mysql'", $config_text);
    $config_text = preg_replace("/'地址' \=\> '(.*?)'/", "'地址' => '$hostname'", $config_text);
    $config_text = preg_replace("/'用户名' \=\> '(.*?)'/", "'用户名' => '$username'", $config_text);
    $config_text = preg_replace("/'密码' \=\> '(.*?)'/", "'密码' => '$password'", $config_text);
    $config_text = preg_replace("/'名称' \=\> '(.*?)'/", "'名称' => '$db'", $config_text);
    $config_text = preg_replace("/'安装' \=\> 0/", "'安装' => 1", $config_text);
    file_put_contents(CONFIG_PATH, $config_text, LOCK_EX);
}

function zq_sqlite($type)
{
    global $config_text;
    $name = get_hash() . '.db';
    $path = INDEX_PATH . '/db/' . $name;
    @mkdir(INDEX_PATH . '/db', 0700);
    copy(__DIR__ . '/sql.db', $path);
    $config_text = preg_replace("/'方式' \=\> '(.*?)'/", "'方式' => '$type'", $config_text);
    $config_text = preg_replace("/'类型' \=\> '(mysql|sqlite)'/", "'类型' => 'sqlite'", $config_text);
    $config_text = preg_replace("/'地址' \=\> '(.*?)'/", "'地址' => '$name'", $config_text);
    $config_text = preg_replace("/'安装' \=\> 0/", "'安装' => 1", $config_text);
    file_put_contents(CONFIG_PATH, $config_text, LOCK_EX);
    //echo $config_text;
}

function check()
{
    if ($_POST['dbtype'] === 1 or $_POST['dbtype'] === 2) {
        if (empty($_POST['host'])) die("参数错误");
        if (empty($_POST['user'])) die("参数错误");
        if (empty($_POST['pwd'])) die("参数错误");
        if (empty($_POST['dbname'])) die("参数错误");
    }
    if ($_POST['dbtype'] > 4 or $_POST['dbtype'] <= 0) die("参数错误");
}
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>安装</title>
       <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">  
   <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
   <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="../../admin/js/style.css">
  <!--[if lt IE 9]>
    <script src="/n.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="/n.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body><div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block " style="float: none;">
     <div class="block">
         
                 <div class="block-title">
            <h2><b>确认您的配置</b></h2>
        </div>
        
        
    <form method="post" action="index.php?hash=<?php echo $_SESSION['sign']; ?>">

        <div>
            <h2>数据库配置</h2>

           <div class="form-group">
                    <label>数据库适配器</label>
                    <select name="dbtype" id="dbtype" class="form-control">
                        <?php
                        if ($pdo) echo '<option value="1">Pdo 驱动 Mysql 适配器</option>';
                        if ($mysqli) echo '<option value="2">Mysqli 驱动 Mysql 适配器</option>';
                        if ($pdo) echo '<option value="3">Pdo 驱动 SQLite 适配器</option>';
                        if ($sqlite3) echo '<option value="4">SQLite3 驱动 SQLite 适配器</option> ';
                        ?>
                    </select>
                    <p class="help-block">
                        请根据您的数据库类型选择合适的适配器(列出来的都是服务器支持的)
                    </p>
          </div>


                <div id="mysql">
                    
                    <div class="form-group">
                                             <label>数据库地址</label>
                        <input type="text"  class="form-control" name="host" value="localhost">
                        <p class="help-block">
                            您可能会使用 "localhost"
                        </p>
                    </div>
                    
                    <div class="form-group">
                                                <label>数据库用户名</label>
                        <input type="text"  class="form-control" name="user" value="root">
                        <p class="help-block">
                            您可能会使用 "root"
                        </p>
                    </div>
                    
                    <div class="form-group">
                                                <label>数据库密码</label>
                        <input type="password"  class="form-control" name="pwd" value="">
                    </div>
                    
                    <div class="form-group">
                                              <label>数据库名</label>
                        <input type="text"  class="form-control" name="dbname" value="">
                        <p class="help-block">
                            请您指定数据库名称
                        </p>  
                    </div>
                    
                    
                    
                </div>




            <script>
                $(document).ready(function() {
                    $("#dbtype").change(function() {
                        if ($(this).val() == 1 || $(this).val() == 2) {
                            $("#mysql").show();
                        }

                        if ($(this).val() == 3 || $(this).val() == 4) {
                            $("#mysql").hide();
                        }
                    })
                });
            </script>



        </div>
         <div class="form-group">
              <input type="submit" value="确认, 开始安装" class="btn btn-primary btn-block"/>
            </div>
    </form>
</div></div>
</body>

</html>
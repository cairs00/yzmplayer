<?php
// 判断修改权限
$_config = require_once('../dmku/config.inc.php');
error_reporting(0);
$pass = $_POST;

$username = $_config['username'];
$password = $_config['后台密码'];

$cookielock = md5($username . $password);


if ($_COOKIE["zt"] !== $cookielock) {
    echo '{"code":-1, "msg":"拒绝访问"}';
    exit;
}

if (isset($_GET['act']) && $_GET['act'] == 'reset') {

    $file = 'yzm.config.php'; //旧目录
    $newFile = 'bak/config.php'; //新目录
    copy($newFile, $file); //拷贝到新目录
    //unlink($file); //删除旧目录下的文件
    echo '{"code":1, "msg":"还原成功"}';
    exit;
}
if (isset($_GET['act']) && $_GET['act'] == 'setting' && isset($_POST['edit']) && $_POST['edit'] == 1) {
    $datas = $_POST;
    $data = $datas['yzm'];

    if (file_put_contents('data.php', "<?php\n \$yzm =  " . var_export($data, true) . ";\n?>")) {
        echo '{"code":1, "msg":"保存成功"}';
    } else {
        echo "<script>alert('修改失败！可能是文件权限问题，请给予yzm.config.php写入权限！');location.href='" . $_SERVER["HTTP_REFERER"] . "';</script>";
    }
    $yzm = $data;
}

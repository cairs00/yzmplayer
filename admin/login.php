<?php
$_config = require_once('../dmku/config.inc.php');
error_reporting(0); 
$pass= $_POST;

$username=$_config['username'];
$password = $_config['后台密码'];

$cookielock=md5($username.$password);


if($_COOKIE["zt"]!==$cookielock){
      if($pass[pass]=="" || $pass[pass]!=$password|| $pass[username]!=$username) {
          
          
 if (!empty($pass[pass])||!empty($pass[username])) {$ti="用户名或密码错误！";}


echo '
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>弹幕管理系统</title>
	<link rel="shortcut icon" href="js/favicon.png" type="image/x-icon">
   <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
   <link rel="stylesheet" href="js/style.css">
  <!--[if lt IE 9]>
    <script src="/n.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="/n.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block " style="float: none;">
  <br /><br /><br /><br /><br />
    <div class="widget">
<p></p>
    </div>

    <div class="block">
        <div class="block-title">
            <h2><b>弹幕管理系统</b></h2>
        </div>
'.$ti.'
          <form action="" method="post" role="form">
           <div class="form-group">
             <div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>
              <input type="text" name="username" class="form-control" required="required" autocomplete="off" placeholder="请输入用户名"/>
            </div></div>
             <div class="form-group">
            <div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></div>
              <input type="password" name="pass" class="form-control" required="required" autocomplete="off" placeholder="请输入密码"/>
            </div></div>
            <div class="form-group">
              <input type="submit" value="登录" class="btn btn-primary btn-block"/>
            </div>
</form>
    </div>
  </div>';
    die();
}else{
	//<br><center><h2>密码: '.$_COOKIE["pass"].'</h2></center>
    setcookie("zt", $cookielock, time()+86400);
}
}else{
  	
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" type="text/css" href="http://static.ebanhui.com/ebh/tpl/default/css/base.css" />
<link rel="stylesheet" type="text/css" href="http://static.ebanhui.com/ebh/tpl/2016/css/personal.css" />
<title>无标题文档</title>
</head>
<body>
<div style="float: left;font-family:'Microsoft YaHei';font-size: 14px;line-height: 1.8;width: 520px;">
    <p>亲爱的:<?=$username?>，您好！</p>
    <p>您绑定的邮箱：<?=$email?></p>
    <p>请于24小时内点击以下链接完成绑定，绑定后即可使用网络学校的所有功能啦！</p>
    <a href="<?=$href?>" style="color: #518bf7;" target="_blank"><?=$href?></a>
    <p style="color: #666;font-size: 12px;">以上链接有效期为24小时，请在有效期内点击链接完成验证。</p>
    <p style="color: #666;font-size: 12px;">如果以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。</p>
</div>
</body>
</html>

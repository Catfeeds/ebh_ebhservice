<?php
require_once  'PHPMailer/PHPMailerAutoload.php';
class EBHMailer extends PHPMailer{
	private $mailer = null;
	
	public function __construct(){
		parent::__destruct();
		if(!isset($this->mailer)){
			$this->mailer = new PHPMailer;
		}
	}

	/**
	 * 邮件发送
	 * @param  $toarr 邮件接受者 email->邮件地址 username->接收者姓名
	 * @param string $subject 邮件主题
	 * @param string $message 邮件内容
	 * @param unknown $fromarr 邮件发送方
	 * @return array status->0 发送成功 ->1 发送失败
	 */
	public function sendMessage($toarr,$subject="邮件主题",$message="",$fromarr=array()){
		//$mail = $this->mailer;
		$mail = new PHPMailer;
		$mailconfig = Ebh()->config->get('mailconfig');
		if(empty($mailconfig)){
			echo " Mailer Error: missing mailconfig.php file!";
			exit;
		}
		
	//	$mail->SMTPDebug = 3; 
		$mail->CharSet    ="UTF-8";                              // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $mailconfig['server'];  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $mailconfig['auth_username'];                 // SMTP username
		$mail->Password = $mailconfig['auth_password'];                           // SMTP password
		$mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
	//	$mail->SMTPSecure = 'ssl';
		$mail->Port = $mailconfig["port"];                                    // TCP port to connect to
		
		$mail->From = empty($fromarr['email'])?$mailconfig['from']:$fromarr['email'];
		$mail->FromName = empty($fromarr['username'])? $mailconfig['sitename']:$fromarr['username'];
		$mail->addAddress($toarr['email'], empty($toarr['username'])?"":$toarr['username']);     //收件人邮箱 姓名
		$mail->addReplyTo($mailconfig['adminemail'], $mailconfig['sitename']); // 回复地址(可填可不填)
	//	$mail->addCC('cc@example.com');
	//	$mail->addBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                  //设置每行字符长度
	//	$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//	$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  //是否HTML格式邮件
		
		$mail->Subject = $subject;//邮件主题
		$mail->Body    = $message; //邮件内容
	//	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';//邮件正文不支持HTML的备用显示
		
		if(!$mail->send()) {
			$retarr =   array('status'=>1,'msg'=>$mail->ErrorInfo);
			log_message(var_export($toarr,true)."\n".var_export($retarr,true));
		} else {
			$retarr =   array('status'=>0,'msg'=>'邮件发送成功');
		}
		
		return $retarr;
	}
	
}

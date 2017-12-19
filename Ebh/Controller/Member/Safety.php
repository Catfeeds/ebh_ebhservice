<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:15
 */
class SafetyController extends Controller{
    public function parameterRules(){
        return array(
            'sendMailAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'email'  =>  array('name'=>'email'),
            ),
            'bindMobileAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'mobile'  =>  array('name'=>'mobile','require'=>true),
            ),
            'checkMobileAction'   =>  array(
                'mobile'  =>  array('name'=>'mobile','require'=>true),
            ),
        );
    }

    /**
     * 校验手机是否存在
     * @return bool
     */
    public function checkMobileAction(){
        $bdModel = new BindModel();
        if( $bdModel->checkmobile($this->mobile)){
            return true;
        }else{
            return  false;
        }
    }

    /**
     * 绑定用户手机信息
     */
    public function bindMobileAction(){
        $bdModel = new BindModel();
        $bdata =array(
            'uid'=>$this->uid,
            'is_mobile'=>1,
            'mobile'=>$this->mobile,
            'mobile_str'=>json_encode(
                array('mobile'=>$this->mobile,
                    'uid'=>$this->uid,
                    'dateline'=>SYSTIME
                )
            )
        );
        $result = $bdModel->doBind($bdata,$this->uid);
        if($result){
            //绑定成功 修改用户手机信息
            $memberModel = new MemberModel();
            $memberModel->editMember(array('mobile'=>$this->mobile,'uid'=>$this->uid));
            return returnData(1,'修改成功');
        }
        return returnData(0,'修改失败');
    }
    /**
     * 发送绑定邮箱邮件
     */
    public function sendMailAction(){
        $bindModel = new BindModel();
        if($bindModel->checkemail($this->email)){
            return returnData(0,'该邮箱已绑定,请换一个邮箱试试');
        }
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        $emailer = new EBHMailer();
        $toarr = array('email'=>$this->email,'username'=>$user['username']);
        $subject = "e板会-邮箱验证";
        $message = $this->getEmailBIndTpl($this->email,$user);
        $retarr = $emailer->sendMessage($toarr,$subject,$message);
        if($retarr['status']==1){
            return returnData(0,'邮件发送失败,请刷新后重试');
        }
        return returnData(1,'邮件发送成功');
    }

    /**
     * 获取短信验证模板
     */
    public function getEmailBIndTpl($email,$user){
        $username = $user['username'];
        $href = $this->_getauthorurl($email,$user);
        $viewpath = APP_PATH . DIRECTORY_SEPARATOR. 'Views' . DIRECTORY_SEPARATOR .'safety_email_msg_tpl.php';
        $view_vars['username'] = $username;
        $view_vars['email'] = $email;
        $view_vars['href'] = $href;

        ob_start();
        extract($view_vars);
        include $viewpath;
        $outputstr = ob_get_contents();
        @ob_end_clean();
        //echo $outputstr;
        return  $outputstr;
    }

    /**
     * 获取验证url
     * @param unknown $email
     * @return string
     */
    private function _getauthorurl($email,$user){
        $uid = $user['uid'];
        $dateline = SYSTIME;
        $url = "http://www.ebh.net/homev2/safety/checkmail.html?codekey=";
        $codekey = urlencode(authcode($uid.'\t'.$email.'\t'.$dateline, 'ENCODE'));
        $url.=$codekey;
        log_message("$uid\t$email\t$dateline");
        log_message($url);
        return $url;
    }
}
<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:13
 * 第三方登录控制器
 * 该控制器用于第三方登录调度
 * 微信登录openid请传入unionid
 * 小程序登录openid请传入code,和相关的附加参数
 */
class OtherController extends Controller{
    public function parameterRules(){
        return array(
            'loginAction'   =>  array(
                'type'  =>  array('name'=>'type','require'=>true,'type'=>'enum','range'=>array('wxapp')), //第三方登录类型 wxapp=小程序
                'openid'  =>  array('name'=>'openid','require'=>true),
                'parameters'    =>  array('name'=>'parameters','default'=>array(),'type'=>'array')
            ),
            'bindAction'   =>  array(
                'type'  =>  array('name'=>'type','require'=>true,'type'=>'enum','range'=>array('wxapp')), //第三方登录类型 wxapp=小程序
                'openid'  =>  array('name'=>'openid','require'=>true),
                'parameters'    =>  array('name'=>'parameters','default'=>array(),'type'=>'array'),
                'username'  =>  array('name'=>'username','require'=>true),
                'password'  =>  array('name'=>'password','require'=>true),
            )
        );
    }

    /**
     * 第三方登录
     * @return array
     */
    public function loginAction(){
        $otherLoginLib = OtherLogin::getObj($this->type,$this->openid,$this->parameters);
        $user = $otherLoginLib->getUser();
        if(!$user){
            return returnData(0,$otherLoginLib->getErr());
        }
        $userModel = new UserModel();
        $result = $userModel->getUserById($user['uid']);
        return returnData(1,'',$result);
    }

    /**
     * 绑定用户
     * @return array
     */
    public function bindAction(){
        $userModel = new UserModel();
        $userInfo = $userModel->getUserByUsernameOrMobile($this->username);

        if(!$userInfo){
            return returnData(0,'用户不存在');
        }

        if($userInfo['password'] != md5($this->password)){
            return returnData(0,'用户密码错误');
        }
        $otherLoginLib = OtherLogin::getObj($this->type,$this->openid,$this->parameters);
        $result = $otherLoginLib->bindUser($userInfo['uid']);
        if(!$result){
            return returnData(0,$otherLoginLib->getErr());
        }
        return returnData(1,'',$userInfo);

    }
}
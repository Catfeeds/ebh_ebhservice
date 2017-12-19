<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:32
 */
class Balance implements Payment{
    private $config = array();
    public function __construct($config){
        $this->config = $config;
    }

    public function getPaymentCode($order,$parameters = array()){
        $uid = $order['uid'];
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($uid);
        if(!$user){
            throw new Exception_BadRequest('用户不存在');
        }

        if($user['balance'] < $order['totalfee']) {
            throw new Exception_BadRequest('用户余额不足');
        }
        $param['payip'] = $order['ip'];
        $doresult = Transaction::notifyOrder($order['orderid'],'',$param);

        if(!$doresult){
            throw new Exception_InternalServerError('开通失败');
        }

        $ubalance = $user['balance'] - $order['totalfee'];
        $uparam = array('balance'=>$ubalance);
        $uresult = $userModel->update($uparam,$user['uid']);
        $creditModel = new CreditModel();
        $creditModel->addCreditlog(array('ruleid'=>23,'detail'=>$order['itemlist'][0]['oname'],'uid'=>$uid,'crid'=>$order['crid']));
        return  array(
            'success'   =>  'ok'
        );
    }

    public function notify($request = array()){

    }
}
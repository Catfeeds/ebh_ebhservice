<?php
/**
 * ebhservice.
 * User: ycq
 */
class PayOrderController extends Controller{
    public function parameterRules(){
        return array(
            'getLatestOrderAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int')
            )
        );
    }
    /**
     * 获取刚刚支付的订单
     * @return mixed
     */
    public function getLatestOrderAction() {
        if ($this->uid < 1) {
            return false;
        }
        $model = new PayorderModel();
        return $model->getLatestPayedOrder($this->uid);
    }
}
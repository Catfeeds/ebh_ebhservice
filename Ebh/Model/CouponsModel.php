<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:12
 */
class CouponsModel{
    //新增一条优惠券
    public function add($param = array()){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['code'])){
            $setarr['code'] = $param['code'];
        }
        if(!empty($param['orderid'])){
            $setarr['orderid'] = $param['orderid'];
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = $param['crid'];
        }
        if(isset($param['fromtype'])){
            $setarr['fromtype'] = $param['fromtype'];
        }
        if(!empty($param['createtime'])){
            $setarr['createtime'] = $param['createtime'];
        }
        return Ebh()->db->insert('ebh_coupons',$setarr);
    }
    //获取优惠券详情
    public function getOne($param = array()) {
        $sql = 'SELECT * FROM `ebh_coupons` c';
        $wherearr = array();
        if(empty($param['uid']) && empty($param['code'])){
            return array();
        }
        if(!empty($param['uid'])){
            $wherearr[] = ' c.uid = '.$param['uid'];
        }
        if(!empty($param['code'])){
            $wherearr[] = ' c.code = '.Ebh()->db->escape($param['code']);
        }
        if(!empty($wherearr)) {
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }
        //echo $sql;
        return Ebh()->db->query($sql)->row_array();
    }

    //检测优惠券是否存在
    public function checkcoupon($coupon){
        $sql = "SELECT count(*) count FROM `ebh_coupons` WHERE code = '{$coupon}'";
        $row = Ebh()->db->query($sql)->row_array();
        if(!empty($row) && $row['count']>0){
            return true;
        }else{
            return false;
        }
    }


    //生成优惠码
    public function getcouponcode(){
        $couponcode = $this->generatestr();
        //检测是否重复

        $ck = $this->checkcoupon($couponcode);
        if($ck){
            $couponcode = $this->getcouponcode();
        }
        return $couponcode;
    }


    /**
     * 生成随机数
     * @param number $length
     * @return string
     */
    protected function generatestr( $length = 6 ){
        // 密码字符集，可任意添加你需要的字符
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
}
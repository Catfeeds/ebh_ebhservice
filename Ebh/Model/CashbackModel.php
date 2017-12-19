<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:57
 */
class CashbackModel{
    //新增一条返现记录
    public function add($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['reward'])){
            $setarr['reward'] = $param['reward'];
        }
        if(!empty($param['time'])){
            $setarr['time'] = $param['time'];
        }
        if(!empty($param['synctime'])){
            $setarr['synctime'] = $param['synctime'];
        }
        if(!empty($param['crname'])){
            $setarr['crname'] = $param['crname'];
        }
        if(!empty($param['fromcrid'])){
            $setarr['fromcrid'] = $param['fromcrid'];
        }
        if(!empty($param['fromuid'])){
            $setarr['fromuid'] = $param['fromuid'];
        }
        if(!empty($param['fromname'])){
            $setarr['fromname'] = $param['fromname'];
        }
        if(!empty($param['fromip'])){
            $setarr['fromip'] = $param['fromip'];
        }
        if(!empty($param['servicestxt'])){
            $setarr['servicestxt'] = $param['servicestxt'];
        }
        if(isset($param['status'])){
            $setarr['status'] = $param['status'];
        }
        return Ebh()->db->insert('ebh_cashback',$setarr);
    }
}
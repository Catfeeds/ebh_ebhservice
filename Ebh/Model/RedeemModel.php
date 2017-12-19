<?php

/**
 * 兑换码类
 * Created by PhpStorm.
 * User: tyt
 * Date: 2017/8/23
 * Time: 13:45
 */
class RedeemModel {
    /**
     * 添加兑换码
     * @param $crid 网校ID
     * @param $uid 发布者ID
     * @param $params 兑换码参数
     * @return bool
     */
    public function add($params) {
        $crid = (int) $params['crid'];
        if ($crid < 1 || empty($params['name']) || empty($params['price']) || empty($params['number']) || empty($params['folderid'])) {
            return false;
        }
        $formatParams = array();
        $formatParams['crid'] = $params['crid'];
        $formatParams['name'] = $params['name'];
        $formatParams['price'] = $params['price'];
        $formatParams['number'] = $params['number'];
        $formatParams['folderid'] = $params['folderid'];
        $formatParams['itemid'] = $params['itemid'];
        $formatParams['lotcode'] = $this->getLotNum($params['crid'],$params['folderid']);
        if (empty($params['effecttime'])) {
            $formatParams['effecttime'] = SYSTIME;
        } else {
            $formatParams['effecttime'] = $params['effecttime'];
        }
        $formatParams['dateline'] = SYSTIME;
        $resid = Ebh()->db->insert('ebh_redeem_lots', $formatParams);
        return $resid;
    }
    /**
     *获取批次号
     */
    public function getLotNum($crid,$folderid) {
        if (empty($crid) || empty($folderid)) {
            return '';
        }
        $pattern = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $cardpass = '';
        for ($i = 0; $i < 3; $i++) {
            $cardpass .= $pattern{mt_rand(0, 22)};
        }
        $checksql = 'select crid from ebh_redeem_lots where crid='.$crid.' and folderid='.$folderid.' and lotcode=\''.$cardpass.'\'';
        while (Ebh()->db->query($checksql)->row_array()) {
            $cardpass = '';
            for ($i = 0; $i < 3; $i++) {
                $cardpass .= $pattern{mt_rand(0, 22)};
            }
            $checksql = 'select crid from ebh_redeem_lots where crid='.$crid.' and folderid='.$folderid.' and lotcode=\''.$cardpass.'\'';
        }
        return $cardpass;
    }

    /**
     *获取唯一的序列号
     *激活码为:123456789012 一共10位激活码不允许重复
     *激活码去除易混淆的字符,如: 1,l,L,0 ,Oo剔除
     */
    public function getUniredeEmnumber($crid) {
        $pattern = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $cardpass = '';
        for ($i = 0; $i < 7; $i++) {
            $cardpass .= $pattern{mt_rand(0, 22)};
        }
        $checksql = 'select redeemnumber from ebh_redeem_cards where redeemnumber=\''.$cardpass.'\''.' and crid='.$crid;
        while (Ebh()->db->query($checksql)->row_array()) {
            $cardpass = '';
            for ($i = 0; $i < 7; $i++) {
                $cardpass .= $pattern{mt_rand(0, 22)};
            }
            $checksql = 'select redeemnumber from ebh_redeem_cards where  redeemnumber=\''.$cardpass.'\''.' and crid='.$crid;
        }
        return $cardpass;
    }

     /**
     * 兑换码列表
     * @param $filterParams 过滤条件
     * @param int $limit 限制条件
     * @param bool $setKey 是否以itemid作为键值
     * @return mixed
     */
    public function getListAndTotalPage($filterParams, $limit = 20, $setKey = true) {
        $params = array();
        if(!empty($filterParams['redeemnumber']))
            $params[] = ' (r.redeemnumber like \'%'. Ebh()->db->escape_str($filterParams['redeemnumber']) .'%\')';
        if(!empty($filterParams['name']))
            $params[] = ' (l.name like \'%'. Ebh()->db->escape_str($filterParams['name']) .'%\')';
        if(!empty($filterParams['foldername']))
            $params[] = ' (i.iname like \'%'. Ebh()->db->escape_str($filterParams['foldername']) .'%\')';
        if(!empty($filterParams['folderid']))
            $params[] = 'l.folderid ='.intval($filterParams['folderid']);
        if(!empty($filterParams['cardid']))
            $params[] = 'r.redeemid ='.intval($filterParams['cardid']);
        if(!empty($filterParams['crid']))
            $params[] = 'l.crid ='.intval($filterParams['crid']);
        if(isset($filterParams['status'])){
            if (empty($filterParams['lot'])) {
                $params[] = 'r.status='.intval($filterParams['status']);
            } else {
                $params[] = 'l.status='.intval($filterParams['status']);
            }
            
        }
        if(!empty($filterParams['q']) ) {
            if (empty($filterParams['lot'])) {
                 $params[] = ' (r.redeemnumber like \'%'. Ebh()->db->escape_str($filterParams['q']) .'%\' or l.name like \'%'. Ebh()->db->escape_str($filterParams['q']) .'%\' or i.iname like \'%'. Ebh()->db->escape_str($filterParams['q']) .'%\')';
             } else {
                $params[] = ' (l.name like \'%'. Ebh()->db->escape_str($filterParams['q']) .'%\' or i.iname like \'%'. Ebh()->db->escape_str($filterParams['q']) .'%\')';
             }
           
        }
        if (empty($filterParams['lot'])) {//条形码
            $sql = 'select i.iname as foldername,i.iprice as fprice,r.cardid,r.redeemid,r.uid,r.redeemnumber,r.usetime,r.status as rstatus,r.crid,l.name,l.effectnumber,l.refundnumber,l.lotid,l.lotcode,l.folderid,l.price,l.number,l.effecttime,l.status as lstatus,l.dateline as ldateline from ebh_redeem_cards r left join ebh_redeem_lots l on l.lotid=r.redeemid left join ebh_pay_items i on i.itemid=l.itemid ';

            $countsql = 'select count(1) as c from ebh_redeem_cards r left join ebh_redeem_lots l on l.lotid=r.redeemid left join ebh_folders f on l.folderid=f.folderid ';
        } else {//批次
             $sql = 'select i.iname as foldername,i.iprice as fprice,l.lotid,l.name,l.lotid,l.status,l.lotcode,l.effectnumber,l.refundnumber,l.folderid,l.price,l.number,l.effecttime,l.dateline as ldateline from ebh_redeem_lots l left join ebh_pay_items i on i.itemid=l.itemid ';

            $countsql = 'select count(1) as c from ebh_redeem_lots l';
        }
        $params[] = 'l.status<>0 ';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
            $countsql .= ' WHERE '.implode(' AND ', $params);
        }
        //算出总的数量
        $count = Ebh()->db->query($countsql)->row_array();

        $offset = 0;
        $pagesize = 20;
        if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
                $offset = ($page - 1) * $pagesize;
            }
        } else {
            $pagesize = max(1, intval($limit));
        }
        $sql .= ' order by l.lotid desc limit '.$offset.','.$pagesize;
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('id');
        }
        $res['list'] = Ebh()->db->query($sql)->list_array();
        if (!empty($filterParams['cardid']) && !empty($res['list'])) {
            $res['effectnumber'] = $res['list'][0]['effectnumber'];
            $res['refundnumber'] = $res['list'][0]['refundnumber'];
        }
        $res['totalpage'] = $count['c'];
        return $res;
    }

    /**
     *教师退款
     */
    public function refund($param = array()) {
        if (!empty($param['lotid'])) {//
            $sql = 'select status,number,effectnumber,refundnumber from ebh_redeem_lots where status=2 lotid = '.$param['lotid'];
            $lotinfo = Ebh()->db->query($sql)->row_array();
            if (empty($lotinfo) || !empty($lotinfo['refundnumber'])) {//已经退款的不给退了
                return false;
            }
            $param['lot'] = 1;
            return $this->doRefund($param,$lotinfo);
            
        } else if (!empty($param['cardid'])) {
            $sql = 'select l.lotid,l.price,l.number,l.effectnumber,l.refundnumber,c.usetime,c.redeemnumber from ebh_redeem_lots l left join ebh_redeem_cards c on l.lotid = c.redeemid where l.status=2 and c.status=0 and c.cardid = '.$param['cardid'];
            $lotinfo = Ebh()->db->query($sql)->row_array();
            if (empty($lotinfo) || !empty($lotinfo['usetime'])) {//用过了不给退
                return false;
            }
            $param['lot'] = 2;
            $param['lotid'] = $lotinfo['lotid'];
            return $this->doRefund($param,$lotinfo);
        } else if (!empty($param['cardidstr'])) {
            $idarr = explode(',', $param['cardidstr']);
            if (!empty($idarr)) {
                $sql = 'select c.cardid,l.name,l.lotcode,l.lotid,l.price,l.number,l.effectnumber,l.refundnumber,c.usetime,c.redeemnumber from ebh_redeem_lots l left join ebh_redeem_cards c on l.lotid = c.redeemid where l.status=2 and c.status=0 and c.cardid in('.$param['cardidstr'].')';
                $lotinfo = Ebh()->db->query($sql)->list_array();
                if (empty($lotinfo)) {//用过了不给退
                    return false;
                }
                $paysql = 'select redeemcode,ordername,crid,dateline,paytime,payfrom,uid,ip,payip,paycode,totalfee,remark,status,refunded,invalid,buyer_id,buyer_info,out_trade_no,itype,isbatchrefund,batchid,ptype from ebh_pay_torders where status=1 and itype=1 and isbatchrefund=0 and batchid ='.$lotinfo[0]['lotid'];
                $resArr = Ebh()->db->query($paysql)->list_array();
                $money = 0;
                Ebh()->db->begin_trans();
                if (!empty($resArr) && count($resArr) == 1) {
                    $pay_info = $resArr[0];
                    $pay_info['ptype'] = 3;//退押金
                    $pay_info['isbatchrefund'] = 2;//批量退款1,单个2
                    $pay_info['totalfee'] = $lotinfo[0]['price'];
                    $pay_info['dateline'] = time();
                    foreach ($lotinfo as $key => $value) {
                        if ($value['usetime']) {
                            Ebh()->db->rollback_trans();
                            return false;
                        }
                        $pay_info['ordername'] = $value['redeemnumber'];//兑换码
                        $pay_info['batchid'] = $value['cardid'];
                        $money += $value['price'];
                        $this->addOrder($pay_info);
                    }
                    $setarr['refundnumber'] = $lotinfo[0]['refundnumber']+count($lotinfo);
                    Ebh()->db->query('update ebh_redeem_cards set status=-1 where cardid in('.$param['cardidstr'].')');
                    Ebh()->db->update('ebh_redeem_lots',$setarr,array('lotid'=>$lotinfo[0]['lotid']));
                    Ebh()->db->query('update ebh_redeem_lots set status=-1 where number=refundnumber and lotid in('.$param['cardidstr'].')');
                    //退钱
                    $sqltouser = "update ebh_users set balance = balance + $money where uid =".intval($pay_info['uid']);
                    Ebh()->db->query($sqltouser);
                    $pay_info['name'] = $lotinfo[0]['lotcode'].'退换'.count($idarr).'个兑换码';//兑换码名称
                    $pay_info['money'] = $money;
                    $this->addCharge($pay_info);
                    if(Ebh()->db->trans_status() === FALSE){
                        Ebh()->db->rollback_trans();
                        return false;
                    }else{
                        Ebh()->db->commit_trans();
                        return true;
                    } 
                }
            }

        } else {
            return false;
        }

    }

    /**
     *兑换码退款操作
     */
    public function doRefund($param,$lotinfo=array()) {
        if (empty($param['lotid']) || empty($lotinfo) || empty($param['lot'])) {
            return false;
        }
        $paysql = 'select redeemcode,ordername,crid,dateline,paytime,payfrom,uid,ip,payip,paycode,totalfee,remark,status,refunded,invalid,buyer_id,buyer_info,out_trade_no,itype,isbatchrefund,batchid,ptype from ebh_pay_torders where status=1 and itype=1 and isbatchrefund=0 and batchid='.$param['lotid'];
        //有支付信息，并且没有客服或者自己退款，就给退款
        $resArr = Ebh()->db->query($paysql)->list_array();
        Ebh()->db->begin_trans();
        if (count($resArr) == 1) {//多条说明已退款
            $pay_info = $resArr[0];
            $pay_info['ptype'] = 3;//退押金
            $pay_info['dateline'] = time();
            $pay_info['batchid'] = $param['lot'] == 2 ? $param['cardid'] : $param['lotid'];
            $pay_info['isbatchrefund'] = $param['lot'];//批量退款1,单个2
            if ($param['lot'] == 1) {
                //更新使得批次弃用
                $setarr['status'] = -1;
                $money = $pay_info['totalfee'];
                $pay_info['name'] = $lotinfo['name'];//兑换码名称
                $setarr['refundnumber'] = $lotinfo['number'];
                Ebh()->db->query('update ebh_redeem_cards set status=-1 where redeemid='.$param['lotid']);
                Ebh()->db->update('ebh_redeem_lots',$setarr,array('lotid'=>$param['lotid']));
            } else {
                $money = $lotinfo['price'];
                $pay_info['totalfee'] = $lotinfo['price'];
                $pay_info['ordername'] = $lotinfo['redeemnumber'];//兑换码
                $setarr['refundnumber'] = $lotinfo['refundnumber']+1;
                if ($setarr['refundnumber'] == $lotinfo['number']) {//弃用
                    $setarr['status'] = -1; 
                }
                Ebh()->db->query('update ebh_redeem_cards set status=-1 where cardid='.$param['cardid']);
                Ebh()->db->update('ebh_redeem_lots',$setarr,array('lotid'=>$param['lotid']));
            }
            //记录
            $this->addOrder($pay_info);
            //退钱
            $sqltouser = "update ebh_users set balance = balance + $money where uid =".intval($pay_info['uid']);
            Ebh()->db->query($sqltouser);
            $pay_info['money'] = $money;
            $this->addCharge($pay_info);
            if(Ebh()->db->trans_status() === FALSE){
                Ebh()->db->rollback_trans();
                return false;
            }else{
                Ebh()->db->commit_trans();
                return true;
            }   
        }
    }

    /**
    *生成订单信息
    */
    public function addOrder($param = array()) {
        if(empty($param))
            return false;
        $setarr = array();
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['ordername']))
            $setarr['ordername'] = $param['ordername'];
        if(!empty($param['uid']))
            $setarr['uid'] = $param['uid'];
        if(!empty($param['paytime']))
            $setarr['paytime'] = $param['paytime'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        if(!empty($param['payfrom']))
            $setarr['payfrom'] = $param['payfrom'];
        if(!empty($param['totalfee']))
            $setarr['totalfee'] = $param['totalfee'];
        if(!empty($param['ip']))
            $setarr['ip'] = $param['ip'];
        if(!empty($param['payip']))
            $setarr['payip'] = $param['payip'];
        if(!empty($param['paycode']))
            $setarr['paycode'] = $param['paycode'];
        if(!empty($param['remark']))
            $setarr['remark'] = $param['remark'];
        if(!empty($param['ordernumber']))
            $setarr['ordernumber'] = $param['ordernumber'];
        if(!empty($param['buyer_id']))
            $setarr['buyer_id'] = $param['buyer_id'];
        if(!empty($param['buyer_info']))
            $setarr['buyer_info'] = $param['buyer_info'];
        if(!empty($param['status']))
            $setarr['status'] = $param['status'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        if(!empty($param['refunded']))
            $setarr['refunded'] = $param['refunded'];
        if(!empty($param['out_trade_no']))
            $setarr['out_trade_no'] = $param['out_trade_no'];
        if(isset($param['invalid']))
            $setarr['invalid'] = $param['invalid'];
        if(isset($param['itype']))
            $setarr['itype'] = $param['itype'];
        if(isset($param['redeemcode']))
            $setarr['redeemcode'] = $param['redeemcode'];
        if(isset($param['isbatchrefund']))
            $setarr['isbatchrefund'] = $param['isbatchrefund'];
        if(isset($param['batchid']))
            $setarr['batchid'] = $param['batchid'];
        if(isset($param['ptype']))
            $setarr['ptype'] = $param['ptype'];
        $orderid = Ebh()->db->insert('ebh_pay_torders',$setarr);
        return $orderid;
    }

    /**
     * 插入一条记录
     * 
     */
    public function addRecorder($param=array()){
        if(empty($param)){
            return false;
        }
        $data = array();
        if(!empty($param['uid'])){
            $data['uid'] = $param['uid'];
        }
        $data['cate'] = 1;
        $data['dateline'] = time();
        $data['status'] = 1;
        return Ebh()->db->insert('ebh_records',$data);
    }

    /**
    *生成充值记录，退钱就是充值
    */
    public function addCharge($param = array()) {
        if(empty($param))
            return false;
        $data = array();
        $param['rid'] = $this->addRecorder($param);
        if(!empty($param['rid'])){
            $data['rid'] = $param['rid'];
        }
        $data['uid'] = 0;
        if(!empty($param['uid'])){
            $data['useuid'] = $param['uid'];
        }
        if(!empty($param['name'])){
            $data['cardno'] = $param['name'];
        } else {
            $data['cardno'] = $param['ordername'];
        }
            
        $data['type'] = 11;//退款充值
        if(!empty($param['money'])){
            $data['value'] = $param['money'];
        }
        //余额
        $sqltouser = "select balance from ebh_users where uid =".intval($param['uid']);
        $res = Ebh()->db->query($sqltouser)->row_array();
        if(!empty($res['balance'])){
            $data['curvalue'] = $res['balance'];
        }
        $data['status'] = 1;
        if(!empty($param['payip'])){
            $data['fromip'] = $param['payip'];
        }
        $data['paytime'] = time();
        $data['dateline'] = time();
        return Ebh()->db->insert('ebh_charges',$data);
    }

    /**
     * 更新
     * @param $lotid 批次ID
     * @param $crid 网校ID
     * @param $params 新闻参数
     * @return int
     */
    public function update($lotid, $crid, $params) {
        $lotid = (int) $lotid;
        $crid= (int) $crid;
        $formatParams = array();
        if (isset($params['name'])) {
            $formatParams['name'] = $params['name'];
            $tParams['ordername'] = $params['name'];
            $whereStr = '`batchid`='.$lotid.' AND `ptype` in(1,2)';
            Ebh()->db->update('ebh_pay_torders', $tParams, $whereStr);
        }
        if (isset($params['effecttime'])) {
            $formatParams['effecttime'] = $params['effecttime'];
        }
        if (empty($formatParams)) {
            return 0;
        }
        $whereStr = '`lotid`='.$lotid.' AND `crid`='.$crid;
        return Ebh()->db->update('ebh_redeem_lots', $formatParams, $whereStr);
    }


}
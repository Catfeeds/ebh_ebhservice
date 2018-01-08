<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:52
 */
class PayorderModel{
	private $db;
	public function __construct() {
		$this->db = Ebh()->db;
	}
    /**
     *根据订单明细内容生成订单信息
     *$noitemlist 如果为TRUE，则允许订单明细为空，默认不允许
     */
    public function addOrder($param = array(),$noitemlist = FALSE) {
        if(empty($param) || (empty($param['itemlist']) && !$noitemlist))
            return FALSE;
        $setarr = array();
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['providercrid']))
            $setarr['providercrid'] = $param['providercrid'];
        if(!empty($param['ordername']))
            $setarr['ordername'] = $param['ordername'];
        if(!empty($param['sourceid']))
            $setarr['sourceid'] = $param['sourceid'];
        if(!empty($param['pid']))
            $setarr['pid'] = $param['pid'];
        if(!empty($param['cwid']))
            $setarr['cwid'] = $param['cwid'];
        if(!empty($param['uid']))
            $setarr['uid'] = $param['uid'];
        if(!empty($param['paytime']))
            $setarr['paytime'] = $param['paytime'];
        if(!empty($param['payfrom']))
            $setarr['payfrom'] = $param['payfrom'];
        if(!empty($param['totalfee']))
            $setarr['totalfee'] = $param['totalfee'];
        if(!empty($param['comfee']))
            $setarr['comfee'] = $param['comfee'];
        if(!empty($param['roomfee']))
            $setarr['roomfee'] = $param['roomfee'];
        if(!empty($param['providerfee']))
            $setarr['providerfee'] = $param['providerfee'];
        if(!empty($param['ip']))
            $setarr['ip'] = $param['ip'];
        if(!empty($param['payip']))
            $setarr['payip'] = $param['payip'];
        if(!empty($param['paycode']))
            $setarr['paycode'] = $param['paycode'];
        if(!empty($param['bankid']))
            $setarr['bankid'] = $param['bankid'];
        if(!empty($param['buyer_id']))
            $setarr['buyer_id'] = $param['buyer_id'];
        if(!empty($param['buyer_info']))
            $setarr['buyer_info'] = $param['buyer_info'];
        if(!empty($param['remark']))
            $setarr['remark'] = $param['remark'];
        if(!empty($param['ordernumber']))
            $setarr['ordernumber'] = $param['ordernumber'];
        if(!empty($param['status']))
            $setarr['status'] = $param['status'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        else
            $setarr['dateline'] = SYSTIME;
        if(!empty($param['refunded']))
            $setarr['refunded'] = $param['refunded'];
        if(!empty($param['couponcode']))
            $setarr['couponcode'] = $param['couponcode'];
        $orderid = Ebh()->db->insert('ebh_pay_orders',$setarr);
        if($orderid > 0 && !empty($param['itemlist'])) {	//处理订单明细
            foreach($param['itemlist'] as $item) {
                $item['orderid'] = $orderid;
                if(!empty($param['status']))
                    $item['dstatus'] = $param['status'];
                if(!empty($param['isschsource'])){//企业选课crid处理
                    $item['crid'] = $param['crid'];
                    $item['providercrid'] = $param['providercrid'];
                    $item['omonth'] = $param['omonth'];
                    $item['comfee'] = $param['comfee'];
                    $item['roomfee'] = $param['roomfee'];
                    $item['providerfee'] = $param['providerfee'];
                    $item['fee'] = $param['totalfee'];
                }
                $detailid = $this->addOrderDetail($item);
            }
        }
        return $orderid;
    }



    /**
     *添加订单明细
     */
    public function addOrderDetail($param) {
        if(empty($param) || empty($param['orderid']))
            return FALSE;
        $setarr = array();
        if(!empty($param['orderid']))
            $setarr['orderid'] = $param['orderid'];
        if(!empty($param['pid']))
            $setarr['pid'] = $param['pid'];
        if(!empty($param['uid']))
            $setarr['uid'] = $param['uid'];
        if(!empty($param['itemid']))
            $setarr['itemid'] = $param['itemid'];
        if(!empty($param['fee']))
            $setarr['fee'] = $param['fee'];
        if(!empty($param['comfee']))
            $setarr['comfee'] = $param['comfee'];
        if(!empty($param['roomfee']))
            $setarr['roomfee'] = $param['roomfee'];
        if(!empty($param['providerfee']))
            $setarr['providerfee'] = $param['providerfee'];
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['providercrid']))
            $setarr['providercrid'] = $param['providercrid'];
        if(!empty($param['folderid']))
            $setarr['folderid'] = $param['folderid'];
        if(!empty($param['rname']))
            $setarr['rname'] = $param['rname'];
        if(!empty($param['oname']))
            $setarr['oname'] = $param['oname'];
        if(!empty($param['omonth']))
            $setarr['omonth'] = $param['omonth'];
        if(!empty($param['oday']))
            $setarr['oday'] = $param['oday'];
        if(!empty($param['osummary']))
            $setarr['osummary'] = $param['osummary'];
        if(isset($param['dstatus']))
            $setarr['dstatus'] = $param['dstatus'];
        if(isset($param['cwid']))
            $setarr['cwid'] = $param['cwid'];
        if(empty($setarr))
            return FALSE;
        $detailid = Ebh()->db->insert('ebh_pay_orderdetails',$setarr);
        return $detailid;
    }


    /**
     *根据订单编号获取订单和订单详情信息
     */
    public function getOrderById($orderid,$crid=0) {
        $sql = "select o.orderid,o.ordername,o.refunded,o.crid,o.uid,o.dateline,o.paytime,o.payfrom,o.totalfee,o.ip,o.payip,o.paycode,o.ordernumber,o.bankid,o.remark,o.status,o.pid,o.providercrid,o.comfee,o.roomfee,o.providerfee,o.couponcode,o.cwid,o.buyer_id,o.buyer_info from ebh_pay_orders o where o.orderid=$orderid";
		if(!empty($crid)){
			$sql.= ' and o.crid='.$crid;
		}
        $myorder = Ebh()->db->query($sql)->row_array();
        if(!empty($myorder) && empty($crid)) {
            $myorder['detaillist'] = $this->getOrderDetailListByOrderId($orderid);
        } elseif(!empty($myorder) && !empty($crid)){//订单详情课程信息
			$myorder['detaillist'] = $this->getOrderDetailCourse($orderid,$myorder['cwid']);
		}
        return $myorder;
    }

    /**
     *根据订单编号获取订单详情
     */
    public function getOrderDetailListByOrderId($orderid) {
        $sql = "select d.detailid,d.orderid,d.itemid,d.fee,d.crid,d.folderid,d.oname,d.osummary,d.omonth,d.oday,d.rname,d.providercrid,d.comfee,d.roomfee,d.providerfee,d.cwid from ebh_pay_orderdetails d where d.orderid=$orderid";
        return Ebh()->db->query($sql)->list_array();
    }
	
	public function getOrderDetailCourse($orderid,$cwid){
		
		if(!empty($cwid)){//课件
			$sql = 'select d.omonth,d.oday,d.fee,d.sharefee,d.comfee,d.roomfee,d.providerfee,cw.title cwtitle
					from ebh_pay_orderdetails d
					left join ebh_coursewares cw on d.cwid=cw.cwid 
					where d.orderid='.$orderid;
		} else {//课程
			$sql = 'select p.pname,i.iname,d.omonth,d.oday,d.fee,d.sharefee,d.comfee,d.roomfee,d.providerfee,s.sname
				from ebh_pay_orderdetails d
				left join ebh_pay_packages p on d.pid=p.pid
				left join ebh_pay_items i on d.itemid=i.itemid
				left join ebh_pay_sorts s on i.sid=s.sid
				where d.orderid='.$orderid;
		}
		return $this->db->query($sql)->list_array();
	}

    /**
     *更新订单信息，如果包含明细，则同时更新明细信息
     */
    public function updateOrder($param = array()) {
        if(empty($param) || empty($param['orderid']))
            return FALSE;
        $setarr = array();
        $wherearr = array('orderid'=>$param['orderid']);
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['providercrid']))
            $setarr['providercrid'] = $param['providercrid'];
        if(!empty($param['ordername']))
            $setarr['ordername'] = $param['ordername'];
        if(!empty($param['paytime']))
            $setarr['paytime'] = $param['paytime'];
        if(!empty($param['payfrom']))
            $setarr['payfrom'] = $param['payfrom'];
        if(!empty($param['totalfee']))
            $setarr['totalfee'] = $param['totalfee'];
        if(!empty($param['comfee']))
            $setarr['comfee'] = $param['comfee'];
        if(!empty($param['roomfee']))
            $setarr['roomfee'] = $param['roomfee'];
        if(!empty($param['providerfee']))
            $setarr['providerfee'] = $param['providerfee'];
        if(!empty($param['ip']))
            $setarr['ip'] = $param['ip'];
        if(!empty($param['payip']))
            $setarr['payip'] = $param['payip'];
        if(!empty($param['paycode']))
            $setarr['paycode'] = $param['paycode'];
        if(!empty($param['bankid']))
            $setarr['bankid'] = $param['bankid'];
        if(!empty($param['buyer_id']))
            $setarr['buyer_id'] = $param['buyer_id'];
        if(!empty($param['buyer_info']))
            $setarr['buyer_info'] = $param['buyer_info'];
        if(!empty($param['remark']))
            $setarr['remark'] = $param['remark'];
        if(!empty($param['ordernumber']))
            $setarr['ordernumber'] = $param['ordernumber'];
        if(isset($param['status']))
            $setarr['status'] = $param['status'];
        if(!empty($param['refunded']))
            $setarr['refunded'] = $param['refunded'];
        $afrows = Ebh()->db->update('ebh_pay_orders',$setarr,$wherearr);
        if($afrows !== FALSE&&(!empty($param['itemlist']))) {	//处理订单明细
            foreach($param['itemlist'] as $item) {
                if(isset($param['status']))
                    $item['dstatus'] = $param['status'];
                $dafrows = $this->updateOrderDetail($item);
            }
        }
        return $afrows;
    }


    /**
     *修改订单明细
     */
    public function updateOrderDetail($item) {
        if(empty($item) || empty($item['detailid']))
            return FALSE;
        $setarr = array();
        $wherearr = array('detailid'=>$item['detailid']);
        if(!empty($item['orderid']))
            $setarr['orderid'] = $item['orderid'];
        if(!empty($item['fee']))
            $setarr['fee'] = $item['fee'];
        if(!empty($item['comfee']))
            $setarr['comfee'] = $item['comfee'];
        if(!empty($item['roomfee']))
            $setarr['roomfee'] = $item['roomfee'];
        if(!empty($item['providerfee']))
            $setarr['providerfee'] = $item['providerfee'];
        if(!empty($item['crid']))
            $setarr['crid'] = $item['crid'];
        if(!empty($item['providercrid']))
            $setarr['providercrid'] = $item['providercrid'];
        if(!empty($item['folderid']))
            $setarr['folderid'] = $item['folderid'];
        if(!empty($item['oname']))
            $setarr['oname'] = $item['oname'];
        if(!empty($item['omonth']))
            $setarr['omonth'] = $item['omonth'];
        if(!empty($item['oday']))
            $setarr['oday'] = $item['oday'];
        if(isset($item['dstatus']))
            $setarr['dstatus'] = $item['dstatus'];
        if(empty($setarr))
            return FALSE;
        $afrows = Ebh()->db->update('ebh_pay_orderdetails',$setarr,$wherearr);
        return $afrows;
    }
	
	/*
	 * 订单列表
	 * @param array $param
	 * @return array
	 * 
	*/
	public function getPayOrderList($param){
		if(!empty($param['itemid'])){
			$wherearr[]='od.itemid ='. intval($param['itemid']);
			$sql = 'select o.orderid,u.sex,u.username,u.realname,u.face,o.crid,o.providercrid,o.orderid,o.refunded,o.invalid,o.ordername,o.paytime,o.ordernumber,o.dateline,o.payfrom, o.totalfee,o.comfee,o.roomfee,o.providerfee,o.ip,o.payip,od.omonth,od.oday,p.pname,i.iname from ebh_pay_orders o 
				left join ebh_pay_orderdetails od on o.orderid=od.orderid
				left join ebh_users u on o.uid = u.uid 
				left join ebh_pay_packages p on p.pid = o.pid
				left join ebh_pay_items i on od.itemid=i.itemid
				';
		} else {
			$sql = 'select u.sex,u.username,u.realname,u.face,o.crid,o.providercrid,o.orderid,o.refunded,o.invalid,o.ordername,o.paytime,o.ordernumber,o.dateline,o.payfrom, o.totalfee,o.comfee,o.roomfee,o.providerfee,o.ip,o.payip,p.pname from ebh_pay_orders o 
				left join ebh_users u on o.uid = u.uid 
				left join ebh_pay_packages p on p.pid = o.pid
				';
		}
		
		$wherearr[]= 'o.status =1';
		if(!empty($param['q']))
			$wherearr[]= '  (u.username like \'%'. $this->db->escape_str($param['q']) .'%\' or o.ordernumber like \'%' . $this->db->escape_str($param['q']) .'%\')';
		if(!empty($param['crid'])){
			if(is_array($param['crid'])){
				$wherearr[]=' ( o.crid IN( '.implode(',', $param['crid']).' ) OR o.providercrid IN ( '.implode(',', $param['crid']).' ) )';
			}else{
				$wherearr[]='( o.crid ='. intval($param['crid']).'  OR (o.providercrid ='. intval($param['crid']).' ) and o.crid = 0)';
			}
		}
		if(!empty($param['providercrid'])){
			$wherearr[]='o.providercrid ='. intval($param['providercrid']);
		}
		if(!empty($param['ordernumber'])){
			$wherearr[]='o.ordernumber=\''.$this->db->escape_str($param['ordernumber']).'\'';
		}
		if(!empty($param['payfrom'])){
			if($param['payfrom']=='all'){
				$wherearr[]='o.payfrom != 5';
			}else{
				$wherearr[]='o.payfrom='.intval($param['payfrom']);
			}
		}
		//订单金额处理
		if($param['money'] > 0){
		    if($param['money'] == 1){//付费订单
		        $wherearr[]='o.totalfee > 0 and o.refunded = 0';
		    }elseif($param['money'] == 2){//免费订单
		        $wherearr[]='o.totalfee = 0 and o.refunded = 0';
		    }elseif($param['money'] == 3){//退款订单
		        $wherearr[]=' o.refunded >  0';
		    }
		}
		
		if(!empty($param['starttime'])){
			$wherearr[]='o.dateline >= '.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[]='o.dateline <= '.$param['endtime'];
		}
		
		if(!empty($param['pid'])){
			$wherearr[]='p.pid = '.intval($param['pid']);
		}
		if(isset($param['needtype'])){
			if($param['needtype'] == 0)//只看服务包
				$wherearr[] = 'o.cwid = 0';
			else//只看课件
				$wherearr[] = 'o.cwid <> 0';
		}
		if(!empty($wherearr))
			$sql.= ' where ' .implode(' AND ',$wherearr);

		if(!empty($param['order']))
			$sql.= ' ORDER BY ' .$param['order'];
		else
			$sql.=' order by o.orderid desc';
		
		if(empty($param['nolimit'])){
			if(!empty($param['limit'])) {
				$sql .= ' limit '.$param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
		}
		$countsql = 'select count(distinct(o.orderid)) count from ebh_pay_orders o 
				left join ebh_pay_orderdetails od on o.orderid=od.orderid
				left join ebh_users u on o.uid = u.uid 
				left join ebh_pay_packages p on p.pid = o.pid';
		$countsql.= ' where ' .implode(' AND ',$wherearr);
		$count = $this->db->query($countsql)->row_array();
		$list = $this->db->query($sql)->list_array();
		return array('list'=>$list,'count'=>$count['count']);
	}
	
	/*
	收入列表
	*/
	public function earningList($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$wherearr = array();
    	if(!empty($param['starttime'])){
    		$wherearr[] = ' p.dateline >= '.$param['starttime'];
    	}
    	if(!empty($param['endtime'])){
    		$wherearr[] = ' p.dateline <='.$param['endtime'];
    	}
    	if(!empty($param['pid'])){
    		$wherearr[] = ' p.pid ='.intval($param['pid']);
    	}
    	$sql = 'select od.itemid,sum(od.fee) as total ,od.fee,count(od.orderid) as numcount,sum(if(o.refunded>0,1,0)) as tuicount,od.crid, od.pid,p.pname,p.dateline ,i.iname,s.sname 
    				from  ebh_pay_orderdetails od
    				LEFT JOIN ebh_pay_orders o on o.orderid = od.orderid 
 					LEFT JOIN ebh_pay_items i on i.itemid = od.itemid
					LEFT JOIN ebh_pay_sorts s on i.sid=s.sid
					LEFT JOIN ebh_pay_packages p on p.pid = od.pid
					where  od.crid ='.$param['crid'].' and od.dstatus = 1 and od.fee !=0 and o.invalid = 0 and od.invalid = 0  ';

    	if(!empty($wherearr)){
    		$sql.= ' AND '.implode(' AND ',$wherearr);
    	}
    	$sql .=' GROUP BY od.itemid ';
    	$sql .= ' order by  p.dateline desc ';
    	if(!empty($param['limit'])) {
            $sql .= ' limit '.$param['limit'];
        } else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
        }
		
		
		$countsql = 'select count(1) count from (  
					select 1
    				from  ebh_pay_orderdetails od
    				LEFT JOIN ebh_pay_orders o on o.orderid = od.orderid 
 					LEFT JOIN ebh_pay_items i on i.itemid = od.itemid
					LEFT JOIN ebh_pay_packages p on p.pid = od.pid
					where  od.crid ='.$param['crid'].' and od.dstatus = 1 and od.fee !=0 and o.invalid = 0 and od.invalid = 0  ';
    	if(!empty($wherearr)){
    		$countsql.= ' AND '.implode(' AND ',$wherearr);
    	};
		$countsql.=' GROUP BY od.itemid
    			) tab';
		
		$count = $this->db->query($countsql)->row_array();
		$list = $this->db->query($sql)->list_array();
		foreach($list as  &$course){
            $course['okcount'] = max(0,$course['numcount']-$course['tuicount']);
        }
		return array('list'=>$list,'count'=>$count['count']);
		
	}
	
	/*
	结算申请
	*/
	public function applyList($param){
		if(empty($param['jids']) && empty($param['crid'])){
			return array();
		}
		$sql = 'select p.crid,p.jid,p.money,p.moneyaftertax,p.realname,p.isinvoice,p.type,p.bankname,p.accountnum,p.uname,p.notes,p.status,p.mstatus,p.paystatus,p.dateline,a.status astatus,jsnotes,p.isprocessed,p.ip,cr.crname
				from ebh_jsapplys p left join ebh_jsauths a on p.aid=a.aid
				left join ebh_classrooms cr on p.crid=cr.crid';
		if(!empty($param['jids'])){
			$wherearr[] = 'p.jid in('.$param['jids'].')';
		} 
		if(!empty($param['crid'])){
			$wherearr[] = 'p.crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'p.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'p.dateline<='.$param['endtime'];
		}
		if(!empty($param['aid'])){
			$wherearr[] = 'p.aid='.$param['aid'];
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[]= '  (p.realname like \'%'. $q .'%\' or p.accountnum like \'%' . $q .'%\' or p.uname like \'%'. $q . '%\')';
		}
		if(isset($param['status'])){
			switch($param['status']){
				case 1://公司或财务未审核,且身份审核不为失败,---->待处理
					$wherearr[] = '((p.status=0 or mstatus=0) and p.status<>2 and mstatus<>2 and (isnull(a.status) or a.status<>2))';
					break;
				case 2://公司以及财务审核通过,未支付,身份审核成功---->申请成功
					$wherearr[] = '(p.status=1 and mstatus=1 and paystatus=0 and (isnull(a.status) or a.status=1))';
					break;
				case 3://公司,财务,身份其中有未通过的---->申请失败
					$wherearr[] = '(p.status=2 or mstatus=2 or a.status=2)';
					break;
				case 4://付款成功
					$wherearr[] = '(paystatus=1)';
					break;
				case 5://付款失败
					$wherearr[] = '(paystatus=2)';
					break;
				case 6://付款失败待处理
					$wherearr[] = '(paystatus=3)';
					break;
			}
		}
		if(!empty($param['paystatusarr'])){
			$wherearr[] = 'paystatus in ('.implode(',',$param['paystatusarr']).')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' order by p.jid desc';
		if(empty($param['nolimit'])){
			if(!empty($param['limit'])) {
				$sql .= ' limit '.$param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
		}
		$list = $this->db->query($sql)->list_array();
		return $list;
	}
	
	/*
	结算申请
	*/
	public function apply($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$insertarr['crid'] = $param['crid'];
		$insertarr['dateline'] = SYSTIME;
		if(!empty($param['money'])){
			$insertarr['money'] = $param['money'];
		}
		if(!empty($param['moneyaftertax'])){
			$insertarr['moneyaftertax'] = $param['moneyaftertax'];
		}
		if(!empty($param['taxrat'])){
			$insertarr['taxrat'] = $param['taxrat'];
		}
		if(!empty($param['isinvoice'])){
			$insertarr['isinvoice'] = $param['isinvoice'];
		}
		if(!empty($param['realname'])){
			$insertarr['realname'] = $param['realname'];
		}
		if(!empty($param['type'])){
			$insertarr['type'] = $param['type'];
		}
		if(!empty($param['bankname'])){
			$insertarr['bankname'] = $param['bankname'];
		}
		if(!empty($param['accountnum'])){
			$insertarr['accountnum'] = $param['accountnum'];
		}
		if(!empty($param['uname'])){
			$insertarr['uname'] = $param['uname'];
		}
		if(!empty($param['notes'])){
			$insertarr['notes'] = $param['notes'];
		}
		if(!empty($param['ip'])){
			$insertarr['ip'] = $param['ip'];
		}
		if(!empty($param['aid'])){
			$insertarr['aid'] = $param['aid'];
		}
		
		return $this->db->insert('ebh_jsapplys',$insertarr);
	}
	
	/*
	结算申请数量
	*/
	public function applyCount($param){
		if(empty($param['jids']) && empty($param['crid'])){
			return 0;
		}
		$countsql = 'select count(1) count from ebh_jsapplys p left join ebh_jsauths a on p.aid=a.aid';
		if(!empty($param['jids'])){
			$wherearr[] = 'p.jid in('.$param['jids'].')';
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'p.crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'p.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'p.dateline<='.$param['endtime'];
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[]= '  (p.realname like \'%'. $q .'%\' or p.accountnum like \'%' . $q .'%\' or p.uname like \'%'. $q . '%\')';
		}
		if(isset($param['status'])){
			switch($param['status']){
				case 1:
					$wherearr[] = '((p.status=0 or mstatus=0) and p.status<>2 and mstatus<>2 and (isnull(a.status) or a.status<>2))';
					break;
				case 2:
					$wherearr[] = '(p.status=1 and mstatus=1 and paystatus=0 and (isnull(a.status) or a.status=1))';
					break;
				case 3:
					$wherearr[] = '(p.status=2 or mstatus=2 or a.status=2)';
					break;
				case 4:
					$wherearr[] = '(paystatus=1)';
					break;
				case 5:
					$wherearr[] = '(paystatus=2)';
					break;
				case 6:
					$wherearr[] = '(paystatus=3)';
					break;
			}
		}
		if(!empty($param['aid'])){
			$wherearr[] = 'aid='.$param['aid'];
		}
		
		$countsql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($countsql)->row_array();
		return $count['count'];
	}
	
	/*
	提交身份证
	*/
	public function doAuth($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$insertarr['crid'] = $param['crid'];
		$insertarr['dateline'] = SYSTIME;
		$insertarr['ip'] = getclientip();
		if(!empty($param['uid'])){
			$insertarr['uid'] = $param['uid'];
		}
		if(!empty($param['mobile'])){
			$insertarr['mobile'] = $param['mobile'];
		}
		if(!empty($param['idcard_z'])){
			$insertarr['idcard_z'] = $param['idcard_z'];
		}
		if(!empty($param['idcard_b'])){
			$insertarr['idcard_b'] = $param['idcard_b'];
		}
		if(!empty($param['aid'])){
			$insertarr['aid'] = $param['aid'];
		}
		return $this->db->insert('ebh_jsauths',$insertarr);
	}
	
	/*
	未审核或已通过的身份审核申请
	*/
	public function authStatus($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select status from ebh_jsauths where crid='.$param['crid'].' and (status=0 or status=1)';
		if(!empty($param['aid'])){
			$sql.= ' and aid='.$param['aid'];
		}
		return $this->db->query($sql)->list_array();
	}
	
	/*
	余额,可提取统计
	*/
	public function moneyStats($param){
		$sql = 'select sum(roomfee) roomfee from ebh_pay_orders';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'status=1';
		$wherearr[] = 'invalid=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$totalfee = $this->db->query($sql)->row_array();
		
		$freezetimesql = 'select fund_freezn from ebh_freezn_times where crid='.$param['crid'].' order by fid desc limit 1';
		$freezetime = $this->db->query($freezetimesql)->row_array();
		$freezetime = empty($freezetime['fund_freezn'])?15:$freezetime['fund_freezn'];
		$freezesql = 'select sum(roomfee) roomfee from ebh_pay_orders';
		$freezewherearr[] = 'crid='.$param['crid'];
		$freezewherearr[] = 'status=1';
		$freezewherearr[] = 'invalid=0';
		$today = strtotime('today')+86400;//今天
		$thatday = $today - $freezetime*86400;//冻结N(15)天前
		$freezewherearr[] = '(dateline>='.$thatday.' and dateline<='.$today.')';
		$freezesql.= ' where '.implode(' AND ',$freezewherearr);
		$freezefee = $this->db->query($freezesql)->row_array();
		
		return array(
			'totalfee'=>empty($totalfee['roomfee'])?0:$totalfee['roomfee'],
			'freezefee'=>empty($freezefee['roomfee'])?0:$freezefee['roomfee'],
			'freezetime'=>$freezetime
		);
	}
	
	/*
	收入统计
	*/
	public function earningStats($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'status=1';
		if(!empty($param['starttime'])){
			$wherearr[] = 'dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'dateline<='.$param['endtime'];
		}
		if($param['bywhich'] == 'type'){
			$sql = 'select count(1) count,payfrom from ebh_pay_orders';
			$sql.= ' where '.implode(' AND ',$wherearr);
			$sql.= ' group by payfrom';
			return $this->db->query($sql)->list_array();
		}
		
		if($param['bywhich'] == 'month'){
			$sql = "select sum(roomfee) roomfee,DATE_FORMAT(from_unixtime(dateline),'%Y-%m') d from ebh_pay_orders";
		} else {
			$sql = "select sum(roomfee) roomfee,DATE_FORMAT(from_unixtime(dateline),'%Y-%m-%d') d from ebh_pay_orders";
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by d';
		return $this->db->query($sql)->list_array('d');
	}
	
	/*
	课程开通人员列表
	*/
	public function getOpenList($param){
		if(empty($param['crid']) || (empty($param['itemid']) && empty($param['bid']))){
			return array();
		}
		$idtype = empty($param['itemid'])?'bid':'itemid';
		$sql = 'select od.uid,o.dateline,c.classname  
				from ebh_pay_orders o 
				join ebh_pay_orderdetails od on o.orderid=od.orderid
				join ebh_classstudents cs on od.uid=cs.uid 
				join ebh_classes c on c.classid=cs.classid';
		$wherearr[] = 'od.'.$idtype.'='.$param[$idtype];
		$wherearr[] = 'od.crid='.$param['crid'];
		$wherearr[] = 'o.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'o.refunded=0';
		$wherearr[] = 'o.status=1';
		if($idtype == 'itemid'){
			$wherearr[] = 'od.bid=0';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by od.uid order by od.orderid desc';
		if(!empty($param['limit'])){
			$sql.= ' limit '.$param['limit'];
		}
		return $this->db->query($sql)->list_array();
	}
	
	/*
	课程开通人员数量
	*/
	public function getOpenCount($param){
		if(empty($param['crid']) || (empty($param['itemid']) && empty($param['bid']))){
			return array('opencount'=>0,'selfcount'=>0);
		}
		$selfcountstr = '';
		if(!empty($param['uid'])){//查询当前用户是否开通过
			$selfcountstr = ',count( case when od.uid='.$param['uid'].' then 1 end) selfcount';
		}
		//bid课程包，iteimid课程
		$idtype = empty($param['itemid'])?'bid':'itemid';
		$sql = 'select '.$idtype.', count(distinct(od.uid)) opencount '.$selfcountstr.' 
				from ebh_pay_orders o 
				join ebh_pay_orderdetails od on o.orderid=od.orderid
				join ebh_classstudents cs on od.uid=cs.uid 
				join ebh_classes c on c.classid=cs.classid';
		$wherearr[] = 'od.'.$idtype.' in ('.$param[$idtype].')';
		$wherearr[] = 'od.crid='.$param['crid'];
		$wherearr[] = 'o.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'o.refunded=0';
		$wherearr[] = 'o.status=1';
		if($idtype == 'itemid'){
			$wherearr[] = 'od.bid=0';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(empty($param['islist'])){//单个课程/课程包
			$count = $this->db->query($sql)->row_array();
		} else {//多个课程/课程包
			$sql.= ' group by '.$idtype;
			$count = $this->db->query($sql)->list_array($idtype);
		}
		return $count;
	}

    /**
     * 获取用户一分钟内支付的订单
     * @param int $uid 用户ID
     * @return mixed
     */
	public function getLatestPayedOrder($uid) {
	    //读取1分钟内支付的订单
        $baseTime = SYSTIME - 60;
	    $wheres = array(
	        '`uid`='.$uid,
            '`status`=1'
        );
	    $sql = 'SELECT `orderid`,`crid`,`dateline` FROM `ebh_pay_orders` WHERE '.implode(' AND ', $wheres).' ORDER BY `orderid` DESC LIMIT 1';
	    return Ebh()->db->query($sql)->row_array();
    }
}
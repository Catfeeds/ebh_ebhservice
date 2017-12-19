<?php

/**
 * 结算管理
 * User: ckx
 */
class SettlementController extends Controller
{
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules()
    {
        return array(
            'payOrderListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','default' => 100,'type'=>'int'),
                'page'  =>  array('name'=>'page','default' => 1,'type'=>'int'),
                'payfrom'  =>  array('name'=>'payfrom','default' => '','type'=>'string'),
                'order'  =>  array('name'=>'order','default' => '','type'=>'string'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>'','type'=>'string'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
                'needtype'  =>  array('name'=>'needtype','default'=>null,'type'=>'int'),
				'invalid'  =>  array('name'=>'invalid','default'=>0,'type'=>'int'),
				'money'  =>  array('name'=>'money','default'=>0,'type'=>'int'),
				'itemid'  =>  array('name'=>'itemid','default'=>0,'type'=>'int'),
				'isreport' =>  array('name'=>'isreport','default'=>0,'type'=>'int'),
            ),
			'payOrderDetailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'orderid'  =>  array('name'=>'orderid','require' => true,'type'=>'int'),
			),
			'earningListAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'pagesize'  =>  array('name'=>'pagesize','default' => 100,'type'=>'int'),
                'page'  =>  array('name'=>'page','default' => 1,'type'=>'int'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
			),
			'applyListAction' => array(
                'crid'  =>  array('name'=>'crid','default'=>0,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'pagesize'  =>  array('name'=>'pagesize','default' => 100,'type'=>'int'),
                'page'  =>  array('name'=>'page','default' => 1,'type'=>'int'),
                'status'  =>  array('name'=>'status','default'=>null,'type'=>'int'),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
				'jids'  =>  array('name'=>'jids','default'=>'','type'=>'string'),
				'q'  =>  array('name'=>'q','default'=>'','type'=>'string'),
				'paystatusarr' => array('name'=>'paystatusarr','default'=>array(),'type'=>'array'),
			),
			'applyAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'money'  =>  array('name'=>'money','default'=>0,'type'=>'float'),
                'moneyaftertax'  =>  array('name'=>'moneyaftertax','default'=>0,'type'=>'float'),
                'taxrat'  =>  array('name'=>'taxrat','default'=>0,'type'=>'float'),
                'isinvoice'  =>  array('name'=>'isinvoice','default'=>0,'type'=>'int'),
				'realname'  =>  array('name'=>'realname','default' => '','type'=>'string'),
                'type'  =>  array('name'=>'type','default' => 0,'type'=>'int'),
                'bankname'  =>  array('name'=>'bankname','default'=>'','type'=>'string'),
                'accountnum'  =>  array('name'=>'accountnum','default'=>'','type'=>'string'),
                'uname'  =>  array('name'=>'uname','default'=>'','type'=>'string'),
                'notes'  =>  array('name'=>'notes','default'=>'','type'=>'string'),
				'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
			),
			'getBindAction' => array(
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
			),
			'getCrBindAction' => array(
				'crids'  =>  array('name'=>'crids','require'=>true,'type'=>'string'),
			),
			'checkApplyLimitAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
			),
			'doAuthAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'mobile'  =>  array('name'=>'mobile','default' => '','type'=>'string'),
				'idcard_z'  =>  array('name'=>'idcard_z','default' => '','type'=>'string'),
				'idcard_b'  =>  array('name'=>'idcard_b','default' => '','type'=>'string'),
			),
			'authStatusAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
			),
			'moneyStatsAction' => array(
				'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
			),
			'earningStatsAction' => array(
				'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
				'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'bywhich' => array('name'=>'bywhich','default'=>'day','type'=>'string'),
			),
			
        );
    }
	
	/*
	订单列表
	*/
	public function payOrderListAction(){
		$param = array(
			'crid' => $this->crid,
			'pagesize' => $this->pagesize,
			'page' => $this->page,
			'payfrom' => $this->payfrom,
			'order' => $this->order,
			'starttime' => $this->starttime,
			'endtime' => $this->endtime,
			'q' => $this->q,
			'pid' => $this->pid,
			'needtype' => $this->needtype,
			'invalid' => $this->invalid,
			'money' => $this->money,
			'itemid' => $this->itemid
		);
		if(!empty($this->isreport)){
			$param['nolimit'] = 1;
		}
		$pomodel = new PayorderModel();
		$polist = $pomodel->getPayOrderList($param);
		$providercrid = array_column($polist['list'],'providercrid');
		$crmodel = new ClassRoomModel();
		$crlist = $crmodel->getClassRoomArray($providercrid);
		foreach($polist['list'] as &$payorder){
			if(isset($crlist[$payorder['providercrid']])){
				$payorder['providercrname'] = $crlist[$payorder['providercrid']];
			}
		}
		return $polist;
	}
	
		
	/*
	订单详情
	*/
	public function payOrderDetailAction(){
		$pomodel = new PayorderModel();
		$detail = $pomodel->getOrderById($this->orderid,$this->crid);
		return $detail;
	}
	
	/*
	交易列表
	*/
	public function earningListAction(){
		$pomodel = new PayorderModel();
		$param = array(
			'crid' => $this->crid,
			'starttime' => $this->starttime,
			'endtime' => $this->endtime,
			'pagesize' => $this->pagesize,
			'page' => $this->page,
			'pid' => $this->pid
		);
		$list = $pomodel->earningList($param);
		return $list;
	}
	
	/*
	申请结算列表
	*/
	public function applyListAction(){
		Ebh()->db->set_con(0);
		$pomodel = new PayorderModel();
		$param = array(
			'crid' => $this->crid,
			'starttime' => $this->starttime,
			'endtime' => $this->endtime,
			'pagesize' => $this->pagesize,
			'page' => $this->page,
			'status' => $this->status,
			'aid' => $this->aid,
			'jids' => $this->jids,
			'q'=>$this->q,
			'paystatusarr'=>$this->paystatusarr
		);
		$list = $pomodel->applyList($param);
		foreach($list as &$apply){
			if($apply['status'] == 2 || $apply['mstatus'] == 2 || $apply['astatus'] == 2){//申请失败
				$apply['status'] = '3';//----注意这里是3
			} elseif(($apply['status'] == 0 || $apply['mstatus'] == 0) && $apply['astatus'] != 2){//待处理
				$apply['status'] = '1';//----注意这里是1
			} elseif($apply['status'] == 1 && $apply['mstatus'] == 1 && $apply['paystatus'] ==0 && (!isset($apply['astatus']) || $apply['astatus'] == 1)){//申请成功
				$apply['status'] = '2';//----注意这里是2
			} elseif($apply['paystatus'] == 1){//付款成功
				$apply['status'] = '4';
			} elseif($apply['paystatus'] == 2){//付款失败
				$apply['status'] = '5';
			} elseif($apply['paystatus'] == 3){//付款失败待处理
				$apply['status'] = '6';
			}
		}
		$count = $pomodel->applyCount($param);
		return array('list'=>$list,'count'=>$count);
	}
	/*
	验证今日申请数量
	*/
	public function checkApplyLimitAction(){
		$param = array(
			'crid' => $this->crid,
			'starttime' => strtotime('today'),
			'endtime' => strtotime('today')+86400,
		);
		$pomodel = new PayorderModel();
		$count = $pomodel->applyCount($param);
		return array('todaycount'=>$count);
	}
	/*
	申请结算
	*/
	public function applyAction(){
		$pomodel = new PayorderModel();
		$check = $this->checkApplyLimitAction();
		if($check['todaycount']>=2){
			return array('status'=>2,'msg'=>'每天只能提交两次申请');
		}
		$moneystats = $this->moneyStatsAction();
		if($this->money>$moneystats['availablefee']){
			return array('status'=>2,'msg'=>'超过金额限制');
		}
		$param = array(
			'crid' => $this->crid,
			'money' => $this->money,
			'moneyaftertax' => $this->moneyaftertax,
			'taxrat' => $this->taxrat,
			'isinvoice' => $this->isinvoice,
			'realname' => $this->realname,
			'type' => $this->type,
			'bankname' => $this->bankname,
			'accountnum' => $this->accountnum,
			'uname' => $this->uname,
			'notes' => $this->notes,
			'ip'=>getclientip(),
			'aid'=>$this->aid
		);
		$res = $pomodel->apply($param);
		if($res != FALSE){
			return array('status'=>0,'msg'=>'成功');
		} else {
			return array('status'=>1,'msg'=>'失败');
		}
	}
    
	/*
	获取绑定信息
	*/
	public function getBindAction(){
		$bindmodel = new BindModel();
		$bind = $bindmodel->getUserBInd($this->uid);
		if(!empty($bind)){
			return array('ismobile'=>$bind['is_mobile'],'mobile'=>$bind['mobile']);
		} else {
			return array('ismobile'=>0);
		}
	}
	
	/*
	获取绑定信息
	*/
	public function getCrBindAction(){
		$bindmodel = new BindModel();
		return $bindmodel->getCrBindList(array('crids'=>$this->crids));
		
	}
	
	/*
	提交身份证
	*/
	public function doAuthAction(){
		$param = array(
			'crid'=>$this->crid,
			'uid'=>$this->uid,
			'mobile'=>$this->mobile,
			'idcard_z'=>$this->idcard_z,
			'idcard_b'=>$this->idcard_b
		);
		$status = $this->authStatusAction();
		if($status['status'] == 0){
			$pomodel = new PayorderModel();
			return $pomodel->doAuth($param);
		} else {
			return FALSE;
		}
	}
	
	/*
	身份验证状态 0没有请求,1通过,2暂未审核
	*/
	public function authStatusAction(){
		$pomodel = new PayorderModel();
		$param['crid'] = $this->crid;
		if(!empty($this->aid)){
			$param['aid'] = $this->aid;
		}
		$statuslist = $pomodel->authStatus($param);
		if(empty($statuslist)){
			return array('status'=>'0');
		} else {
			$statuslist = array_column($statuslist,'status');
			if(array_sum($statuslist) > 0){
				return array('status'=>1);
			} else {
				return array('status'=>2);
			}
		}
	}
	
	/*
	余额,可提取统计
	*/
	public function moneyStatsAction(){
		$param['crid'] = $this->crid;
		$pomodel = new PayorderModel();
		$moneystats = $pomodel->moneyStats($param);
		
		
		$param['nolimit'] = 1;
		$applylist = $pomodel->applyList($param);
		$moneystats['drawfee'] = 0;
		foreach($applylist as $apply){
			if($apply['paystatus'] == 1){//申请结算成功付款的
				$moneystats['totalfee'] -= $apply['money'];
				$moneystats['drawfee'] += $apply['money'];
			} elseif($apply['status'] != 2 && $apply['mstatus'] != 2 && $apply['paystatus'] != 2){//待审核或待付款的情况
				$moneystats['freezefee'] += $apply['money'];
			}
		}
		$moneystats['availablefee'] = round($moneystats['totalfee'] - $moneystats['freezefee'],2);
		return $moneystats;
	}
	
	/*
	收入统计
	*/
	public function earningStatsAction(){
		$param = array(
			'crid' => $this->crid,
			'starttime' => $this->starttime,
			'endtime' => $this->endtime,
			'bywhich' => $this->bywhich,
		);
		if($this->bywhich == 'month'){//12个月
			$param['starttime'] = strtotime(Date('Y-m',$this->starttime));
			$param['endtime'] = strtotime(Date('Y-m',$this->starttime) .' +1 year');
		} elseif($this->bywhich != 'type'){//31日
			$param['starttime'] = strtotime(Date('Y-m-d',$this->starttime));
			$param['endtime'] = $param['starttime'] + 86400*31;
		}
		$pomodel = new PayorderModel();
		$stats = $pomodel->earningStats($param);
		if($this->bywhich == 'month'){//填充月
			$m = $param['starttime'];
			while($m<$param['endtime']){
				$date = Date('Y-m',$m);
				if(!isset($stats[$date])){
					$stats[$date] = array('roomfee'=>0,'d'=>$date);
				}
				$m = strtotime($date.' +1 month');
			}
			ksort($stats);
		} elseif($this->bywhich != 'type'){//填充日
			for($i = $param['starttime'];$i<$param['endtime'];$i=$i+86400){
				$date = Date('Y-m-d',$i);
				if(!isset($stats[$date])){
					$stats[$date] = array('roomfee'=>0,'d'=>$date);
				}
			}
			ksort($stats);
		}
		
		return $stats;
	}
	
	
}
<?php
/**
 * 新版微校通
 * @author eker
 * @email qq704855854@126.com
 * @time 2016年6月7日17:05:30
 *
 */
class EthModel {
    //需要添加wechat
	private $outboxtable = NULL;
	private $inboxtable = NULL;
	private $messagetable = NULL;
	private $replytable = NULL;
	private $ethdb = NULL;
	
	//初始化表
	public function __construct(){
		$this->ethdb = getOtherDb('ethdb');
		$this->outboxtable = 'ebh_outboxs';
		$this->inboxtable = 'ebh_inboxs';
		$this->messagetable = 'ebh_messages';
		$this->replytable = 'ebh_replys';
	}
	
	
	/**
	 * 添加邮件表
	 */
	public function addMessage($param){
		$setarr = array();
		if(!empty($param['send_uid'])){
			$setarr['send_uid'] = $param['send_uid'];
		}
		if(!empty($param['send_user'])){
			$setarr['send_user'] = $param['send_user'];
		}
		if(!empty($param['subject'])){
			$setarr['subject'] = $param['subject'];
		}
		if(!empty($param['message'])){
			$setarr['message'] = $param['message'];
		}
		if(!empty($param['dateline'])){
			$setarr['dateline'] = $param['dateline'];
		}
		if(!empty($param['classids'])){
			$setarr['classids'] = $param['classids'];
		}
		if(!empty($param['crid'])){
			$setarr['crid'] = $param['crid'];
		}
		if(!empty($param['type'])){
			$setarr['type'] = $param['type'];
		}
		if(!empty($param['send_total_num'])){
			$setarr['send_total_num'] = $param['send_total_num'];
		}
		if(!empty($param['send_success_num'])){
			$setarr['send_success_num'] = $param['send_success_num'];
		}
		if(!empty($param['send_error_num'])){
			$setarr['send_error_num'] = $param['send_error_num'];
		}
		if(!empty($param['reply_num'])){
			$setarr['reply_num'] = $param['reply_num'];
		}
		
		return $this->ethdb->insert($this->messagetable,$setarr);		
	}


	/**
	 *添加一条发件记录
	 */
	public function addOutbox($param){
		$setarr = array();
		if(!empty($param['mid'])){
			$setarr['mid'] = $param['mid'];
		}
		if(!empty($param['send_uid'])){
			$setarr['send_uid'] = $param['send_uid'];
		}
		if(!empty($param['send_user'])){
			$setarr['send_user'] = $param['send_user'];
		}
		if(!empty($param['receive_user'])){
			$setarr['receive_user'] = $param['receive_user'];
		}
		if(!empty($param['subject'])){
			$setarr['subject'] = $param['subject'];
		}
		if(!empty($param['classids'])){
			$setarr['classids'] = $param['classids'];
		}
		if(!empty($param['crid'])){
			$setarr['crid'] = $param['crid'];
		}
		if(!empty($param['crname'])){
			$setarr['crname'] = $param['crname'];
		}
		if(!empty($param['dateline'])){
			$setarr['dateline'] = $param['dateline'];
		}
		return $this->ethdb->insert($this->outboxtable,$setarr);
	}
	
	/**
	 * 添加一条收件记录
	 */
	public function addInbox($param){
		$setarr = array();
		if(!empty($param['in_uid'])){
			$setarr['in_uid'] = $param['in_uid'];
		}
		if(!empty($param['in_user'])){
			$setarr['in_user'] = $param['in_user'];
		}
		if(!empty($param['mid'])){
			$setarr['mid'] = $param['mid'];
		}
		if(!empty($param['crid'])){
			$setarr['crid'] = $param['crid'];
		}
		if(!empty($param['status'])){
			$setarr['status'] = $param['status'];
		}
		if(!empty($param['dateline'])){
			$setarr['dateline'] = $param['dateline'];
		}
		
		return $this->ethdb->insert($this->inboxtable,$setarr);
	}
	
	/**
	 * 删除一条收件记录
	 */
	public function delInbox($inid){
		return $this->ethdb->update($this->inboxtable,array('del'=>1),array('inid'=>$inid));
	}
	
	/**
	 * 删除一条回复
	 */
	public function delReply($rid){
		return $this->ethdb->update($this->replytable,array('del'=>1),array('rid'=>$rid));
	}

	/**
	 * 添加一条回复
	 */
	public function addReply($param){
		$setarr = array();
		if(!empty($param['mid'])){
			$setarr['mid'] = $param['mid'];
		}
		if(!empty($param['crid'])){
			$setarr['crid'] = $param['crid'];
		}
		if(!empty($param['comment'])){
			$setarr['comment'] = $param['comment'];
		}
		if(!empty($param['quote'])){
			$setarr['quote'] = $param['quote'];
		}
		if(!empty($param['uid'])){
			$setarr['uid'] = $param['uid'];
		}
		if(!empty($param['dateline'])){
			$setarr['dateline'] = $param['dateline'];
		}
		if(isset($param['type'])){
			$setarr['type'] = $param['type'];
		}
		if(!empty($param['touid'])){
			$setarr['touid'] = $param['touid'];
		}
		return $this->ethdb->insert($this->replytable,$setarr);
	}

	public function editInbox($param){
		$setarr = array();
		if (!empty($param['inid']))
			$inid = $param['inid'];
		else
			return FALSE;
		if (!empty($param['isreply']))
			$setarr['isreply'] = $param['isreply'];
		return $this->ethdb->update($this->inboxtable,$setarr,array('inid'=>$inid));
	}
	
	/**
	 * 消息编辑
	 */
	public function  editMessage($param,$mid,$sparam=array()){
		$setarr = array();
		if(!empty($param['type'])){
			$setarr['type'] = $param['type'];
		}
		if(!empty($param['send_total_num'])){
			$setarr['send_total_num'] = $param['send_total_num'];
		}
		if(!empty($param['send_success_num'])){
			$setarr['send_success_num'] = $param['send_success_num'];
		}
		if(!empty($param['send_error_num'])){
			$setarr['send_error_num'] = $param['send_error_num'];
		}
		if(!empty($param['classids'])){
			$setarr['classids'] = $param['classids'];
		}
		if(!empty($param['del'])){
			$setarr['del'] = $param['del'];
		}
		
		return $this->ethdb->update($this->messagetable,$setarr,array('mid'=>$mid),$sparam);
	}
	
	/**
	 * 发件编辑
	 */
	public function editOutbox($param,$mid,$sparam=array()){
		$setarr = array();
		if(!empty($param['type'])){
			$setarr['type'] = $param['type'];
		}
		if(!empty($param['batchid'])){
			$setarr['batchid'] = $param['batchid'];
		}
		if(!empty($param['classids'])){
			$setarr['classids'] = $param['classids'];
		}
		if(!empty($param['uids'])){
			$setarr['uids'] = $param['uids'];
		}
		if(!empty($param['receive_user'])){
			$setarr['receive_user'] = $param['receive_user'];
		}
		if(!empty($param['crname'])){
			$setarr['crname'] = $param['crname'];
		}
		if(!empty($param['send_total_num'])){
			$setarr['send_total_num'] = $param['send_total_num'];
		}
		
		if(!empty($param['del'])){
			$setarr['del'] = $param['del'];
		}
		
		return $this->ethdb->update($this->outboxtable,$setarr,array('mid'=>$mid),$sparam);
	}
	
	/**
	 * @desc 得到班级学生列表
	 * @param $classid int
	 * @retun array
	 */
	public function getClassStudents($classid)
	{
		//获取班级学生列表
/* 		$sql = 'select cs.uid,u.username,u.realname,u.sex from ebh_classstudents cs '.
				'join ebh_users u on (cs.uid = u.uid) '.
				'where cs.classid='.$classid;
		$rows = $this->db->query($sql)->list_array(); */
		
		//快速方法
		if(is_array($classid)){
			$sql = "select uid ,classid from ebh_classstudents where classid in( ".implode(",", $classid)." )";
		}else{
			$sql = "select uid ,classid from ebh_classstudents where classid=".$classid;
		}
		$rows = $this->db->query($sql)->list_array();
		
		return $rows;
	}
	
	/**
	 * 获取发件箱
	 */
	public function getOutboxList($param){
		$sql = "select  o.* from ".$this->outboxtable." o ";
		$wherearr = array();
		if(!empty($param['send_uid'])){
			$wherearr[] = 'o.send_uid='.$param['send_uid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'o.crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'o.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'o.dateline<='.$param['endtime'];
		}
		$wherearr[] = "o.del = 0";
		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		if(!empty($param['order'])){
			$sql.= ' order by '.$param['order'];
		}else{
			$sql.= ' order by o.dateline desc';
		}
		if(!empty($param['limit'])) {
			$sql .= ' limit '. $param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
		}
		//echo $sql;
		return  $this->ethdb->query($sql)->list_array();
	}
	
	/**
	 * 获取发件记录数
	 */
	public function getOutboxCount($param){
		$sql = "select count(*) count from ".$this->outboxtable." o";
		$wherearr = array();
		if(!empty($param['send_uid'])){
			$wherearr[] = 'o.send_uid='.$param['send_uid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'o.crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'o.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'o.dateline<='.$param['endtime'];
		}
		$wherearr[] = "o.del = 0";
		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		//echo $sql;
		$res = $this->ethdb->query($sql)->row_array();
		return $res['count'];
	}
	
	/**
	 * 查看邮件信息
	 */
	public function getMessage($mid){
		$sql = "select  m.*,o.batchid,o.batch_staus,o.receive_user,o.crname from ".$this->outboxtable." o  left join ".$this->messagetable." m on m.mid = o.mid
				where m.mid = $mid limit 1
				";
		return $this->ethdb->query($sql)->row_array();
	}
	
	
	/**
	 * 获取收件箱消息
	 * @param  integer $inid 收件箱id
	 * @return array       收件箱信息
	 */
	public function getInbox($inid){
		$sql = 'select i.inid,i.in_uid,i.mid,i.isreply,i.del,m.send_uid,m.send_user,m.message from ' . $this->inboxtable . ' i left join ' . $this->messagetable . ' m on m.mid = i.mid where i.inid=' . intval($inid);
		return $this->ethdb->query($sql)->row_array();
	}

	/**
	 * 获取回复详情
	 * @param  integer $rid 回复id
	 * @return array       回复详情
	 */
	public function getReply($rid){
		$sql = 'select r.rid,r.comment,r.del,m.send_uid from ' . $this->replytable . ' r left join ' . $this->messagetable . ' m on m.mid = r.mid where r.rid=' . intval($rid);
		return $this->ethdb->query($sql)->row_array();
	}

	/**
	 * 获取收件箱-记录
	 */
	public function getInboxList($param){
		$sql = "select  m.*,i.* from ".$this->inboxtable." i left join ".$this->messagetable." m on m.mid = i.mid";
		$wherearr = array();
		if(!empty($param['in_uid'])){
			$wherearr[] = 'i.in_uid='.$param['in_uid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'i.crid='.$param['crid'];
		}
		if(!empty($param['send_uid'])){
			$wherearr[] = 'm.send_uid ='.$param['send_uid'];
		}
		if(!empty($param['mid'])){
			$wherearr[] = 'i.mid='.$param['mid'];
		}
		if(isset($param['type'])){
			if ($param['type'] == 1)
				$wherearr[] = 'i.status=1';
			elseif ($param['type'] == 2)
				$wherearr[] = 'i.status=0';
		}
		
		$wherearr[] = "i.del = 0";
		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		if(!empty($param['order'])){
			$sql.= ' order by '.$param['order'];
		}else{
			$sql.= ' order by i.dateline desc';
		}
		if(!empty($param['limit'])) {
			$sql .= ' limit '. $param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
		}
		//echo $sql;
		return  $this->ethdb->query($sql)->list_array();
	}
	/**
	 * 获取回复总数
	 */
	public function getInboxCount($param){
		$sql = "select count(*) count  from ".$this->inboxtable." i left join ".$this->messagetable." m on m.mid = i.mid";
		$wherearr = array();
		if(!empty($param['in_uid'])){
			$wherearr[] = 'i.in_uid='.$param['in_uid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'i.crid='.$param['crid'];
		}
		if(!empty($param['send_uid'])){
			$wherearr[] = 'm.send_uid ='.$param['send_uid'];
		}
		if(!empty($param['mid'])){
			$wherearr[] = 'i.mid='.$param['mid'];
		}
		if(isset($param['type'])){
			if ($param['type'] == 1)
				$wherearr[] = 'i.status=1';
			elseif ($param['type'] == 2)
				$wherearr[] = 'i.status=0';
		}
		
		$wherearr[] = "i.del = 0";
 
		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		//echo $sql;
		$res = $this->ethdb->query($sql)->row_array();
		return $res['count'];
	}

	/**
	 * 获取回复记录
	 */
	public function getReplyList($param){
		$sql = 'select * from '.$this->replytable;
		$wherearr = array();
		if(!empty($param['mid'])){
			$wherearr[] = 'mid='.$param['mid'];
		}
		if(!empty($param['uid'])){
			$wherearr[] = 'uid='.$param['uid'];
		}

		$wherearr[] = "del=0";
		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		if(!empty($param['order'])){
			$sql.= ' order by '.$param['order'];
		}else{
			$sql.= ' order by rid desc';
		}
		if(!empty($param['limit'])) {
			$sql .= ' limit '. $param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
		}
		//echo $sql;
		return  $this->ethdb->query($sql)->list_array();
	}
	/**
	 * 获取收件箱-总数
	 */
	public function getReplyCount($param){
		$sql = 'select count(*) count from '.$this->replytable;
		$wherearr = array();
		if(!empty($param['mid'])){
			$wherearr[] = 'mid='.$param['mid'];
		}
		if(!empty($param['uid'])){
			$wherearr[] = 'uid='.$param['uid'];
		}

		$wherearr[] = "del=0";

		if(!empty($wherearr)){
			$sql .= ' WHERE '.implode(' AND ', $wherearr);
		}
		//echo $sql;
		$res = $this->ethdb->query($sql)->row_array();
		return $res['count'];
	}
	
	/**
	 * 获取uid的父母的openid
	 */
	public function getParentOpenid($uid){
		$sql = "select p.openid,b.crid from ebh_parents p,ebh_binds b where b.pid = p.pid and b.uid = {$uid} ";
		return $this->ethdb->query($sql)->list_array();
	}
	
	/**
	 * 获取uid的父母的pid
	 */
	public function getParentPid($uid){
		$sql = "select pid from ebh_binds where uid = {$uid} ";
		return $this->ethdb->query($sql)->list_array();
	}

	/**
	 * 更新家长
	 */
	public function editParent($param,$pid,$sparam=array()){
		$setarr = array();
		return $this->ethdb->update('ebh_parents',$setarr,array('pid'=>$pid),$sparam);
	}
	
	/**
	 * 未读数+1
	 */
	public function incrNoread($openid){
		$sql = "update ebh_parents set noread = noread + 1 where openid = ".$this->ethdb->escape($openid);
		return $this->ethdb->simple_query($sql);
	}
	

	/**
	 * 获取微信配置
	 */
	public function getConfigByCrid($crid = 0){
		if($crid > 0){
			$sql = "select * from ebh_wxt_configs where crid = {$crid} and `del`=0 limit 1";
		}else{
			$sql = "select * from ebh_wxt_configs where cid = 1 and `del`=0";
		}
		$config = $this->ethdb->query($sql)->row_array();
	
		//获取模板配置
		if (!empty($config)){
			$msql = "select * from ebh_template_configs where cid = {$config['cid']} and `del`=0 limit 1 ";
			$template = $this->ethdb->query($msql)->row_array();
			$config['template'] = $template;
		}
		return $config;
	}

	/**
	 * 保存设置
	 */
	public function saveSetting($param){
		$setarr = array();
		$tplarr = array();
		if (empty($param['crid']))
			return FALSE;
		else
			$setarr['crid'] = intval($param['crid']);

		if (!empty($param['marks']))
			$setarr['marks'] = $param['marks'];
		if (!empty($param['appID']))
			$setarr['appID'] = $param['appID'];
		if (!empty($param['appsecret']))
			$setarr['appsecret'] = $param['appsecret'];
		if (!empty($param['token']))
			$setarr['token'] = $param['token'];
		if (!empty($param['server_url']))
			$setarr['server_url'] = $param['server_url'];
		if (!empty($param['domain']))
			$setarr['domain'] = $param['domain'];
		if (!empty($param['ebhcode']))
			$setarr['ebhcode'] = $param['ebhcode'];
		if (!empty($param['phone']))
			$setarr['phone'] = $param['phone'];
		if (isset($param['isvalid']))
			$setarr['isvalid'] = $param['isvalid'];
		if (isset($param['ismenu']))
			$setarr['ismenu'] = $param['ismenu'];
		if (isset($param['wechat'])) {
		    $setarr['wechat'] = $param['wechat'];
        }

		$tplarr['crid'] = $setarr['crid'];
		if (!empty($param['tempid']))
			$tplarr['tempid'] = $param['tempid'];
		$tplarr['title'] = '学校通知';
		$tplarr['data'] = '{{first.DATA}} 学校：{{keyword1.DATA}} 通知人：{{keyword2.DATA}} 时间：{{keyword3.DATA}} 通知内容：{{keyword4.DATA}} {{remark.DATA}} ';

		$config = $this->getConfigByCrid($setarr['crid']);
		if (!empty($config)){
			$cid = $config['cid'];
			$this->ethdb->update('ebh_wxt_configs', $setarr, 'cid='.$cid);
			$this->ethdb->update('ebh_template_configs', $tplarr, 'cid='.$cid);
		} else {
			$cid = $this->ethdb->insert('ebh_wxt_configs', $setarr);
			$tplarr['cid'] = $cid;
			$tid = $this->ethdb->insert('ebh_template_configs', $tplarr);
		}

		return $cid;
	}
	
	/**
	 * 更新批次状态
	 */
	public function updateBatchStatus($batachid,$batch_staus = 1){
		$sql = "update ".$this->outboxtable." set batch_staus = ".$batch_staus ." where batchid  = ".$this->ethdb->escape($batachid);
		$this->ethdb->simple_query($sql);
		return  $this->ethdb->affected_rows();
	}
	
	/**
	 * 获取用户基本信息
	 * 包含有个人简介,头像,性别,关注,粉丝等
	 *
	 */
	public function getUserInfo($users,$keys="uid"){
		if(empty($users[0])) return false;
		$uidarr = array();
		foreach($users as $user){
			array_push($uidarr, $user[$keys]);
		}
		$sql = "select u.uid,u.username,u.balance,u.realname,u.nickname,u.sex,u.face,u.credit,u.mysign,u.groupid, m.hobbies,m.profile from ebh_users u left join ebh_members m on m.memberid = u.uid where u.uid in ( ".implode(",",$uidarr)." )";
		$infots = $this->db->query($sql)->list_array();
	
		foreach($users as $key=>&$user){
			//组装ebh库信息
			foreach($infots as $infot){
				if(!empty($infot)&&$infot['uid']==$user[$keys]){
					unset($infot['uid']);
					$user = array_merge($user,$infot);
					break;
				}else{
					continue;
				}
			}
		}
		return $users;
	}
	
	
 	 /**
	 * @desc 得到班级学生列表，（包含微信号信息）
	 * @param $classid int
	 * @retun array
	 */
    public function getClassStudentWithBindList($param){
    	$classid = $param['classid'];
    	$crid = $param['crid'];
    	$where = array();
		//获取班级学生列表
		$sql = 'select distinct(cs.uid),u.username,u.realname,u.sex,u.face,u.groupid,u.email,u.mobile from ebh_classstudents cs '
				.' left join ebh_users u on (cs.uid = u.uid) '
				.' left join ebh_roomusers r on (r.uid = cs.uid) and r.crid = '.$param['crid']
			;
		if(!empty($param['classid'])){
			if(is_array($param['classid'])){
				$where[] = " cs.classid IN ( ".implode(",", $param['classid']).") "; 
			}else{
				$where[] = " cs.classid = {$param['classid']} ";
			}
		}
		if(!empty($param['uidarr']) && is_array($param['uidarr'])){
			//存在性能问题---
			$where[] = " u.uid IN ( ".implode(",", $param['uidarr']).") ";
		}
		if(!empty($param['nouidarr']) && is_array($param['nouidarr'])){
			//存在性能问题---
			$where[] = " u.uid NOT IN ( ".implode(",", $param['nouidarr']).") ";
		}
		if(!empty($param['condition'])){
			$where[] = $param['condition'];
		}
		if(!empty($param['crid'])){
			$where[] =" r.crid = ".$param['crid'];
		}
		if(!empty($where)){
			$sql.=" WHERE ".implode(" AND ", $where);
		} 
		if(!empty($param['order']))
			$sql .= ' order by '.$param['order'];
		else
			$sql .= ' order by u.uid desc';
		if(!empty($param['limit'])) {
			$sql .= ' limit '. $param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
		}
		//log_message($sql);
		$rows = $this->db->query($sql)->list_array();	
		
		//获取绑定详情
		$uidArr = array();
		$bindRows = array();
		$bindProws = array();
		if(!empty($rows)){
			foreach ($rows as $row){
				array_push($uidArr, $row['uid']);
			}
		}
		if(!empty($uidArr)){
			$bsql = "select bid,uid,crid from ebh_binds where uid IN( ".implode(",", $uidArr)." ) and crid = {$crid}";
			$bindRows = $this->ethdb->query($bsql)->list_array();
			if(!empty($bindRows)){
				foreach($bindRows as $bind){
					if(!empty($bindProws[$bind['uid']])){
						$bindProws[$bind['uid']].=$bind['bid'].",";
					}else{
						$bindProws[$bind['uid']] =$bind['bid'].",";
					}
				}
			}
		}
		
		if(!empty($bindProws)&&!empty($rows)){
			foreach($rows as &$row){
				$binds = !empty($bindProws[$row['uid']])? $bindProws[$row['uid']] :"" ;
				$row['bind'] = $binds;
			}
		}

		return  $rows;
    }
    
    /**
     * 获取班级总数
     */
    public function getClassStudentWithBindCount($param){
    	$where = array();
    	//获取班级学生列表
    	$sql = 'select count(distinct(cs.uid))  count from ebh_classstudents cs '.
    			' left join ebh_users u on (cs.uid = u.uid) '
    			.' left join ebh_roomusers r on (r.uid = cs.uid) and r.crid = '.$param['crid']		
    	;
    	if(!empty($param['classid'])){
    		if(is_array($param['classid'])){
    			$where[] = " cs.classid IN ( ".implode(",", $param['classid']).") ";
    		}else{
    			$where[] = " cs.classid = {$param['classid']} ";
    		}
    	}
    	if(!empty($param['uidarr']) && is_array($param['uidarr'])){
    		//存在性能问题---
    		$where[] = " u.uid IN ( ".implode(",", $param['uidarr']).") ";
    	}
    	if(!empty($param['nouidarr']) && is_array($param['nouidarr'])){
    		//存在性能问题---
    		$where[] = " u.uid NOT IN ( ".implode(",", $param['nouidarr']).") ";
    	}
    	if(!empty($param['condition'])){
    		$where[] = $param['condition'];
    	}
    	if(!empty($param['crid'])){
    		$where[] =" r.crid = ".$param['crid'];
    	}
    	if(!empty($where)){
    		$sql.=" WHERE ".implode(" AND ", $where);
    	}
 
    	//echo $sql;
    	$row = $this->db->query($sql)->row_array();
    	
    	return $row['count'];
    }
    
    /**
     * 获取已经绑定的学生
     */
    public function getBindStudent($crid){
    	$sql = "select distinct uid from ebh_binds where crid = {$crid}";
    	$rows = $this->ethdb->query($sql)->list_array();
    	return $rows;
    }
    
}
<?php
/**
 * CreditModel 积分相关model类，此类还需优化.
 * Author: lch
 * Email: 15335667@qq.com
 */
class CreditModel{
	private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }

	/*
	添加积分日志,并修改积分
	@param array $param ruleid, toid/aid..
	*/
	public function addCreditlog($param) {
		$logarr['ruleid'] = $param['ruleid'];
		$crid = $param['crid'];
		if(isset($param['uid'])){
            $logarr['uid'] = $param['uid'];
        }elseif (isset($param['loginuid'])){
            $logarr['uid'] = $param['loginuid'];
        }

		$flag = 0;
		if(!empty($param['uid'])){//指定了受分对象的
			$logarr['toid'] = $param['uid'];
		}else if(!empty($param['aid'])){//指定了答疑号的,被采纳为最佳答案
			$sql = 'select q.uid,a.uid toid,q.title,q.qid from ebh_askanswers a 
				join ebh_askquestions q on (q.qid=a.qid)';
			$warr[] = 'a.aid='.$param['aid'];
			// $warr[] = 'q.uid='.$logarr['uid'];
			$sql.= ' where '.implode(' AND ',$warr);
			$temp = $this->db->query($sql)->row_array();
			//var_dump($sql);
			
			$logarr['uid'] = empty($param['qid']) ? $temp['qid'] : $param['qid'];//记录qid
			$logarr['toid'] = $temp['toid'];
			$logarr['type'] = 3;
			$logarr['detail'] = $temp['title'];
		}else if(!empty($param['qid'])){//指定了qid的,回答问题
			$sql = 'select q.title,q.qid from ebh_askquestions q';
			$warr[] = 'q.qid='.$param['qid'];
			$sql.= ' where '.implode(' AND ',$warr);
			$temp = $this->db->query($sql)->row_array();
			$logarr['toid'] = $logarr['uid'];
			$logarr['uid'] = $temp['qid'];
			$logarr['detail'] = $temp['title'];
			$logarr['type'] = 3;
		}else if(!empty($param['eid'])){//指定了eid的,完成作业
			$sql = 'select crid,totalscore/score*100 percent from ebh_schexams e 
					join ebh_schexamanswers a on e.eid=a.eid ';
			$warr[] = 'e.eid='.$param['eid'];
			$warr[] = 'a.uid='.$logarr['uid'];
			$sql.= ' where '.implode(' AND ',$warr);
			$temp = $this->db->query($sql)->row_array();
			if($temp['percent']==100)
				$param['credit'] = 10;
			elseif($temp['percent']>=80)
				$param['credit'] = 7;
			elseif($temp['percent']>=60)
				$param['credit'] = 6;
			else
				$param['credit'] = 5;
			$logarr['crid'] = $temp['crid'];
			$logarr['toid'] = $logarr['uid'];
			$logarr['detail'] = $param['detail'];
			$logarr['type'] = 4;
		}elseif(!empty($param['cwid']) && $param['ruleid'] != 5){
			$sql = 'select cw.title,cw.cwid,cw.uid from ebh_coursewares cw';
			$warr[] = 'cw.cwid='.$param['cwid'];
			$sql.= ' where '.implode(' AND ',$warr);
			$temp = $this->db->query($sql)->row_array();
			$logarr['uid'] = $param['cwid'];
			$logarr['detail'] = $temp['title'];
			$logarr['toid'] = $temp['uid'];
			// $logarr['type'] = 0;
		}else{//没有指定，则为自己
			$logarr['toid'] = $logarr['uid'];
		}
		$ruleinfo = $this->getCreditRuleInfo($logarr['ruleid']);
		//每次都增加
		if($ruleinfo['actiontype'] == 0){
			$flag = 1;
		}
		//只一次
		elseif($ruleinfo['actiontype'] == -1){
			$wherearr['toid'] = $logarr['toid'];
			$wherearr['ruleid'] = $logarr['ruleid'];
			$logcount = $this->getUserCreditCount($wherearr);
			if($logcount>0)
				return ;
			else{
				$flag=1;
			}
		}
		//每天增加有限次数
		elseif($ruleinfo['actiontype'] == -2){
			$today = strtotime(Date('Y-m-d'));
			$wherearr['toid'] = $logarr['toid'];
			$wherearr['ruleid'] = $logarr['ruleid'];
			$wherearr['dateline'] = ' dateline>'.$today.' and dateline<'.($today+86400);
			$logcount = $this->getUserCreditCount($wherearr);
			if($logcount>=$ruleinfo['maxaction']){
				if(!empty($param['nocheck'])&&($param['nocheck']==true)){//抽奖再来一次不需要检测最大次数;权限由控制器给出
					$flag=1;
				}else{
					return ;
				}
				
			}else{
				$uniqueconfirm = 0;
				if(!empty($param['cwid'])){
					$wherearr['uid'] = $param['cwid'];
					$wherearr['type'] = 2;
					$uniqueconfirm = 1;
				}elseif(!empty($param['qid'])){
					$wherearr['uid'] = $param['qid'];
					$wherearr['type'] = 3;
					$uniqueconfirm = 1;
				}
				if($uniqueconfirm){
					$logcount = $this->getUserCreditCount($wherearr);
					if($logcount>0)
						return;
					else{
						$logarr['type'] = $wherearr['type'];
						$logarr['uid'] = $wherearr['uid'];
					}
				}
				$flag=1;
			}
		}
		
		//课件特殊处理,学一次只得一次积分,改天再学也没有积分
		elseif($ruleinfo['actiontype'] == 1 && !empty($param['cwid'])){
			$wherearr = array();
			$wherearr['toid'] = $logarr['toid'];
			$wherearr['ruleid'] = $logarr['ruleid'];
			$wherearr['uid'] = $param['cwid'];
			$wherearr['type'] = 2;
			$logcount = $this->getUserCreditCount($wherearr);
			if($logcount>0)
				return;
			else{
				$logarr['type'] = $wherearr['type'];
				$logarr['uid'] = $wherearr['uid'];
				$flag = 1;
			}
		}
		//按时间段增加
		else{
			return;
		}
		
		//添加记录并增加toid的积分
		if($flag){
			if($logarr['ruleid'] == 16 && isset($param['productid']) && isset($param['credit'])){//积分兑换
				$logarr['credit'] = $param['credit'];
				$logarr['productid'] = $param['productid'];
			}
			elseif(isset($param['credit']))
				$logarr['credit'] = $param['credit'];
			else
				$logarr['credit'] = $ruleinfo['credit'];
			$logarr['dateline'] = SYSTIME;
			$logarr['fromip'] = getip();
			if(!empty($param['detail']))
				$logarr['detail'] = $param['detail'];
			
			//活动id添加crid
			$actids = array(5,7,13,14,15,21);
			if(in_array($logarr['ruleid'],$actids)){
				$logarr['crid'] = empty($logarr['crid'])?$crid:$logarr['crid'];
				$tsql = 'select logid from ebh_studentactivitys sa 
						join ebh_activitys a on sa.aid=a.aid';
				$twhere[] = 'uid='.$logarr['toid'];
				$twhere[] = 'sa.crid='.$logarr['crid'];
				$twhere[] = 'endtime+86400>'.SYSTIME;//截止日的23:59
				$twhere[] = 'starttime<='.SYSTIME;
				$tsql .= ' where '.implode(' AND ',$twhere);
				$actloglist = $this->db->query($tsql)->list_array();
				// var_dump($actloglist);
				// log_message($tsql);
				if($actloglist){
					$logarr['isact'] = 1;
					$actlogids = '';
					foreach($actloglist as $actlog){
						$actlogids .= $actlog['logid'].',';
					}
					$actlogids = rtrim($actlogids,',');
					$tuwhere = 'logid in ('.$actlogids.')';
					$this->db->update('ebh_studentactivitys',array(),$tuwhere,array('credit'=>'credit+'.$logarr['credit']));
				
				}
			}
			if(!empty($param['qid']) && $param['ruleid'] == 33){//屏蔽问题，扣除积分
				$sql = 'select u.credit,q.uid,q.title from `ebh_users` u left join `ebh_askquestions` q on(q.uid = u.uid) where q.qid ='.intval($param['qid']);
				$credit = $this->db->query($sql)->row_array();
				if(!empty($credit) && $credit['credit'] > 0){
					$logarr = array(
							'ruleid' => $param['ruleid'],
							'uid' => $param['qid'],
							'toid' => $credit['uid'],
							'credit' => '1',
							'dateline' => SYSTIME,
							'fromip' => getip(),
							'detail' => $credit['title'],
							'type' => 5
						);
					$res = $this->db->insert('ebh_creditlogs',$logarr);
					$sparam = array('credit'=>'credit'.$ruleinfo['action'].$logarr['credit']);
					$this->db->update('ebh_users',array(),'uid='.$credit['uid'],$sparam);
					return true;
				}
			}elseif(!empty($param['aid']) && $param['ruleid'] == 34){//屏蔽回答，扣除积分
				$sql = 'select u.credit,a.uid from `ebh_users` u left join `ebh_askanswers` a on(a.uid = u.uid) where a.aid ='.intval($param['aid']);
				$credit = $this->db->query($sql)->row_array();
				if(!empty($credit) && $credit['credit'] > 0){
					$logarr = array(
							'ruleid' => $param['ruleid'],
							'uid' => $param['aid'],
							'toid' => $credit['uid'],
							'credit' => '1',
							'dateline' => SYSTIME,
							'fromip' => getip(),
							'detail' => $logarr['detail'],
							'type' => 5
						);
					$res = $this->db->insert('ebh_creditlogs',$logarr);
					$sparam = array('credit'=>'credit'.$ruleinfo['action'].$logarr['credit']);
					$this->db->update('ebh_users',array(),'uid='.$credit['uid'],$sparam);
					return true;
				}
			}
			// exit;
			$res = $this->db->insert('ebh_creditlogs',$logarr);
			$sparam = array('credit'=>'credit'.$ruleinfo['action'].$logarr['credit']);
			$this->db->update('ebh_users',array(),'uid='.$logarr['toid'],$sparam);
			//以前老代码，每次更新积分会写统计的按天处理每个网校积分记录，本次不做处理，可以放到定时任务中处理
			/**
			if($ruleinfo['action'] == '+' && $logarr['ruleid'] != 29){
				$redis = $redis = Ebh::app()->getCache('cache_redis');
				$crcache = $redis->hget('credit',$crid);
				if(!is_array($crcache))
					$crcache = unserialize($crcache);
				$day = Date('Y/m/d',SYSTIME);
				if(isset($crcache[$day]))
					$crcache[$day] += $logarr['credit'];
				else
					$crcache[$day] = $logarr['credit'];
				if(!empty($crid))
					$redis->hset('credit',$crid,$crcache);
			}
			**/
			return $res;
		}
	}
	/*
	根据ruleid查看积分规则信息
	@param int $ruleid
	*/
	public function getCreditRuleInfo($ruleid){
		$sql = 'select r.rulename,r.action,r.credit,r.actiontype,r.maxaction
			from ebh_creditrules r where r.ruleid='.$ruleid;
		return $this->db->query($sql)->row_array();
	}
	
	/*
	积分记录数量
	@param int $uid
	*/
	public function getUserCreditCount($param){
		$wherearr = array();
		$sql = 'select count(*) count from ebh_creditlogs';
		if(!empty($param['uid']))
			$wherearr[]= 'uid='.$param['uid'];
		if(!empty($param['toid']))
			$wherearr[]= 'toid='.$param['toid'];
		if(!empty($param['ruleid']))
			$wherearr[]= 'ruleid='.$param['ruleid'];
		if(!empty($param['credit']))
			$wherearr[]= 'credit='.$param['credit'];
		if(!empty($param['dateline']))//特殊条件
			$wherearr[]= $param['dateline'];
		if(!empty($param['type']))
			$wherearr[]= 'type='.$param['type'];
		if(!empty($param['crid']))
			$wherearr[] = 'crid='.$param['crid'];
		if(!empty($param['isact']))
			$wherearr[] = 'isact='.$param['isact'];
		if(!empty($param['datefrom']))
			$wherearr[] = 'dateline>'.$param['datefrom'];
		if(!empty($param['dateto']))
			$wherearr[] = 'dateline<='.$param['dateto'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		// log_message($sql);
		// echo $sql;
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
}
?>
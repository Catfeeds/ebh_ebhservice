<?php
/**
 * 解冻分享的钱
 */
class UnfreezeTaskController extends Controller{
    private $successCount = 0;  //执行成功的日志数
    private $failCount = 0; //执行失败的日志数
    private $failUid = ''; //执行失败的日志数
    public function init(){
        Ebh()->filter = 'Filter_Server';    //验证IP来源
        parent::init();
        $this->db = Ebh()->db;
    }
    public function unFreezeAction() {
    	$begin = microtime(true);
    	set_time_limit(0);
    	//return true;
        //分页获取所有被冻结的资金
        $param['pagesize'] = 200;//每次执行多少条
        $param['isfreeze'] = 1;//冻结
        $param['type'] = 12;//分享类型
        $param['value'] = 0;//金额要大于0
        $param['status'] = 1;//已生效

        if (isset($param['isfreeze'])) {
			$whereArr[] = 'isfreeze = '.$param['isfreeze'];
		}
		if (isset($param['type'])) {
			$whereArr[] = 'type = '.$param['type'];
		}
		if (isset($param['status'])) {
			$whereArr[] = 'status = '.$param['status'];
		}
		if (isset($param['value'])) {
			$whereArr[] = 'value > '.$param['value'];
		}
       
        $sql = 'SELECT chargeid,value,useuid as uid,paytime,buyer_id as crid FROM ebh_charges ';
		$countsql = 'SELECT count(1) as c FROM ebh_charges ';
		if (!empty($whereArr)) {
			$countsql.= ' WHERE '.implode(' AND ',$whereArr);
			$sql.= ' WHERE '.implode(' AND ',$whereArr);
		}

		$count = $this->db->query($countsql)->row_array();
		$sum = $count['c'];
		if (empty($sum)) {
            $msg = '1|成功执行'.$this->successCount.'条|0';
            log_message($msg);
			return $msg;
		}
		$totalPage = $sum/$param['pagesize'];
		for ($i=0; $i < $totalPage; $i++) {
			$listSql = $sql.' limit '.$i*$param['pagesize'].','.$param['pagesize'];
			$this->runData($listSql);
		}
		$end = microtime(true);
		$usetime = round($end - $begin,6);
        if ($this->failCount > 0) {
            $msg = '0|成功执行'.$this->successCount.'条,失败执行'.$this->failCount.'条, 失败uid '.$this->failUid .'|'.$usetime;
        } else {
            $msg = '1|成功执行'.$this->successCount.'条|'.$usetime;
        }
        log_message($msg);
        echo $msg;exit;
    }

    //执行数据
    public function runData($sql='') {
    	if (empty($sql)) {
    		return true;
    	}
    	$today = strtotime('today')+86400;
    	$allFreezes = $this->db->query($sql)->list_array();
        if (empty($allFreezes)) {
            return true;
        } else {
            $cridstr = '';
            $uidstr = '';
            $chargeidstr = '';
            $uid_map = array();
            foreach ($allFreezes as $value) {
            	$cridArr[$value['crid']] = 1;
                $uid_map[$value['uid']]['fee'] = 0;
            }
            $cridstr = implode(',', array_unique(array_keys($cridArr)));
            $freeze_crid = array();
            //获取冻结的天数
            $freezeDayInfos = $this->db->query('SELECT crid,fund_freezn FROM ebh_freezn_times WHERE crid IN('.$cridstr.') ORDER BY fid DESC')->list_array();
            if (!empty($freezeDayInfos)) {
                foreach ($freezeDayInfos as $fvalue) {
                    $freeze_crid[$fvalue['crid']] = $fvalue['fund_freezn'];
                }
            }
            //获取时间符合有需要解冻的用户
            foreach ($allFreezes as $avalue) {
                $frozeDay = empty($freeze_crid[$avalue['crid']])?15:$freeze_crid[$avalue['crid']];//某用户的冻结天数
                if (($today - $frozeDay*86400) > $avalue['paytime']) {//解冻了15天后
                    $uid_map[$avalue['uid']]['fee'] += $avalue['value'];//钱的累加
                    $chargeidstr .= $avalue['chargeid'].',';
                    $uidArr[$avalue['uid']] = 1;
                }
            }
            if (empty($uidArr)) {
                return true;
            }
            $uidstr = implode(',', array_unique(array_keys($uidArr)));
            if (!empty($chargeidstr) && !empty($uidstr)) {//开始处理用户的，冻结资金和余额以及此处的冻结状态解封
                //获取用户当前的余额，和冻结资金
                $users = $this->db->query('SELECT uid,balance,freezebalance FROM ebh_users WHERE uid IN ('.$uidstr.')')->list_array();
                foreach ($users as $bvalue) {
                    $userBalance[$bvalue['uid']] = $bvalue;
                }
                foreach ($uid_map as $ukey => $uvalue) {
                    if ($uvalue['fee'] > 0) {
                        $uvalue['balance'] = $userBalance[$ukey]['balance'] + $uvalue['fee'];//解冻的金额加上
                        $uvalue['freezebalance'] = $userBalance[$ukey]['freezebalance'] - $uvalue['fee'];//冻结的金额扣除
                        if ($uvalue['freezebalance'] < 0) {
                        	$uvalue['freezebalance'] = 0;
                        	log_message('uid 为'.$ukey.' 分享的钱解冻，总的冻结资金不够扣');
                            $this->failUid .= $ukey.',';
                            $this->failCount += 1;
                        	continue;
                        }
                        $updateArr[$ukey] = $uvalue;
                    }
                }
                if (empty($updateArr)) {
                	return false;//有些人的冻结资金不够付了
                }
                //金额更新操作
                $this->db->begin_trans();
                $this->db->query('UPDATE ebh_charges SET isfreeze=0 WHERE chargeid IN ('.substr($chargeidstr,0,-1).') AND isfreeze=1 AND type= 12 AND value>0 AND status=1');

                $ids = implode(',', array_keys($updateArr));
                $sql = '';
                $commonSet = "UPDATE ebh_users SET balance = CASE uid ";
                $goodSet = " freezebalance = CASE uid ";
                foreach ($updateArr as $id => $ordinal) {
                    $commonSet .= sprintf("WHEN %.2f THEN %.2f ", $id, $ordinal['balance']);
                    $goodSet .= sprintf("WHEN %.2f THEN %.2f ", $id, $ordinal['freezebalance']);
                }
                $sql .= $commonSet.' END, '. $goodSet;
                $sql .= " END WHERE uid IN ($ids)";
                $res = $this->db->query($sql);

                if($this->db->trans_status() === FALSE){
                    $this->failCount += count($updateArr);
                    $this->failUid .= $ids.',';
                    $this->db->rollback_trans();
                    return false;
                }else{
                    $this->successCount += count($updateArr);
                    $this->db->commit_trans();
                    return true;
                }
            } else {
                return true;
            }   
        }
    }




}


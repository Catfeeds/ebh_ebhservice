<?php
/**
 * 操作日志
 * Author: ckx
 */
class LogModel{
	private $db;
	public function __construct() {
		$this->db = Ebh()->db;
	}
	/*
	添加日志
	*/
	public function add($param){
		if (empty($param['uid']) || empty($param['opid']) || empty($param['toid']) || empty($param['type']))
			return FALSE;
		$setarr['uid'] = intval($param['uid']);
		$setarr['opid'] = intval($param['opid']);
		$setarr['toid'] = intval($param['toid']);
		$setarr['type'] = $this->db->escape_str($param['type']);
		if (!empty($param['message'])) {
			$setarr['message'] = $this->db->escape_str($param['message']);
		}
		if (!empty($param['value'])) {
			$setarr['value'] = intval($param['value']);
		}
		if (!empty($param['credit'])) {
			$setarr['credit'] = intval($param['credit']);
		}
		if (!empty($param['fromip'])) {
			$setarr['fromip'] = $this->db->escape_str($param['fromip']);
		}
		if (!empty($param['crid'])) {
			$setarr['crid'] = intval($param['crid']);
		}
		if (!empty($param['detailtype'])) {
			$setarr['detailtype'] = $this->db->escape_str($param['detailtype']);
		}
		$setarr['dateline'] = SYSTIME;
		$logid = $this->db->insert('ebh_logs', $setarr);
		return $logid;
	}
	
	/*
	日志列表
	*/
	public function getLogList($param){
		$sql = 'select l.uid,u.username,u.realname,u.sex,u.face,l.detailtype,l.message,l.dateline,l.logid,l.fromip from ebh_logs l
				join ebh_users u on l.uid=u.uid';
		if(!empty($param['crid'])){
			$wherearr[] = 'l.crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'l.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'l.dateline<='.$param['endtime'];
		}
		if(!empty($param['detailtype'])){
			$wherearr[] = 'l.detailtype=\''.$this->db->escape_str($param['detailtype']).'\'';
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = "(u.username like '%$q%' or u.realname like '%$q%' or l.message like '%$q%')";
		}
		if(!empty($wherearr)){
			$sql.= ' where '.implode(' AND ',$wherearr);
		}
		$sql.= ' order by l.logid desc';
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
		return $this->db->query($sql)->list_array();
	}
	
	/*
	日志数量
	*/
	public function getLogCount($param){
		$sql = 'select count(*) count from ebh_logs l
				join ebh_users u on l.uid=u.uid';
		if(!empty($param['crid'])){
			$wherearr[] = 'crid='.$param['crid'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'l.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'l.dateline<='.$param['endtime'];
		}
		if(!empty($param['detailtype'])){
			$wherearr[] = 'l.detailtype=\''.$this->db->escape_str($param['detailtype']).'\'';
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = "(u.username like '%$q%' or u.realname like '%$q%' or l.message like '%$q%')";
		}
		if(!empty($wherearr)){
			$sql.= ' where '.implode(' AND ',$wherearr);
		}
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}

    /**
     * 根据条件获取最后一次评论等日志的时间
     */
    public function getLastLogTime($param) {
        $lasttime = 0;
        $sql = 'select l.dateline from ebh_logs l';
        $wherearr = array();
        if(!empty($param['logid'])) {
            $wherearr[] = 'l.logid = '.$param['logid'];
        }
        if(!empty($param['uid'])) {
            $wherearr[] = 'l.uid = '.$param['uid'];
        }
        if(!empty($param['toid'])) {
            $wherearr[] = 'l.toid = '.$param['toid'];
        }
        if(!empty($param['opid'])) {
            $wherearr[] = 'l.opid = '.$param['opid'];
        }
        if(!empty($param['value'])) {
            $wherearr[] = 'l.value = '.$param['value'];
        }
        if(!empty($param['type'])) {
            $wherearr[] = 'l.type = \''.$param['type'].'\'';
        }
        if(empty($wherearr))
            return FALSE;
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        $sql .= ' order by l.logid desc ';
        $row = $this->db->query($sql)->row_array();
        if(!empty($row))
            $lasttime = $row['dateline'];
        return $lasttime;
    }

    /**
     * 通过条件获取最后一次评论时间列表
     * @param $param
     * @return array
     */
    public function getLaseLogTimeList($param){
        $lasttime = 0;
        $sql = 'select l.toid,l.dateline from ebh_logs l';
        $wherearr = array();
        if(!empty($param['logid'])) {
            $wherearr[] = 'l.logid = '.$param['logid'];
        }
        if(!empty($param['uid'])) {
            $wherearr[] = 'l.uid = '.$param['uid'];
        }
        if(!empty($param['toid'])) {
            $wherearr[] = 'l.toid in ('.$param['toid'].')';
        }
        if(!empty($param['opid'])) {
            $wherearr[] = 'l.opid = '.$param['opid'];
        }
        if(!empty($param['value'])) {
            $wherearr[] = 'l.value = '.$param['value'];
        }
        if(!empty($param['type'])) {
            $wherearr[] = 'l.type = \''.$param['type'].'\'';
        }
        if(empty($wherearr))
            return array();
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        $sql .= ' order by l.logid desc ';
        return $this->db->query($sql)->list_array('toid');

    }
}
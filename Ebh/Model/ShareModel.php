<?php
/**
 * 分销
 * User: ckx
 */
class ShareModel{
	private $db;
	public function __construct() {
		$this->db = Ebh()->db;
	}
	/*
	获取通用设置
	*/
	public function getSettings($crid){
		$sql = 'select isshare,sharepercent from ebh_systemsettings where crid='.intval($crid);
		$res = $this->db->query($sql)->row_array();
		if(empty($res)){
			$res['isshare'] = 0;
			$res['sharepercent'] = 0;
		}
		return $res;
	}
	/**
	 * 编辑基本设置
	 */
	public function upSettings($param){
		if(empty($param['crid'])){
			return FALSE;
		}
        $param['crid'] = intval($param['crid']);
		$sql = 'select 1 from ebh_systemsettings where crid='.$param['crid'];
		$res = $this->db->query($sql)->row_array();
		if(isset($param['isshare'])){
			$setarr['isshare'] = intval($param['isshare']);
		}
		if(isset($param['sharepercent'])){
			$setarr['sharepercent'] = intval($param['sharepercent']);
		}
		if(empty($setarr)){
			return FALSE;
		}
		if(empty($res)){
			$setarr['crid'] = $param['crid'];
			return $this->db->insert('ebh_systemsettings',$setarr);
		} else {
			$wherearr['crid'] = $param['crid'];
			return $this->db->update('ebh_systemsettings',$setarr,$wherearr);
		}
	}
	
	/*
	添加用户设置
	*/
	public function addUserPercent($param){
		if(empty($param['uids']) || empty($param['crid'])){
			return FALSE;
		}
		$olduids = $this->getUserPercent($param);
		$olduids = array_keys($olduids);
		$newuids = $param['uids'];
		$truenewuids = array_diff($newuids,$olduids);
		$rows = 0;
		if(!empty($truenewuids)){
			$crid = intval($param['crid']);
			$percent = empty($param['percent'])?0:($param['percent']>100?100:intval($param['percent']));
			$insertssql = 'insert into ebh_usershares (uid,crid,percent) values ';
			$valuearr = array();
			foreach($truenewuids as $uid){
                $uid = intval($uid);
                if(!empty($uid)){
                    $valuearr[]= "($uid,$crid,$percent)";
                }
			}
			$insertssql.= implode(',',$valuearr);
			$this->db->query($insertssql);
			$rows = $this->db->affected_rows();
		}
		return $rows;
	}
	/*
	编辑用户设置
	*/
	public function editUserPercent($param){
		if(empty($param['did'])){
			return FALSE;
		}
		$setarr['percent'] = empty($param['percent'])?0:($param['percent']>100?100:intval($param['percent']));
		$wherearr['did'] = intval($param['did']);
		return $this->db->update('ebh_usershares',$setarr,$wherearr);
	}
	/*
	删除用户分销比设置
	*/
	public function delUserPercent($param){
		if(empty($param['did'])){
			return FALSE;
		}
        $wherearr['did'] = intval($param['did']);
        $setarr['status'] = 2;
		$ret = $this->db->update('ebh_usershares',$setarr,$wherearr);
		if(!empty($ret)){
		    return TRUE;
        }else{
            return FALSE;
        }
	}
	/*
	获取用户设置
	*/
	public function getUserPercent($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select uid,percent from ebh_usershares';
        $wherearr[] = 'crid='.intval($param['crid']);
        $wherearr[] = 'status=0';
		if(!empty($param['uids']) && is_array($param['uids'])){
			$wherearr[] = 'uid in('.implode(',',$param['uids']).')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		return $this->db->query($sql)->list_array('uid');
	}
    /**
    *根据分销比例did,获取用户设置,单个
    */
    public function getOnePercent($did){
        if(empty($did)){
            return FALSE;
        }
        $sql = 'select uid,crid,percent,dateline from ebh_usershares';
        $wherearr[] = 'did='.intval($did);
        $wherearr[] = 'status=0';
        $sql.= ' where '.implode(' AND ',$wherearr);
        return $this->db->query($sql)->row_array();
    }
    /**
     *获取网校用户分销比列表
     */
    public function getPercentList($param){
        if(empty($param['crid'])){
            return FALSE;
        }
        $sql = 'select sh.did,sh.uid,sh.crid,sh.percent,sh.dateline,u.username,u.realname,u.sex,u.face,u.groupid from ebh_usershares sh join ebh_users u on sh.uid = u.uid';
        $wherearr[] = 'crid='.intval($param['crid']);
        $wherearr[] = 'sh.status=0';
        $sql.= ' where '.implode(' AND ',$wherearr);
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        } else {
            $sql.= ' ORDER BY sh.dateline desc';
        }
        if(!empty($param['limit'])) {
            $sql .= ' limit '.$param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1){
                $page = 1;
            }else{
                $page = $param['page'];
            }
            $pagesize = empty($param['pagesize']) ? 50 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }
    /**
     *获取网校用户分销比列表数量
     */
    public function getPercentCount($param){
        $return = 0;
        if(empty($param['crid'])){
            return $return;
        }
        $sql = 'select count(1) count from ebh_usershares sh  join ebh_users u on sh.uid = u.uid';
        $wherearr[] = 'sh.crid='.intval($param['crid']);
        $wherearr[] = 'sh.status=0';
        $sql.= ' where '.implode(' AND ',$wherearr);
        $ret = $this->db->query($sql)->row_array();
        if(!empty($ret['count'])){
            $return = $ret['count'];
        }
        return $return;
    }
    /**
     * 分销返现列表
     * @param array $param
     * @return mixed
     */
    public function shareList($param) {
        if(empty($param['crid'])){
            return array();
        }
        $sql = 'select po.detailid,po.orderid,po.shareuid,po.uid buyuid,po.providercrid,po.oname,po.fee,po.sharefee,p.paytime,c.crname, 
                ua.face shareface,ua.username shareusername,ua.realname sharerealname,ua.sex sharesex,ua.groupid sharegroupid, 
                ub.face buyface,ub.username buyusername,ub.realname buyrealname,ub.sex buysex,ub.groupid buygroupid 
                from ebh_pay_orderdetails po 
				left join ebh_pay_orders p on po.orderid=p.orderid 
				left join ebh_classrooms c on po.providercrid=c.crid 
				join ebh_users ua on po.shareuid=ua.uid 
				join ebh_users ub on po.uid=ub.uid';
        $wherearr[] = 'po.crid='.intval($param['crid']);
        if(!empty($param['starttime'])){
            $wherearr[] = 'p.paytime>='.intval($param['starttime']);
        }
        if(!empty($param['endtime'])){
            $wherearr[] = 'p.paytime<='.intval($param['endtime']);
        }
        if(isset($param['providercrid'])){
            $wherearr[] = 'po.providercrid='.intval($param['providercrid']);
        }
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = '(ua.username like \'%'.$q.'%\' or ua.realname like \'%'.$q.'%\' or ub.username like \'%'.$q.'%\' or ub.realname like \'%'.$q.'%\' or po.oname like \'%'.$q.'%\')';
        }
        $wherearr[] = 'po.invalid=0';
        $wherearr[] = 'po.dstatus=1';

        $sql.= ' where '.implode(' AND ',$wherearr);
        if(!empty($param['order'])){
            $param['order'] = $this->db->escape_str($param['order']);
            $sql.= ' ORDER BY '.$param['order'];
        } else {
            $sql.= ' ORDER BY p.paytime desc';
        }
        if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 50 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }
    /**
     * 分销返现列表数量
     * @param array $param
     * @return mixed
     */
    public function shareCount($param) {
        $return = 0;
        if(empty($param['crid'])){
            return $return;
        }
        $sql = 'select count(1) count 
                from ebh_pay_orderdetails po 
				left join ebh_pay_orders p on po.orderid=p.orderid 
				left join ebh_classrooms c on po.providercrid=c.crid 
				join ebh_users ua on po.shareuid=ua.uid 
				join ebh_users ub on po.uid=ub.uid';
        $wherearr[] = 'po.crid='.intval($param['crid']);
        if(!empty($param['starttime'])){
            $wherearr[] = 'p.paytime>='.intval($param['starttime']);
        }
        if(!empty($param['endtime'])){
            $wherearr[] = 'p.paytime<='.intval($param['endtime']);
        }
        if(isset($param['providercrid'])){
            $wherearr[] = 'po.providercrid='.intval($param['providercrid']);
        }
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = '(ua.username like \'%'.$q.'%\' or ua.realname like \'%'.$q.'%\' or ub.username like \'%'.$q.'%\' or ub.realname like \'%'.$q.'%\' or po.oname like \'%'.$q.'%\')';
        }
        $wherearr[] = 'po.invalid=0';
        $wherearr[] = 'po.dstatus=1';

        $sql.= ' where '.implode(' AND ',$wherearr);
        $ret = $this->db->query($sql)->row_array();
        if(!empty($ret['count'])){
            $return = $ret['count'];
        }
        return $return;
    }

    /**
     *获取分销返现来源网校列表
     */
    public function sourceLists($param){
        if(empty($param['crid'])){
            return array();
        }
        $sql = 'select DISTINCT po.providercrid,c.crname from ebh_pay_orderdetails po';
        $sql .= ' left join ebh_classrooms c on (case when po.providercrid=0 then po.crid else po.providercrid end)=c.crid where po.crid='.intval($param['crid']);
        return $this->db->query($sql)->list_array();
    }
}
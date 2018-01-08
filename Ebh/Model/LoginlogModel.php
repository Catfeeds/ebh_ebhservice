<?php

/**
 * 登录日志
 * User: ckx
 */
class LoginlogModel {
	/*
	修改,日志少量标记字段修改,其他字段理论上不应修改
	*/
	public function update($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$wherearr['crid'] = $param['crid'];
		if(!empty($param['uid'])){
			$wherearr['uid'] = $param['uid'];
		}
		if(!empty($param['logid'])){
			$wherearr['logid'] = $param['logid'];
		}
		if(isset($param['intention'])){
			$setarr['intention'] = $param['intention'];
		}
		return Ebh()->db->update('ebh_loginlogs',$setarr,$wherearr);
	}
    /**
     * 日志列表
     * @param array $param
     * @return mixed
     */
    public function getLogList($param) {
        if(empty($param['crid'])){
			return array();
		}
		$sql = 'select l.uid,crid,l.dateline,ip,system,browser,screen,u.groupid,u.face,u.sex,u.username,u.realname,l.citycode,u.mobile,l.intention from ebh_loginlogs l
				join ebh_users u on l.uid=u.uid';
		$wherearr[] = 'l.crid='.$param['crid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'l.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'l.dateline<='.$param['endtime'];
		}
		if(!empty($param['groupid'])){
			$wherearr[] = 'groupid = '.$param['groupid'];
		}
		if(!empty($param['citycode'])){
			$strlen = strlen($param['citycode']);
			$wherearr[] = 'left(l.citycode,'.$strlen.') = '.$param['citycode'];
		}
		if(!empty($param['system'])){
			$wherearr[] = 'system = \''.$param['system'].'\'';
		}
		if(!empty($param['browser'])){
			$wherearr[] = 'browser = \''.$param['browser'].'\'';
		}
		if(!empty($param['screen'])){
			$wherearr[] = 'screen = \''.$param['screen'].'\'';
		}
		if(!empty($param['uids'])){
			$wherearr[] = 'l.uid in ('.$param['uids'].')';
		}
		if(!empty($param['q'])){
			$q = Ebh()->db->escape_str($param['q']);
			$wherearr[] = '(username like \'%'.$q.'%\' or realname like \'%'.$q.'%\')';
		}
		
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['order'])){
			$sql.= ' ORDER BY '.$param['order'];
		} else {
			$sql.= ' ORDER BY `l`.`dateline` desc';
		}
		if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        } else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return Ebh()->db->query($sql)->list_array();
    }
	
	/*
	日志数量
	*/
	public function getLogCount($param){
		$count = 0;
		if(empty($param['crid'])){
			return $count;
		}
		$sql = 'select count(*) count from ebh_loginlogs l
				join ebh_users u on l.uid=u.uid';
		$wherearr[] = 'crid='.$param['crid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'l.dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'l.dateline<='.$param['endtime'];
		}
		if(!empty($param['groupid'])){
			$wherearr[] = 'groupid = '.$param['groupid'];
		}
		if(!empty($param['citycode'])){
			$strlen = strlen($param['citycode']);
			$wherearr[] = 'left(l.citycode,'.$strlen.') = '.$param['citycode'];
		}
		if(!empty($param['system'])){
			$wherearr[] = 'system = \''.$param['system'].'\'';
		}
		if(!empty($param['browser'])){
			$wherearr[] = 'browser = \''.$param['browser'].'\'';
		}
		if(!empty($param['screen'])){
			$wherearr[] = 'screen = \''.$param['screen'].'\'';
		}
		if(!empty($param['uids'])){
			$wherearr[] = 'l.uid in ('.$param['uids'].')';
		}
		if(!empty($param['q'])){
			$q = Ebh()->db->escape_str($param['q']);
			$wherearr[] = '(username like \'%'.$q.'%\' or realname like \'%'.$q.'%\')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
        $res = Ebh()->db->query($sql)->row_array();
		if(!empty($res)){
			$count = $res['count'];
		}
		return $count;
	}
	
	/*
	地区列表
	*/
	public function getRegionList($param){
		$sql = 'select c.citycode,c.cityname,c.areacode,cp.cityname as pcityname 
				from ebh_cities c 
				left join ebh_cities cp on c.parent_areacode=cp.areacode';
		if(isset($param['pcode']) && $param['pcode'] != -1){
			$wherearr[]= 'c.parent_areacode='.$param['pcode'];
		}
		if(!empty($param['codes'])){
			$wherearr[]= 'c.citycode in('.$param['codes'].')';
		}
		if(!empty($wherearr)){
			$sql.= ' where '.implode(' AND ',$wherearr);
		}
		return Ebh()->db->query($sql)->list_array('citycode');
	}
    
	/*
	设备列表
	*/
	public function getClientList($crid){
		if(empty($crid)){
			return array();
		}
		$sql = 'select system,browser from ebh_loginlogs';
		$sql.= ' where crid='.$crid;
		$sql.= ' group by system,browser';
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	分辨率列表
	*/
	public function getScreenList($crid){
		if(empty($crid)){
			return array();
		}
		$sql = 'select distinct(screen) from ebh_loginlogs';
		$sql.= ' where crid='.$crid;
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	地域分布列表
	*/
	public function getDistributeList($param){
		if(empty($param['crid'])){
			return array();
		}
		$sql = 'select count(*) count,count(distinct(ip)) ipcount,citycode,parentcode from ebh_loginlogs';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'citycode<>0';
		if(!empty($param['citycode'])){
			$wherearr[] = 'parentcode='.$param['citycode'];
		}
		if(!empty($param['starttime'])){
			$wherearr[] = 'dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'dateline<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by '.(empty($param['citycode']) && empty($param['allcities'])?'parentcode':'citycode');
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	签到列表
	*/
	public function getSignListByCity($param){
		if(empty($param['crid'])){
			return array();
		}
		$sql = 'select count(*) count,citycode from ebh_signlogs';
		$wherearr[] = 'crid='.$param['crid'];
		// $wherearr[] = 'ruleid=22';
		if(!empty($param['starttime'])){
			$wherearr[] = 'dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'dateline<='.$param['endtime'];
		}
		if(!empty($param['codes'])){
			$wherearr[] = 'citycode in('.$param['codes'].')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['codes'])){
			$sql.= ' group by citycode';
		}
		return Ebh()->db->query($sql)->list_array('citycode');
	}
	
	/**
     * 登录统计分析数据
     * @param $crid
     * @param $params
     * @param bool $setKey
     * @return mixed
     */
	public function getAnalyzeList($crid, $params, $setKey = false, $baseTime = 0) {
	    $whereArr = array('`crid`='.intval($crid));
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr[] = '`dateline`>='.$starttime;
        if (isset($params['endtime'])) {
            $whereArr[] = '`dateline`<='.intval($params['endtime']);
        }
        if ($starttime >= $today || isset($params['endtime']) && $params['endtime'] < $starttime + 86400) {
            $format = '\'%Y-%m-%d %H:00:00\'';
        } else {
            $format = '\'%Y-%m-%d\'';
        }
	    $sql = 'SELECT FROM_UNIXTIME(`dateline`,'.$format.') AS `date`,COUNT(DISTINCT `uid`) AS `users`,
	        COUNT(DISTINCT `ip`) AS `ips` FROM `ebh_loginlogs` WHERE '.implode(' AND ', $whereArr) .' GROUP BY `date`';
        return Ebh()->db->query($sql)->list_array($setKey ? 'date' : '');
    }

	/**
     * 时间段内登录分析数据
     * @param $crid
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getDayAnalyze($crid, $startTime, $endTime = 0) {
	    $whereArr = array(
	        '`crid`='.$crid,
            '`dateline`>='.intval($startTime),
            '`dateline`<'.intval($endTime)
        );
        $sql = 'SELECT COUNT(DISTINCT `uid`) AS `users`,
	        COUNT(DISTINCT `ip`) AS `ips` FROM `ebh_loginlogs` WHERE '.implode(' AND ', $whereArr);
        return Ebh()->db->query($sql)->row_array();
    }


    /**
     * 客户端分辨率统计
     * @param $crid
     * @param $params
     * @param bool $setKey
     * @param int $baseTime
     * @return mixed
     */
    public function screen($crid, $params, $setKey = false, $baseTime = 0) {
        $whereArr = array('`crid`='.intval($crid));
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr[] = '`dateline`>='.$starttime;
        if (isset($params['endtime'])) {
            $whereArr[] = '`dateline`<='.intval($params['endtime']);
        }
        $sql = 'SELECT `screen`,COUNT(DISTINCT `uid`) AS `count`
              FROM `ebh_loginlogs` WHERE '.implode(' AND ', $whereArr).' GROUP BY `screen`';
        return Ebh()->db->query($sql)->list_array($setKey ? 'screen' : '');
    }

    /**
     * 浏览器统计
     * @param $crid
     * @param $params
     * @param int $baseTime
     * @return mixed
     */
    public function browser($crid, $params, $baseTime = 0, $setKey = false) {
        $whereArr = array('`crid`='.intval($crid));
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr[] = '`dateline`>='.$starttime;
        if (isset($params['endtime'])) {
            $whereArr[] = '`dateline`<='.intval($params['endtime']);
        }
        $sql = 'SELECT `ismobile`,`browser`,IF(`browser`=\'IE\',CONCAT(`browser`,`broversion`),`browser`) AS `browser2`,`broversion`,COUNT(DISTINCT `uid`) AS `count`
              FROM `ebh_loginlogs` WHERE '.implode(' AND ', $whereArr).' GROUP BY `ismobile`,`browser2`';
        return Ebh()->db->query($sql)->list_array($setKey ? 'browser2' : '');
    }

    /**
     * 操作系统统计
     * @param $crid
     * @param $params
     * @param int $baseTime
     * @return mixed
     */
    public function os($crid, $params, $baseTime = 0) {
        $whereArr = array('`crid`='.intval($crid));
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr[] = '`dateline`>='.$starttime;
        if (isset($params['endtime'])) {
            $whereArr[] = '`dateline`<='.intval($params['endtime']);
        }
        $sql = 'SELECT `ismobile`,`system`,COUNT(DISTINCT `uid`) AS `count` FROM `ebh_loginlogs` WHERE '.
            implode(' AND ', $whereArr).' GROUP BY `ismobile`,`system`';
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 网络提供商统计
     * @param $crid
     * @param $params
     * @param int $baseTime
     * @return mixed
     */
    public function ips($crid, $params, $baseTime = 0) {
        $whereArr = array('`a`.`crid`='.intval($crid));
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr[] = '`a`.`dateline`>='.$starttime;
        if (isset($params['endtime'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($params['endtime']);
        }
        /*$sql = 'SELECT COUNT(DISTINCT `a`.`uid`) AS `count`,IFNULL(`b`.`isp`,0) AS `isp` FROM `ebh_loginlogs` `a`
            LEFT JOIN `ebh_isps` `b` ON (INET_ATON(`a`.`ip`) & `b`.`masklong`)=(`b`.`startiplong` & `b`.`masklong`) WHERE '.
            implode(' AND ', $whereArr).' GROUP BY `isp` ORDER BY `count` DESC,`isp` DESC';*/

        $sql = 'SELECT COUNT(DISTINCT `a`.`uid`) AS `count`,IFNULL(`b`.`isp`,0) AS `isp` FROM `ebh_loginlogs` `a`
            LEFT JOIN `ebh_isps` `b` ON `a`.`isp`=`b`.`id` WHERE '.implode(' AND ', $whereArr).' GROUP BY `b`.`isp` ORDER BY `count` DESC';
        return Ebh()->db->query($sql)->list_array();
    }
	
	/*
	 *学生注册信息
	*/
	public function firstLoginList($param){
		if(empty($param['crid']) || empty($param['uids'])){
			return array();
		}
		$sql = 'select l.uid,l.ip,c.cityname,cp.cityname as pcityname from ebh_loginlogs l 
				left join ebh_cities c on c.citycode=l.citycode 
				left join ebh_cities cp on cp.citycode=l.parentcode';
		$wherearr[] = 'l.uid in('.$param['uids'].')';
		$wherearr[] = 'l.logtype<>0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by l.uid';
		return Ebh()->db->query($sql)->list_array('uid');
		
	}
	
	/*
	 *学生末次登录时间
	*/
	public function lastLoginList($param){
		if(empty($param['crid']) || empty($param['uids'])){
			return array();
		}
		$sql = 'select uid,max(dateline) lastlogin from ebh_loginlogs l';
		$wherearr[] = 'l.uid in('.$param['uids'].')';
		$wherearr[] = 'l.crid='.$param['crid'];
		// $wherearr[] = 'l.logtype=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by l.uid';
		return Ebh()->db->query($sql)->list_array('uid');
		
	}
	
	/*
	 * 一个学生末次登录信息
	*/
	public function lastLogin($param){
		if(empty($param['crid']) || empty($param['uid'])){
			return array();
		}
		$sql = 'select uid,dateline,ip from ebh_loginlogs';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'uid='.$param['uid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' order by logid desc';
		$sql.= ' limit 1';
		// log_message($sql);
		return Ebh()->db->query($sql)->row_array();
	}

    /*
    根据区域名称查询信息
    */
    public function getCityByName($cityname){
        if(empty($cityname)){
            return FALSE;
        }
        $sql = 'select citycode from ebh_cities where cityname like \''.$cityname.'%\'';
        return Ebh()->db->query($sql)->row_array();
    }
	
	/*
	 * 添加日志
	*/
	public function add($param){
		if(empty($param['uid']) || empty($param['crid'])){
			return FALSE;
		}
		$setarr = array();
		if(!empty($param['ip']))
			$setarr['ip'] = $param['ip'];
		if(!empty($param['system']))
			$setarr['system'] = $param['system'];
		if(!empty($param['systemversion']))
			$setarr['systemversion'] = $param['systemversion'];
		if(!empty($param['browser']))
			$setarr['browser'] = $param['browser'];
		if(!empty($param['broversion']))
			$setarr['broversion'] = $param['broversion'];
		if(!empty($param['screen']))
			$setarr['screen'] = $param['screen'];
		if(!empty($param['citycode']))
			$setarr['citycode'] = $param['citycode'];
		if(!empty($param['parentcode']))
			$setarr['parentcode'] = $param['parentcode'];
		if(!empty($param['ismobile']))
			$setarr['ismobile'] = $param['ismobile'];
		if(!empty($param['isp']))
			$setarr['isp'] = $param['isp'];
		if(!empty($param['mac']))
			$setarr['mac'] = $param['mac'];
		if(!empty($param['logtype']))
			$setarr['logtype'] = $param['logtype'];
		if(!empty($param['dateline'])){
			$setarr['dateline'] = $param['dateline'];
		} else {
			$setarr['dateline'] = SYSTIME;
		}
		$setarr['crid'] = $param['crid'];
		$setarr['uid'] = $param['uid'];
		$logid = Ebh()->db->insert('ebh_loginlogs',$setarr);
		return $logid;
	}
}
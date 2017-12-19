<?php 
/*
网校公共模块
*/
class AppmoduleModel {
	private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }
    /**
     * @param $moduleType 模块类型：学生类型、教师类型
     * @param null $isSystem 模块分类：系统模块、普通模块
     * @return bool
     */
    public function getSimpleList($moduleType = false, $isSystem = NULL) {
        if ($moduleType !== false) {
            $where[] = $moduleType == 1 ? '`tors` IN(1,2)' : '`tors` IN(0,2)';
        }
        if ($isSystem !== NULL) {
            $where[] = '`system`='.intval($isSystem);
        }
        $where[] = '`showmode`=0';
        if ($moduleType == 1) {
            $sql = 'SELECT `moduleid`,`modulecode`,IF(`modulename_t`=\'\',`modulename`,`modulename_t`) AS `nickname`,`isfree`,`ismore`,`system`,`url_t`,`remark_t` AS `remark`,1 AS `available`,`moduleid` AS `displayorder`,`classname` 
                FROM `ebh_appmodules` 
                WHERE '.implode(' AND ', $where);
        } else {
            $sql = 'SELECT `moduleid`,`modulecode`,`modulename` AS `nickname`,`isfree`,`ismore`,`system`,`remark`,1 AS `available`,`moduleid` AS `displayorder`,`classname` 
                FROM `ebh_appmodules` 
                WHERE '.implode(' AND ', $where);
        }
        return Ebh()->db->query($sql)->list_array('moduleid');
    }
	/*
	所有可选用的模块列表
	*/
	public function getmodulelist($param=array()){
		$sql = 'select moduleid,modulename,modulecode,system,url,classname,isdynamic,isfree,logo,target,isstrict,showmode,tors,modulename_t,url_t,ismore from ebh_appmodules';
		if(isset($param['system']))
			$wherearr[] = 'system='.$param['system'];
		if(isset($param['tors']))
			$wherearr[] = 'tors in('.$param['tors'].')';
		if(isset($param['showmode']))
			$wherearr[] = 'showmode='.$param['showmode'];
		if(isset($param['ismore']))
			$wherearr[] = 'ismore='.$param['ismore'];
		if(!empty($param['q']))
			$wherearr[] = '(modulename like \'%'.Ebh()->db->escape_str($param['q']).'%\' or modulecode like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
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
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	所有模块数量
	*/
	public function getmodulecount($param){
		$sql = 'select count(*) count from ebh_appmodules';
		if(isset($param['system']))
			$wherearr[] = 'system='.$param['system'];
		if(!empty($param['q']))
			$wherearr[] = '(modulename like \'%'.$this->db->escape_str($param['q']).'%\' or modulecode like \'%'.$this->db->escape_str($param['q']).'%\'';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
	/*
	学校选用的模块
	*/
	public function getroommodulelist($param){
		if(empty($param['crid'])) {
		    return false;
        }
		$sql = 'select crid,rm.moduleid,nickname,available,displayorder,rm.ismore,modulecode from ebh_roommodules rm join ebh_appmodules am on rm.moduleid=am.moduleid';
		$wherearr[] = 'crid='.intval($param['crid']);
		$wherearr[] = 'rm.tors='.intval($param['tors']);
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['order'])) {
            $sql.= ' order by '.$param['order'];
        }
		return Ebh()->db->query($sql)->list_array();
	}
	
	
	/*
	学校后台编辑模块
	*/
	public function savemodule($param){
		if(empty($param['crid']) || empty($param['modulearr']))
			exit;
		$crid = $param['crid'];
		$tors = $param['tors'];
		//是否有数据
		$sql = 'select 1 from ebh_roommodules where crid='.$crid;
		$modulelist = $this->db->query($sql)->row_array();
		
		
		if(empty($modulelist)){//没有则新增
			$insertsql = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
			$sql = 'select moduleid,modulename,modulename_t,ismore from ebh_appmodules where system = 1';
			$modulelist = $this->db->query($sql)->list_array();
			foreach($modulelist as $module){
				$modulearr[$module['moduleid']] = $module;
			}
			foreach($param['modulearr'] as $k=>$module){

				if(!empty($modulearr[$module['moduleid']])){
					$moduleid = $module['moduleid'];
					$nickname = $module['nickname'];
					$available = $module['available'];
					$displayorder = $k;
					$ismore = $module['ismore'];
					$insertsql.= "($crid,$moduleid,'$nickname',$available,$displayorder,$tors,$ismore),";
					$insertsql.= "($crid,$moduleid,'',1,$displayorder,1-$tors,$ismore),";
				}else{
					$illegal = true;
					break;
				}
				unset($modulearr[$module['moduleid']]);
			}
			//系统的全加上
			if(!empty($modulearr)){
				foreach($modulearr as $l=>$module){
					$moduleid = $module['moduleid'];
					$nickname = '';
					$available = 1;
					$ismore = $module['ismore'];
					$displayorder = count($param['modulearr'])+$l;
					$insertsql.= "($crid,$moduleid,'$nickname',$available,$displayorder,0,$ismore),";
					$insertsql.= "($crid,$moduleid,'$nickname',$available,$displayorder,1,$ismore),";
				}
			}
			if(empty($illegal)){
				$insertsql = rtrim($insertsql,',');
				$this->db->query($insertsql);
			}
		}else{
			foreach($param['modulearr'] as $k=>$module){
				$nickname = $module['nickname'];
				$available = $module['available'];
				$displayorder = $k;
				$moduleid = $module['moduleid'];
				$ismore = $module['ismore'];
				$sql = "update ebh_roommodules set nickname='$nickname',available=$available,displayorder=$displayorder,ismore=$ismore where moduleid=$moduleid and crid=$crid and tors=$tors;";
				$this->db->query($sql);
			}
		}
	}
	
	/*
	学生后台显示的模块
	*/
	public function getstudentmodule($param){
		if(empty($param['crid']))
			exit;
		$sql = 'select rm.moduleid,rm.nickname,rm.available,rm.displayorder,am.modulename,am.url,am.isdynamic,am.classname,am.target,am.isstrict,modulename_t,url_t,rm.ismore,modulecode
			from ebh_roommodules rm join ebh_appmodules am on rm.moduleid=am.moduleid';
		$wherearr[] = 'crid='.$param['crid'];
		if(isset($param['system']))
			$wherearr[] = 'system='.$param['system'];
		if(isset($param['isfree']))
			$wherearr[] = 'isfree='.$param['isfree'];
		if(isset($param['available']))
			$wherearr[] = 'available='.$param['available'];
		if(!empty($param['modulecode']))
			$wherearr[] = "modulecode='".$this->db->escape_str($param['modulecode'])."'";
		if(isset($param['tors']))
			$wherearr[] = 'rm.tors in('.$param['tors'].') and am.tors in ('.$param['tors'].')';
		if(isset($param['showmode']))
			$wherearr[] = 'showmode='.$param['showmode'];
		if(isset($param['ismore']))
			$wherearr[] = 'rm.ismore='.$param['ismore'];
		$sql .= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['order']))
			$sql .= ' order by '.$param['order'];
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
        //echo $sql;
		return $this->db->query($sql)->list_array();
	}
	
	/*
	旧数据myroomleft
	*/
	public function getroomlefts(){
		$sql = 'select crid, myroomleft from ebh_classrooms where myroomleft<>\'\'';
		return $this->db->query($sql)->list_array();
	}
	
	/*
	新增网校模块权限,旧数据迁移用
	*/
	public function addroommodule($param){
		$modulelist = $this->getmodulelist();
		foreach($modulelist as $module){
			$modulelist[$module['modulecode']] = $module;
		}
		// var_dump($param);
		$sql = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder) values ';
		foreach($param as $modulearr){
			foreach($modulearr['modulelist'] as $k=>$module){
				$valuestr = '('.$modulearr['crid'].',';
				// $tempmodule = $modulelist[$module['code']];
				$moduleid = $modulelist[$module['code']]['moduleid'];
				$nickname = $module['nickname'];
				$available = $module['available'];
				$displayorder = $k;
				$valuestr .= "$moduleid,'$nickname',$available,$displayorder)";
				$sql .= $valuestr.',';
			}
		}
		$sql = rtrim($sql,',');
		$this->db->query($sql);
	}
	
	/*
	配置过模块的网校
	*/
	public function getclassroomlist($param){
		$sql = 'select distinct(cr.crid),crname,domain from ebh_classrooms cr join ebh_roommodules rm on cr.crid=rm.crid';
		
		if(!empty($param['q']))
			$wherearr[] = '(cr.crname like \'%'.$this->db->escape_str($param['q']).'%\' or cr.domain like \'%'.$this->db->escape_str($param['q']).'%\')';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		
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
	配置过模块的网校数量
	*/
	public function getclassroomcount(){
		$sql = 'select count(distinct(cr.crid)) count from ebh_classrooms cr join ebh_roommodules rm on cr.crid=rm.crid';
		if(!empty($param['q']))
			$wherearr[] = '(cr.crname like \'%'.$this->db->escape_str($param['q']).'%\' or cr.domain like \'%'.$this->db->escape_str($param['q']).'%\')';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
	
	/*
	网校模块权限编辑
	*/
	public function roommoduleedit($param){
		
		$crid = $param['crid'];
		$sql = 'select moduleid,crid from ebh_roommodules where crid='.$crid.' and tors=0';
		$oldlist = $this->db->query($sql)->list_array();
		// var_dump($oldlist);
		// var_dump($param);
		
		//之前的权限和现在的权限比对
		foreach($oldlist as $j=>$oldmodule){
			foreach($param['modulelist'] as $k=>$module){
				if($oldmodule['moduleid'] == $module){
					unset($oldlist[$j]);
					unset($param['modulelist'][$k]);
				}
			}
		}
		$this->db->begin_trans();
		//不要的删掉
		if(!empty($oldlist)){
			$delmoduleids = '';
			foreach($oldlist as $module){
				$delmoduleids .= $module['moduleid'].',';
			}
			$delmoduleids = rtrim($delmoduleids,',');
			$delsql = 'delete from ebh_roommodules where crid='.$crid.' and moduleid in('.$delmoduleids.')';
			$this->db->query($delsql);
		}
		//新增的加上
		if(!empty($param['modulelist'])){
			$insertsql = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
			$insertsql_t = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
			$valuestr = '';
			$valuestr_t = '';
			
			$infosql = 'select moduleid,ismore from ebh_appmodules where moduleid in ('.implode(',',$param['modulelist']).')';
			$modulelist = $this->db->query($infosql)->list_array();
			
			foreach($modulelist as $k=>$module){
				// var_dump($moduleid);
				$moduleid = $module['moduleid'];
				$ismore = $module['ismore'];
				if(!empty($moduleid)){
					$displayorder = $k+10;
					$valuestr.= "($crid,$moduleid,'',1,$displayorder,0,$ismore),";
					$valuestr_t.= "($crid,$moduleid,'',1,$displayorder,1,$ismore),";
				}
			}
			if(!empty($valuestr)){
				$insertsql .= rtrim($valuestr,',');
				$insertsql_t .= rtrim($valuestr_t,',');
				
				$this->db->query($insertsql);
				$this->db->query($insertsql_t);
			}
		}
		
		if($this->db->trans_status()===FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
		return TRUE;
	}
	
	/*
	添加模块
	*/
	public function addappmodule($param){
		if(isset($param['modulename']))
			$setarr['modulename'] = $param['modulename'];
		if(isset($param['modulecode']))
			$setarr['modulecode'] = $param['modulecode'];
		if(isset($param['url']))
			$setarr['url'] = $param['url'];
		if(isset($param['system']))
			$setarr['system'] = $param['system'];
		if(isset($param['classname']))
			$setarr['classname'] = $param['classname'];
		if(isset($param['target']))
			$setarr['target'] = $param['target'];
		if(isset($param['isstrict']))
			$setarr['isstrict'] = $param['isstrict'];
		if(isset($param['tors']))
			$setarr['tors'] = $param['tors'];
		if(isset($param['showmode']))
			$setarr['showmode'] = $param['showmode'];
		if(isset($param['modulename_t']))
			$setarr['modulename_t'] = $param['modulename_t'];
		if(isset($param['url_t']))
			$setarr['url_t'] = $param['url_t'];
		if(isset($param['ismore']))
			$setarr['ismore'] = $param['ismore'];
		$this->db->insert('ebh_appmodules',$setarr);
	}
	
	/*
	编辑模块
	*/
	public function editappmodule($param){
		if(empty($param['moduleid']))
			exit;
		$wherearr['moduleid'] = $param['moduleid'];
		if(isset($param['modulename']))
			$setarr['modulename'] = $param['modulename'];
		if(isset($param['modulecode']))
			$setarr['modulecode'] = $param['modulecode'];
		if(isset($param['url']))
			$setarr['url'] = $param['url'];
		if(isset($param['system']))
			$setarr['system'] = $param['system'];
		if(isset($param['classname']))
			$setarr['classname'] = $param['classname'];
		if(isset($param['target']))
			$setarr['target'] = $param['target'];
		if(isset($param['isstrict']))
			$setarr['isstrict'] = $param['isstrict'];
		if(isset($param['tors']))
			$setarr['tors'] = $param['tors'];
		if(isset($param['showmode']))
			$setarr['showmode'] = $param['showmode'];
		if(isset($param['modulename_t']))
			$setarr['modulename_t'] = $param['modulename_t'];
		if(isset($param['url_t']))
			$setarr['url_t'] = $param['url_t'];
		$this->db->update('ebh_appmodules',$setarr,$wherearr);
	}
	
	/*
	按moduleid获取模块信息
	*/
	public function getmoduleinfo($moduleid){
		$sql = 'select moduleid,modulename,modulecode,system,url,classname,isdynamic,isfree,logo,target,isstrict,tors,showmode,modulename_t,url_t from ebh_appmodules where moduleid='.$moduleid;
		return $this->db->query($sql)->row_array();
	}
	
	/*
	删除应用模块
	*/
	public function del($moduleid){
		if(empty($moduleid) || !is_numeric($moduleid))
			exit;
		$wherearr['moduleid'] = $moduleid;
		return $this->db->delete('ebh_appmodules',$wherearr);
	}
	/*
	给系统应用模块 添加各网校的对应关系
	*/
	public function initsystemmodule($moduleid){
		if(empty($moduleid) || !is_numeric($moduleid))
			exit;
		$msql = 'select moduleid,ismore from ebh_appmodules where moduleid='.$moduleid;
		$moduleinfo = $this->db->query($msql)->row_array();
		if(empty($moduleinfo))
			return false;
		$ismore = $moduleinfo['ismore'];
		$sql = 'select distinct(crid) from ebh_roommodules where crid not in (select distinct(crid) from ebh_roommodules where moduleid ='.$moduleid.')';
		$crids = $this->db->query($sql)->list_array();
		if(!empty($crids)){
			$insertsql = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
			$displayorder = SYSTIME;
			foreach($crids as $v){
				$crid = $v['crid'];
				$insertsql.= "($crid,$moduleid,'',1,$displayorder,0,$ismore),";
				$insertsql.= "($crid,$moduleid,'',1,$displayorder,1,$ismore),";
			}
			$insertsql = rtrim($insertsql,',');
			
			$this->db->query($insertsql);
		}
		return true;
		
	}
	
	/*
	根据modulecode获取modulename,nickname等
	*/
	public function getmodulenamebycode($param){
		$sql = 'select modulecode,modulename,modulename_t,nickname
				from ebh_appmodules am 
				join ebh_roommodules rm on am.moduleid=rm.moduleid';
		$wherearr[] = 'modulecode=\''.$param['modulecode'].'\'';
		$wherearr[] = 'rm.tors='.$param['tors'];
		$wherearr[] = 'rm.crid='.$param['crid'];
		$sql .= ' where '.implode(' AND ',$wherearr);
		return $this->db->query($sql)->row_array();
	}
	/*
	根据modulecode获取classname等
	*/
    public function getClassnameByCode($param){
        $sql = 'select classname from ebh_appmodules';
        if(!empty($param['modulecode'])){
            $sql.=' where modulecode = \''.$param['modulecode'].'\'';
        }
        return $this->db->query($sql)->row_array();
    }
    /*
    根据moudleid以及搜索内容获取classroom信息
     */
    public function getclassroomlistbymid($param){
    	$sql = 'select distinct(cr.crid),cr.crname,cr.domain from ebh_classrooms cr join ebh_roommodules rm on (cr.crid=rm.crid)';
		
		if(!empty($param['q']))
			$wherearr[] = '(cr.crname like \'%'.$this->db->escape_str($param['q']).'%\' or cr.domain like \'%'.$this->db->escape_str($param['q']).'%\')';
		if(!empty($param['moduleid'])){
			$wherearr[] = 'rm.moduleid = '.$param['moduleid'];
		}
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		
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
    根据moudleid和搜索内容获取符合条件教室总数
     */
    public function getclassroomcountbymid($param){
    	$sql = 'select count(distinct(cr.crid)) count from ebh_classrooms cr join ebh_roommodules rm on (cr.crid=rm.crid)';
		if(!empty($param['q']))
			$wherearr[] = '(cr.crname like \'%'.$this->db->escape_str($param['q']).'%\' or cr.domain like \'%'.$this->db->escape_str($param['q']).'%\')';
		if(!empty($param['moduleid'])){
			$wherearr[] = 'rm.moduleid = '.$param['moduleid'];
		}
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
    }
    /**
     * 根据modulecode获取模块的相关信息
     */
    public function getModuleInfoByCode($modulecode){
    	if(empty($modulecode)){
    		return false;
    	}
    	$sql = 'select moduleid,modulename,url,url_t,isdynamic,classname,target,isstrict,modulename_t from `ebh_appmodules` where modulecode ='.$this->db->escape($modulecode);
    	return $this->db->query($sql)->row_array();
    }
	
	/*
	将更多模块移到第7个,旧数据迁移用
	*/
	public function replacemore($moduleid){
		$sql = 'select distinct(crid) crid from ebh_roommodules';
		$crlist = $this->db->query($sql)->list_array();
		$this->db->begin_trans();
		foreach($crlist as $cr){
			$crid = $cr['crid'];
			$sql = 'select displayorder	from ebh_roommodules where crid='.$crid.' and tors = 0 order by displayorder limit 5,1';
			$ordersix = $this->db->query($sql)->row_array();
			// echo $sql;
			$displayorder = $ordersix['displayorder'];
			$upsql = "update ebh_roommodules set displayorder=displayorder+1 where crid=$crid and displayorder>$displayorder";
			$this->db->query($upsql);
			$upsql = "update ebh_roommodules set displayorder=$displayorder+1 where crid=$crid and moduleid=$moduleid";
			$this->db->query($upsql);
		}
		if ($this->db->trans_status() === FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
		return TRUE;
	}

	/*
	检测某网校是否有模块的权限
	*/
	public function checkRoomMoudle($crid,$url_t){
		if (empty($crid) OR empty($url_t)) {
			return FALSE;
		}
		$sql = 'select moduleid from ebh_roommodules rm left join ebh_appmodules am using (moduleid) where rm.crid ='.$crid.' and am.url_t=\''.$url_t.'\'';
		return $this->db->query($sql)->list_array();
	}

	/*
	创建网校，默认模块
	*/
	public function defaultmodule($param){
		$tlist = $param['tlist'];
		$slist = $param['slist'];
		$crid = $param['crid'];
		$insertsql = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
		$insertsql_t = 'insert into ebh_roommodules (crid,moduleid,nickname,available,displayorder,tors,ismore) values ';
		$infosql = 'select moduleid,ismore from ebh_appmodules where moduleid in ('.implode(',',$param['modulelist']).')';
		$modulelist = $this->db->query($infosql)->list_array();
		foreach($modulelist as $k=>$module){
				// var_dump($moduleid);
			$moduleid = $module['moduleid'];
			$ismore = $module['ismore'];
			if(!empty($moduleid)){
				$sdisplayorder = array_search($moduleid,$slist);
				$tdisplayorder = array_search($moduleid,$tlist);
				if($sdisplayorder === FALSE || $sdisplayorder === NULL)
					$sdisplayorder = 99;
				if($tdisplayorder === FALSE || $tdisplayorder === NULL)
					$tdisplayorder = 99;
				$valuestr.= "($crid,$moduleid,'',1,$sdisplayorder,0,$ismore),";
				$valuestr_t.= "($crid,$moduleid,'',1,$tdisplayorder,1,$ismore),";
			}
		}
		$insertsql .= rtrim($valuestr,',');
		$insertsql_t .= rtrim($valuestr_t,',');
		
		$this->db->begin_trans();
		
		$this->db->query($insertsql);
		$this->db->query($insertsql_t);
		
		if($this->db->trans_status()===FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
		return TRUE;
		
	}
}
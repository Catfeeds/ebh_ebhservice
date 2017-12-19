<?php 
/*
菜单
*/
class MenuModel{
	private $db;
	public function __construct() {
		$this->db = Ebh()->db;
	}
    /**
     * 网校的菜单
     * @param array $param 查询参数
     * @param bool $setKey 是否以moduleid为键
     * @return mixed
     */
	public function getMenuList($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select mid,tmid,crid,nickname,icon,mdisplayorder,status,dateline from ebh_roommenus';
		$sql.= ' where crid='.$param['crid'].' and roomtype='.$param['roomtype'];
		$roommenu = $this->db->query($sql)->list_array('mid');
		$midarr = array_keys($roommenu);
		$mids = implode(',',$midarr);
		if(!empty($param['nomalteacher'])){//普通老师菜单权限
			if(empty($param['uid'])){
				return 	FALSE;
			}
			$sql = 'select permissions from ebh_teacher_roles r join ebh_roomteachers rt on r.rid=rt.role
					where rt.tid='.$param['uid'].' and rt.crid='.$param['crid'].' and r.crid='.$param['crid']. ' and rt.status=1';
			$teachermenu = $this->db->query($sql)->row_array();
			if(empty($teachermenu)){
				return FALSE;
			}
			$midarr_t = json_decode($teachermenu['permissions']);
			if(empty($midarr_t)){
				return FALSE;
			}
			$mids_t = implode(',',$midarr_t);
			
			//查找上级
			$sql = 'select tmid from ebh_roommenus where crid='.$param['crid'].' and mid in ('.$mids_t.') and tmid<>0';
			$tmids = $this->db->query($sql)->list_array();
			$tmids = array_column($tmids,'tmid');
			$midarr_t = array_merge($midarr_t,$tmids);
			$mids_t = implode(',',$midarr_t);
		}

		$sql = 'select mtitle,mid,tmid,url,status,mdisplayorder,modulecode,icon,roomtype,crid from ebh_menus m';
		if(!empty($mids)){
			$wherearr[] = 'm.mid in ('.$mids.')';
		}
		if(!empty($mids_t)){
			$wherearr[] = 'm.mid in ('.$mids_t.')';
		}
		if(isset($param['roomtype'])){
			$wherearr[] = 'm.roomtype='.$param['roomtype'];
		}
		if(!empty($param['onlylevel1'])){
			$wherearr[] = 'm.tmid=0';
		}
		$wherearr[] = '(m.crid = 0 or m.crid = '.$param['crid'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$menulist = $this->db->query($sql)->list_array();
		if(empty($menulist)){
			return array();
		}
		$menuarr = array();
		foreach($menulist as $k=>&$menu){
			$mid = $menu['mid'];
			$menu['status'] = isset($roommenu[$mid]['status'])?$roommenu[$mid]['status']:$menu['status'];
			if(!empty($param['nohide']) && $menu['status'] ==0){//不显示隐藏的
				unset($menulist[$k]);
				continue;
			}
			$menu['tmid'] = isset($roommenu[$mid]['tmid'])?$roommenu[$mid]['tmid']:$menu['tmid'];
			$menu['mtitle'] = !empty($roommenu[$mid]['nickname'])?$roommenu[$mid]['nickname']:$menu['mtitle'];
			$menu['icon'] = !empty($roommenu[$mid]['icon'])?$roommenu[$mid]['icon']:$menu['icon'];
			$menu['dateline'] = !empty($roommenu[$mid]['dateline'])?$roommenu[$mid]['dateline']:0;
			$menu['mdisplayorder'] = isset($roommenu[$mid]['mdisplayorder'])?$roommenu[$mid]['mdisplayorder']:$menu['mdisplayorder'];
			$orderarr[] = $menu['mdisplayorder'];
			$tmidarr[] = $menu['tmid'];
			if(!empty($menu['modulecode'])){
				$modulecodearr[] = '\''.$menu['modulecode'].'\'';
			}
		}
		//判断是否有模块权限
		if(!empty($param['withmodule']) && !empty($modulecodearr)){
			$modulecodes = implode(',',$modulecodearr);
			$msql = 'select modulecode 
					from ebh_appmodules am 
					join ebh_roommodules rm on am.moduleid=rm.moduleid
					where rm.crid='.$param['crid'].' and am.modulecode in('.$modulecodes.') and rm.available=1';
			$codes = $this->db->query($msql)->list_array('modulecode');
			$codes = array_column($codes,'modulecode');
		}
		//排序，按一级二级分组
		array_multisort($tmidarr , SORT_ASC , $orderarr , SORT_DESC , $menulist);
		foreach($menulist as $k=>$menu2){
			if(!empty($param['withmodule']) && !empty($menu2['modulecode'])){//关联模块的菜单，如果本校没有勾选该模块，则剔除
				if(empty($codes) || !in_array($menu2['modulecode'],$codes))
					continue;
			}
			$mid = $menu2['mid'];
			if(empty($menu2['tmid'])){
				$menuarr[$mid] = $menu2;
				$menuarr[$mid]['children'] = array();
			} elseif(!empty($menuarr[$menu2['tmid']])) {
				$menuarr[$menu2['tmid']]['children'][] = $menu2;
			}
			if(empty($menu2['icon'])){
				$menulist[$k]['icon'] = 'http://static.ebanhui.com/ebh/tpl/aroomv3/icon/more.png';
			}
		}
		return array_values($menuarr);
	}
	
	/*
	所有模块数量-------暂时不用
	*/
	public function getMenuCount($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select mid from ebh_roommenus';
		$sql.= ' where crid='.$param['crid'];
		$roommenu = $this->db->query($sql)->list_array();
		$mids = array_column($roommenu,'mid');
		$mids = implode(',',$mids);
		
		$sql = 'select count(*) count from ebh_menus m';
		if(!empty($mids)){
			$wherearr[] = 'm.mid in ('.$mids.')';
		}
		if(isset($param['roomtype'])){
			$wherearr[] = 'm.roomtype='.$param['roomtype'];
		}
		$wherearr[] = '(m.crid = 0 or m.crid = '.$param['crid'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
	
	/*
	添加菜单
	*/
	public function addMenu($param){
		if(empty($param['mtitle']) || !isset($param['roomtype'])){
			return FALSE;
		}
		$insertarr['mtitle'] = $param['mtitle'];
		$insertarr['roomtype'] = $param['roomtype'];
		if(!empty($param['mdisplayorder']))
			$insertarr['mdisplayorder'] = $param['mdisplayorder'];
		if(!empty($param['url']))
			$insertarr['url'] = $param['url'];
		if(isset($param['status']))
			$insertarr['status'] = $param['status'];
		if(!empty($param['icon']))
			$insertarr['icon'] = $param['icon'];
		if(!empty($param['tmid']))
			$insertarr['tmid'] = $param['tmid'];
		$this->db->begin_trans();
		//初始化菜单,非系统菜单
		if(empty($param['issystem'])){
			$this->initMenu($param['crid'],$param['roomtype']);
			$insertarr['crid'] = $param['crid'];
		}
		$mid = $this->db->insert('ebh_menus',$insertarr);
		// unset($insertarr['roomtype']);
		unset($insertarr['mtitle']);
		unset($insertarr['url']);
		$insertarr['dateline'] = SYSTIME;
		$insertarr['nickname'] = $param['mtitle'];
		$insertarr['mid'] = $mid;
		if(empty($param['issystem'])){//非系统菜单,执行添加
			$this->db->insert('ebh_roommenus',$insertarr);
		} else {//系统菜单，为已设置过的添加
			$this->initMenu_sys($insertarr,$param['roomtype']);
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
	添加菜单
	*/
	public function editMenu($param){
		if(empty($param['mid']) || !isset($param['roomtype'])){
			return FALSE;
		}
		
		$wherearr['crid'] = $param['crid'];
		$wherearr['mid'] = $param['mid'];
		if(isset($param['status']))
			$setarr['status'] = $param['status'];
		if(empty($param['statusonly'])){//只修改是否显示
			if(!empty($param['mtitle'])){
				$setarr['mtitle'] = $param['mtitle'];
				$setarr['nickname'] = $param['mtitle'];
			}
			if(isset($param['mdisplayorder']))
				$setarr['mdisplayorder'] = $param['mdisplayorder'];
			if(isset($param['icon']))
				$setarr['icon'] = $param['icon'];
			if(isset($param['tmid']))
				$setarr['tmid'] = $param['tmid'];
			if(isset($param['url']))
				$setarr['url'] = $param['url'];
		}
		$this->db->begin_trans();
		//初始化菜单,非系统菜单
		if(empty($param['issystem'])){
			$this->initMenu($param['crid'],$param['roomtype']);
			
			unset($setarr['nickname']);
			$this->db->update('ebh_menus',$setarr,$wherearr);
			unset($setarr['mtitle']);
			unset($setarr['url']);
			if(empty($param['statusonly'])){
				$setarr['nickname'] = $param['mtitle'];
			}
			$setarr['dateline'] = SYSTIME;
			$this->db->update('ebh_roommenus',$setarr,$wherearr);
			
		} else {
			unset($setarr['nickname']);
			$this->db->update('ebh_menus',$setarr,$wherearr);
			$mtsetarr['status'] = $param['status'];
			if($param['mtitle'] != $param['oldmtitle']){//如果网校的菜单和基础菜单名称一样,更新名称
				$mtsetarr['nickname'] = $param['mtitle'];
				$mtsetarr['dateline'] = SYSTIME;
				$mtwherearr['nickname'] = $param['oldmtitle'];
			}
			$mtwherearr['mid'] = $param['mid'];
			$this->db->update('ebh_roommenus',$mtsetarr,$mtwherearr);
		}
		
		if($this->db->trans_status()===FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
		return TRUE;
	}
	
	public function delMenu($param){
		if(empty($param['crid']) || empty($param['mid']) || !isset($param['roomtype'])){
			return FALSE;
		}
		
		//初始化菜单
		$this->initMenu($param['crid'],$param['roomtype']);
		unset($param['roomtype']);
		$sql = 'select 1 from ebh_roommenus where tmid='.$param['mid'].' and crid='.$param['crid'];
		$children = $this->db->query($sql)->row_array();
		if(!empty($children)){
			return FALSE;
		}
		
		$this->db->begin_trans();
		$this->db->delete('ebh_roommenus',$param);
		$this->db->delete('ebh_menus',$param);
		if($this->db->trans_status()===FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
		return TRUE;
	}
	
	/*
	网校是否设置过菜单
	*/
	private function existsMenu($crid,$roomtype){
		$sql = 'select 1 from ebh_roommenus rm join ebh_menus m on rm.mid=m.mid
				where rm.crid='.$crid.' and m.roomtype='.$roomtype;
		$exists = $this->db->query($sql)->row_array();
		return !empty($exists);
	}
	/*
	没有设置过时，初始化菜单
	*/
	private function initMenu($crid,$roomtype){
		if(!$this->existsMenu($crid,$roomtype)){//从未设置过的(按教育,企业版区分)
			$initsql = 'insert into ebh_roommenus (mid,tmid,crid,nickname,icon,mdisplayorder,dateline,status,roomtype)
					select mid,tmid,'.$crid.',mtitle,icon,mdisplayorder,'.SYSTIME.',status,roomtype from ebh_menus
					where roomtype='.$roomtype;
			$this->db->query($initsql);
		}
	}
	/*
	有设置过时，初始化菜单(系统的)
	*/
	private function initMenu_sys($param,$roomtype){
		$sql = 'select distinct crid from ebh_roommenus where roomtype ='.$roomtype;
		$crids = $this->db->query($sql)->list_array();
		if(!empty($crids)){
			$crids = array_column($crids,'crid');
			$crids = implode(',',$crids);
			$sql = 'select crid from ebh_classrooms where crid in('.$crids.')';
			// if($roomtype==0){
				// $sql.= ' and (isschool <> 7 or property <> 3)';
			// } else {
				// $sql.= ' and isschool = 7 and property =3';
			// }
			$needcrids = $this->db->query($sql)->list_array();
		}
		if(!empty($needcrids)){
			$initsql = 'insert into ebh_roommenus (mid,tmid,crid,nickname,icon,mdisplayorder,dateline,status,roomtype) values ';
			$valuestr = '';
			$mid = $param['mid'];
			$tmid = empty($param['tmid'])?0:$param['tmid'];
			$nickname = $param['nickname'];
			$icon = empty($param['icon'])?'':$param['icon'];
			$mdisplayorder = $param['mdisplayorder'];
			$dateline = $param['dateline'];
			$status = $param['status'];
			foreach($needcrids as $cr){
				$crid = $cr['crid'];
				$valuestr .= "($mid,$tmid,$crid,'$nickname','$icon',$mdisplayorder,$dateline,$status,$roomtype),";
			}
			$initsql.= rtrim($valuestr,',');
			$this->db->query($initsql);
		}
	}
	public function menuDetail($param){
		if(!isset($param['crid']) || empty($param['mid']) || !isset($param['roomtype'])){
			return FALSE;
		}
		if(!$this->existsMenu($param['crid'],$param['roomtype'])){//从未设置过的(按教育,企业版区分)
			$sql = 'select mtitle,mid,tmid,url,status,mdisplayorder,modulecode,icon,crid from ebh_menus';
			$sql.= ' where mid='.$param['mid'];
			$detail = $this->db->query($sql)->row_array();
		} else {
			$sql = 'select rm.mid,rm.tmid,rm.nickname mtitle,rm.icon,rm.mdisplayorder,rm.status,m.url,m.crid 
					from ebh_roommenus rm join ebh_menus m on rm.mid=m.mid';
			$wherearr[] = 'rm.mid='.$param['mid'];
			$wherearr[] = 'rm.crid='.$param['crid'];
			$wherearr[] = 'm.roomtype='.$param['roomtype'];
			$sql.= ' where '.implode(' AND ',$wherearr);
			$detail = $this->db->query($sql)->row_array();
		}
		return $detail;
	}
	/**
	 * 获取父级菜单ID列表
	 * @param $mids 菜单ID
	*/
	public function getParentIds($mids) {
		if (is_array($mids)) {
			$mids = array_filter($mids, function($mid) {
				return intval($mid) > 0;
			});
		} else if (is_numeric($mids)) {
			$mids = array(intval($mids));
		}
		if (empty($mids)) {
			return array();
		}
		$sql = 'SELECT `tmid` FROM `ebh_menus` WHERE `mid` IN('.implode(',', $mids).')';
		$ret = Ebh()->db->query($sql)->list_field();
		if (empty($ret)) {
			return array();
		}
		return array_unique($ret);
	}
	
	public function getBaseMenuList($param){
		if(!isset($param['roomtype'])){
			return FALSE;
		}
		$sql = 'select mtitle,mid,tmid,url,status,mdisplayorder,modulecode,icon,roomtype,crid,0 as dateline from ebh_menus';
		$wherearr[] = 'roomtype='.$param['roomtype'];
		$wherearr[] = 'crid=0';
		if(!empty($param['onlylevel1'])){
			$wherearr[] = 'tmid=0';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$menulist = $this->db->query($sql)->list_array();
		foreach($menulist as $k=>$menu){
			$orderarr[] = $menu['mdisplayorder'];
			$tmidarr[] = $menu['tmid'];
		}
		//排序，按一级二级分组
		array_multisort($tmidarr , SORT_ASC , $orderarr , SORT_DESC , $menulist);
		foreach($menulist as $menu2){
			$mid = $menu2['mid'];
			if(empty($menu2['tmid'])){
				$menuarr[$mid] = $menu2;
			} elseif(!empty($menuarr[$menu2['tmid']])) {
				$menuarr[$menu2['tmid']]['children'][] = $menu2;
			}
		}
		return array_values($menuarr);
	}

    //根据搜索条件获取一二级菜单列表
    public function getListByQuery($param){
        $menulist = array();
        if(!isset($param['roomtype'])){
            return $menulist;
        }
        $sql = 'SELECT ma.mtitle AS pmtitle,mb.mtitle,mb.mid,mb.tmid,mb.url,mb.mdisplayorder FROM ebh_menus mb LEFT JOIN ebh_menus ma ON mb.tmid=ma.mid ';
        $wherearr[] = 'mb.roomtype='.intval($param['roomtype']);    //网校类型,0教育版,1企业版
        if(isset($param['crid'])){
            $wherearr[] = 'mb.crid='.intval($param['crid']);
        }
        if(isset($param['tmid'])){  //父级菜单id
            $wherearr[] = 'mb.tmid='.intval($param['tmid']);
        }else{
            $wherearr[] = 'mb.tmid<>0';
        }
        if(isset($param['q'])){
            $wherearr[] = 'mb.mtitle LIKE \'%'.$this->db->escape_str($param['q']).'%\'';
        }
        $sql.= ' WHERE '.implode(' AND ',$wherearr);
        $sql.= ' GROUP BY mb.mtitle';
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$this->db->escape_str($param['order']);
        }else{
            $sql.= ' ORDER BY mb.tmid ASC,mb.mdisplayorder DESC';
        }
        if(empty($param['notpage'])){
            if(!empty($param['limit'])) {
                $sql .= ' LIMIT '.$this->db->escape_str($param['limit']);
            } else {
                if (empty($param['page']) || $param['page'] < 1)
                    $page = 1;
                else{
                    $page = intval($param['page']);
                }
                $pagesize = empty($param['pagesize']) ? 10 : intval($param['pagesize']);
                $start = ($page - 1) * $pagesize;
                $sql .= ' LIMIT ' . $start . ',' . $pagesize;
            }
        }
        $res = $this->db->query($sql)->list_array();
        if($res !== false){
            $menulist = $res;
        }
        return $menulist;
    }
    //根据搜索条件获取一二级菜单数量
    public function getCountByQuery($param){
        $count = 0;
        if(!isset($param['roomtype'])){
            return $count;
        }
        $sql = 'SELECT count(1) AS count  FROM (select mb.mid FROM ebh_menus mb LEFT JOIN ebh_menus ma ON mb.tmid=ma.mid ';
        $wherearr[] = 'mb.roomtype='.intval($param['roomtype']);    //网校类型,0教育版,1企业版
        if(isset($param['crid'])){
            $wherearr[] = 'mb.crid='.intval($param['crid']);
        }
        if(isset($param['tmid'])){  //父级菜单id
            $wherearr[] = 'mb.tmid='.intval($param['tmid']);
        }else{
            $wherearr[] = 'mb.tmid<>0';
        }
        if(isset($param['q'])){
            $wherearr[] = 'mb.mtitle LIKE \'%'.$this->db->escape_str($param['q']).'%\'';
        }
        $sql.= ' WHERE '.implode(' AND ',$wherearr);
        $sql.= ' GROUP BY mb.mtitle) AS total';
        $res = $this->db->query($sql)->row_array();
        if($res !== false){
            $count = !empty($res['count']) ? $res['count'] : 0;
        }
        return $count;
    }
}

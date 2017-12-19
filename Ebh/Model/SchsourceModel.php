<?php
/**
 * 企业选课，网校来源信息关联表
 */
class SchsourceModel{
	/**
	 * 获取来源信息列表
	 * @param array $param
	 * @return boolean
	 */
	public function getSourceList($param) {
		$sql = 'select sourceid,s.crid,s.sourcecrid,s.name,s.dateline,sum(if(si.del=1 or isnull(si.del),0,1)) coursecount 
			from ebh_schsources s
			left join ebh_schsourceitems si on s.crid=si.crid and s.sourcecrid=si.sourcecrid';
		$wherearr = array();
		if(!empty($param['crid'])){
			$wherearr[] = 's.crid='.$param['crid'];
		}
		if(!empty($param['sourcecrid'])){
			$wherearr[] = 's.sourcecrid='.$param['sourcecrid'];
		}
		if(!empty($param['sourceid'])){
			$wherearr[] = 'sourceid='.$param['sourceid'];
		}
		if(!empty($wherearr)){
			$sql .= ' WHERE ' . implode(' AND ', $wherearr);
		}
		$sql .= ' group BY s.crid,s.sourcecrid ';
		$sql .= ' ORDER BY sourceid DESC ';
		if (!empty($param['limit'])){
			$sql .= ' LIMIT ' . $param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= 'limit ' . $start . ',' . $pagesize;
		}
		return Ebh()->db->query($sql)->list_array();
	}


	/**
	 * 获取课程列表
	 * @param array $param
	 * @return list
	 */
	public function getItemList($param) {
		if (empty($param['crid']) && empty($param['itemids']))
			return FALSE;
		$sql = 'select i.iname,i.itemid,i.folderid,p.pname,s.sname,i.pid,i.sid,i.crid 
				from ebh_pay_items i
				left join ebh_pay_sorts s on i.sid=s.sid
				join ebh_pay_packages p on p.pid=i.pid';
		$wherearr = array('i.`status`=0','p.`status`=1');
		if(!empty($param['pid'])){
			$wherearr[] = 'i.pid=' . $param['pid'];
			if(!empty($param['sid'])){
				$wherearr[] = 'i.sid=' . $param['sid'];
			}
		}
		if(!empty($param['q'])){
			$q = Ebh()->db->escape_str($param['q']);
			$wherearr[] = '(i.iname like \'%'.$q.'%\' or p.pname like \'%'.$q.'%\' or s.sname like \'%'.$q.'\')';
		}
		if(!empty($param['itemids'])){
			$wherearr[] = 'i.itemid in ('.$param['itemids'].')';
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'i.crid='.$param['crid'];
			$wherearr[] = 'p.crid='.$param['crid'];
		}
		$sql .= ' WHERE ' . implode(' AND ', $wherearr);
		if (!empty($param['sid'])) {
			$sql .= ' displayorder asc,folderid asc';
		} else {
			$sql .= ' order by itemid desc ,i.folderid desc';
		}
		return Ebh()->db->query($sql)->list_array();
	}
	
	/**
	 * 获取课程数量
	 * @param array $param
	 * @return int
	 */
	public function getItemCount($param) {
		if (empty($param['crid']) && empty($param['itemids']))
			return FALSE;
		$count = 0;
		$sql = 'select count(*) count
				from ebh_pay_items i
				left join ebh_pay_sorts s on i.sid=s.sid
				join ebh_pay_packages p on p.pid=i.pid';
		$wherearr = array('i.`status`=0','p.`status`=1');
		if(!empty($param['pid'])){
			$wherearr[] = 'i.pid=' . $param['pid'];
			if(!empty($param['sid'])){
				$wherearr[] = 'i.sid=' . $param['sid'];
			}
		}
		if(!empty($param['q'])){
			$q = Ebh()->db->escape_str($param['q']);
			$wherearr[] = '(i.iname like \'%'.$q.'%\' or p.pname like \'%'.$q.'%\' or s.sname like \'%'.$q.'\')';
		}
		if(!empty($param['itemids'])){
			$wherearr[] = 'i.itemid in ('.$param['itemids'].')';
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'i.crid='.$param['crid'];
			$wherearr[] = 'p.crid='.$param['crid'];
		}
		$sql .= ' WHERE ' . implode(' AND ', $wherearr);
		$row = Ebh()->db->query($sql)->row_array();
		if (!empty($row)){
			$count = $row['count'];
		}
		return $count;
	}
	
	/**
	 * 根据记录id,获取已选课程
	 * @param int $sourceid
	 * @return array
	 */
	public function getSelectedItems($param){
		if(empty($param['sourceid']) && empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select s.sourceid,itemid,folderid,price,month,del,s.crid,s.sourcecrid,s.name from ebh_schsourceitems si 
				join ebh_schsources s on s.crid=si.crid and s.sourcecrid=si.sourcecrid';
		if(!empty($param['sourceid'])){
			$wherearr[]= 's.sourceid='.$param['sourceid'];
		}
		if(!empty($param['crid'])){
			$wherearr[]= 's.crid='.$param['crid'];
		}
		// if(isset($param['del'])){
			$wherearr[]= 'si.del =0';//.$param['del'];
		// }
		if(!empty($param['itemid'])){
			$wherearr[]= 'si.itemid='.$param['itemid'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array('itemid');
	}
	
	
	/**
	 * 根据记录id,获取学校id
	 * @param int $sourceid
	 * @return array
	 */
	public function getCrinfoBySourceid($sourceid){
		$sql = 'select crid,sourcecrid,name from ebh_schsources where sourceid='.$sourceid;
		return Ebh()->db->query($sql)->row_array();
	}
	
	/*
	 *班级选择的课程
	 *@param array $param
	 *@return array
	*/
	public function getClassItems($param){
		if(empty($param['crid']) || empty($param['classids'])){
			return array();
		}
		$sql = 'select sc.crid,sc.classid,sc.itemid,sc.folderid,i.iname from ebh_schsourceclaasitems sc
				join ebh_schsourceitems si on si.itemid=sc.itemid and si.crid=sc.crid
				join ebh_pay_items i on sc.itemid=i.itemid';
		$wherearr[] = 'sc.crid='.$param['crid'];
		$wherearr[] = 'sc.classid in('.$param['classids'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array();
	}
	/*
	 *班级选择的课程数量
	 *@param array $param
	 *@return array
	*/
	public function getClassItemCount($param){
		if(empty($param['crid']) || empty($param['classids'])){
			return array();
		}
		$sql = 'select sc.classid,count(*) count from ebh_schsourceclaasitems sc
				join ebh_schsourceitems si on si.itemid=sc.itemid and si.crid=sc.crid
				join ebh_pay_items i on sc.itemid=i.itemid';
		$wherearr[] = 'sc.crid='.$param['crid'];
		$wherearr[] = 'sc.classid in('.$param['classids'].')';
		if(!empty($param['sourcecrid'])){
			$wherearr[] = 'si.sourcecrid='.$param['sourcecrid'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by sc.classid';
		return Ebh()->db->query($sql)->list_array('classid');
	}
	
	/*
	 *添加班级课程关联记录
	 */
	public function add($param){
		if(empty($param['classid']) || empty($param['itemids']) || empty($param['crid'])){
			return false;
		}
		
		$itemid = 0;
		$crid = $param['crid'];
		$classid = $param['classid'];
		$dateline = SYSTIME;
		$itemlist = $this->getListByItemids($crid,$param['itemids']);
		if(!empty($itemlist)){
			$param['itemlist'] = $itemlist;
			$sql_c = 'insert into ebh_schsourceclaasitems (crid,classid,itemid,folderid) values ';
			foreach ($itemlist as $item){
				$itemid = $item['itemid'];
				$folderid = $item['folderid'];
				$sql_c .= "($crid,$classid,$itemid,$folderid),";
			}
			$sql_c = rtrim($sql_c,',');
			Ebh()->db->query($sql_c);
			
			//userpermission添加
			$this->addUserpermision($param);
		}
		
	}
	
	/*
	根据itemids获取学校课程列表
	*/
	private function getListByItemids($crid,$itemids){
		if(empty($itemids) || empty($crid)){
			return array();
		}
		$sql = 'select crid,itemid,folderid from ebh_schsourceitems ';
		$itemids = implode(',',$itemids);
		$wherearr[] = 'itemid in ('.$itemids.')';
		$wherearr[] = 'crid='.$crid;
		$wherearr[] = 'del=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	清空课程及关联权限
	*/
	public function clearAllItems($param){
		if(empty($param['classid']) || empty($param['crid'])){
			return false;
		}
		$sql_1 = 'delete from ebh_schsourceclaasitems where classid = '.$param['classid'];
		$type = 2;
		$sql_2 = 'delete from ebh_userpermisions where crid = '.$param['crid'].' and type = '.$type. ' and classidschsource = '.$param['classid'];
		// Ebh()->db->begin_trans();
		$res_1 = Ebh()->db->query($sql_1,false);
		$res_2 = Ebh()->db->query($sql_2,false);
	}
	
	/*
	保存班级课程
	*/
	public function saveClassitem($param){
		Ebh()->db->begin_trans();
		$this->clearAllItems($param);
		if(empty($param['isclear'])){
			$this->add($param);
		}
		if(Ebh()->db->trans_status()===FALSE) {
            Ebh()->db->rollback_trans();
            return FALSE;
        } else {
            Ebh()->db->commit_trans();
        }
		return TRUE;
	}
	
	/*
	添加用户权限
	*/
	public function addUserpermision($param){
		$crid = $param['crid'];
		$classid = $param['classid'];
		$type = 2;
		$dateline = SYSTIME;
		if(!empty($param['uids']) && !empty($param['itemlist'])){
			$startdate = SYSTIME;
			$enddate = 2147483647;
			$sql_u = "insert into ebh_userpermisions (itemid,uid,type,crid,folderid,dateline,classidschsource,startdate,enddate) values ";
			foreach ($param['uids'] as $uid){
				foreach ($param['itemlist'] as $item){
					$itemid = $item['itemid'];
					$folderid = $item['folderid'];
					$sql_u .= "($itemid,$uid,$type,$crid,$folderid,$dateline,$classid,$startdate,$enddate),";	
				}
			}
			$sql_u = rtrim($sql_u,',');
			Ebh()->db->query($sql_u);
		}
	}
	
	/*
	删除用户权限
	*/
	public function delUserpermision($param){
		if(empty($param['classid']) || empty($param['uids']) || empty($param['crid'])){
			return FALSE;
		}
		$wherearr[] = 'classidschsource='.$param['classid'];
		$wherearr[] = 'uid in ('.implode(',',$param['uids']).')';
		$wherearr[] = 'crid = '.$param['crid'];
		$wherearr[] = 'type=2';
		$sql = 'delete from ebh_userpermisions where '.implode(' AND ',$wherearr);
		Ebh()->db->query($sql);
	}

    /*
     *部门选择的课程
     *@param $classid
     * @param $crid
     *@return array
    */
    public function getDeptItems($classid, $crid){
        $classid = intval($classid);
        $crid = intval($crid);
        /*$sql = 'select sc.crid,sc.classid,sc.itemid,sc.folderid,i.iname from ebh_schsourceclaasitems sc
				join ebh_schsourceitems si on si.itemid=sc.itemid and si.crid=sc.crid
				join ebh_pay_items i on sc.itemid=i.itemid';
        $wherearr[] = 'sc.crid='.$param['crid'];
        $wherearr[] = 'sc.classid in('.$param['classids'].')';
        $sql.= ' where '.implode(' AND ',$wherearr);*/
        $dept = Ebh()->db->query(
            'SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$classid.' AND `crid`='.$crid)
            ->row_array();
        if (empty($dept)) {
            return array();
        }
        $sql = 'SELECT `a`.`crid`,`a`.`classid`,`a`.`itemid`,`a`.`folderid`,`c`.`iname` FROM `ebh_schsourceclaasitems` `a`
                LEFT JOIN `ebh_classes` `b` ON `a`.`classid`=`b`.`classid`
                LEFT JOIN `ebh_pay_items` `c` ON `a`.`itemid`=`c`.`itemid`
                WHERE `a`.`crid`='.$crid.' AND `b`.`lft`>='.$dept['lft'].' AND `b`.`rgt`<='.$dept['rgt'];
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 企业选课来源列表
     * @param int $crid 网校ID
     * @param array $sourcecrids 来源网校ID集
     * @return array
     */
    public function getSourceSchoolList($crid, $sourcecrids) {
        $sql = 'SELECT `a`.`name`,`a`.`sourcecrid`,`b`.`crname` FROM `ebh_schsources` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`sourcecrid` WHERE `a`.`crid`='.$crid.' AND `sourcecrid` IN('.implode(',', $sourcecrids).')';
        $ret = Ebh()->db->query($sql)->list_array('sourcecrid');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }
}

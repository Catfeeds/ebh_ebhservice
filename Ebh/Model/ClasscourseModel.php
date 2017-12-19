<?php
/**
 * 教室课程关联模型
 */
class ClasscourseModel{
	//添加班级课程关联记录
	public function add($param){
		if(empty($param['classid']) || empty($param['folderids']) || empty($param['crid'])){
			return false;
		}
		
		$itemid = 0;
		$type = 2;
		$crid = $param['crid'];
		$classid = $param['classid'];
		$dateline = SYSTIME;
		$folderids = implode(',',$param['folderids']);
		if(!empty($folderids)){
			$sql_f = 'select max(itemid) itemid,folderid from ebh_pay_items where folderid in('.$folderids.') group by folderid';
			$itemlist = Ebh()->db->query($sql_f)->list_array('folderid');
		}
		$sql_c = 'insert into `ebh_classcourses` (classid,folderid) values ';
		foreach ($param['folderids'] as $folderid){
			$sql_c .= '('.$param['classid'].','.$folderid.'),';
		}
		$sql_c = rtrim($sql_c,',');
		Ebh()->db->query($sql_c);
		
		//userpermission添加
		if(!empty($param['uids'])){
			$startdate = $dateline;
			$enddate = 2147483647;
			$sql_u = "insert into ebh_userpermisions (itemid,uid,type,crid,folderid,dateline,classid,startdate,enddate) values ";
			foreach ($param['uids'] as $uid){
				foreach($param['folderids'] as $folderid){
					$itemid = empty($itemlist[$folderid])?0:$itemlist[$folderid]['itemid'];
					$sql_u .= "($itemid,$uid,$type,$crid,$folderid,$dateline,$classid,$startdate,$enddate),";	
				}
			}
			$sql_u = rtrim($sql_u,',');
			Ebh()->db->query($sql_u);
		}
		
	}
	//删除班级课程关联记录
	public function delete($param){
		if(empty($param['classid'])){
			return -1;
		}
		$where = ' classid = '.$param['classid'];
		if(!empty($param['folderidstr'])){
			$where .= ' and folderid in ('.$param['folderidstr'].')';
		}
		return Ebh()->db->delete('ebh_classcourses',$where);	
	}
	//根据条件获取folderid、foldername
	public function getFolders($param){
		$wharr = array();
		if(empty($param['isstats'])){
			$sql = 'select c.folderid,f.foldername,c.classid from `ebh_classcourses` c left join `ebh_folders` f on c.folderid = f.folderid';
		} else {//统计分析用的关联
			$sql = 'select c.itemid,i.iname,c.classid from `ebh_classcoursestats` c left join `ebh_pay_items` i on c.itemid = i.itemid';
		}
		if(!empty($param['classids'])){
			$wharr[] = 'c.classid in ('.$param['classids'].')';
		}
		if(!empty($param['crid'])){
			$wharr[] = (empty($param['isstats'])?'f':'i').'.crid = '.$param['crid'];	
		}
		if(!empty($wharr)){
			$sql .= ' where '.implode(' and ', $wharr);
		}
		if(!empty($param['limit'])){
			$sql .= ' limit ' . $param['limit'];
		}
		return Ebh()->db->query($sql)->list_array();
	}
	//根据classid获取已选的folderid
	public function getFolderidsByClassid($classid){
		if(empty($classid)){
			return array();
		}
		$sql = 'select folderid from `ebh_classcourses` where classid in ('.$classid.')';
		return Ebh()->db->query($sql)->list_array();
	}
	/*
	 *根据classid获取已选的数量
	*/
	public function getFolderidCountByClassid($classid){
		if(empty($classid)){
			return array();
		}
		$sql = 'select count(*) count,classid from `ebh_classcourses` where classid in ('.$classid.') group by classid';
		return Ebh()->db->query($sql)->list_array('classid');
	}
	/*
	清空课程及关联权限
	*/
	public function clearAllCourses($param){
		if(empty($param['classid']) || empty($param['crid'])){
			return false;
		}
		$sql_1 = 'delete from ebh_classcourses where classid = '.$param['classid'];
		$type = 2;
		$sql_2 = 'delete from ebh_userpermisions where crid = '.$param['crid'].' and type = '.$type. ' and classid = '.$param['classid'];
		// Ebh()->db->begin_trans();
		$res_1 = Ebh()->db->query($sql_1,false);
		$res_2 = Ebh()->db->query($sql_2,false);
		// if($res_2 && $res_2){
			// 提交事务
			// Ebh()->db->commit_trans();
		// }else{
			// 回滚
			// Ebh()->db->rollback_trans();
		// }
		// return $res_1 && $res_2;
	}
	
	/*
	保存班级课程
	*/
	public function saveClasscourse($param){
		Ebh()->db->begin_trans();
		$this->clearAllCourses($param);
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
}
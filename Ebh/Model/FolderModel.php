<?php
/**
 * 课程相关model类 FolderModel
 */
class FolderModel{
	private $db;
	function __construct()
    {
        $this->db = Ebh()->db;
    }
    /**
     * 添加课程对应的课件数
     * @param int $folderid 课程编号
     * @param int $num 如为正数则添加，负数则为减少
     */
    public function addcoursenum($folderid,$num = 1) {
        $where = 'folderid='.$folderid;
        $setarr = array('coursewarenum'=>'coursewarenum+'.$num);
        Ebh()->db->update('ebh_folders',array(),$where,$setarr);
    }
    /**
     * 根据课程编号获取课程详情信息
     * @param int $folderid 课程编号
	 * @param int $crid 教室编号
     * @return array 课程信息数组 
     */
    public function getFolderById($folderid,$crid = 0) {
    	if(empty($folderid))return false;
        $sql = 'select f.folderid,f.foldername,f.displayorder,f.img,f.coursewarenum,f.summary,f.grade,f.district,f.upid,f.folderlevel,f.folderpath,f.fprice,f.speaker,f.detail,f.viewnum,f.coursewarelogo,f.power,f.credit,f.creditrule,f.playmode,f.isremind,f.remindmsg,f.remindtime,f.creditmode,f.credittime,f.showmode,f.introduce,f.isschoolfree,f.uid,ifnull(i.introtype,0) as introtype,ifnull(i.attid,0) as attid,ifnull(i.slides,\'\') as slides,f.cwcredit,f.cwpercredit,f.examcredit,f.exampercredit,f.creditdate
		from ebh_folders f left join ebh_folder_intros i on i.folderid=f.folderid where f.folderid='.
            $folderid.' and ifnull(i.`status`,0)=0';
		if($crid>0){
			$sql .= ' and f.crid = ' . $crid;
		}
		return Ebh()->db->query($sql)->row_array();
    }
	
	/**
	*获取课程列表
	*在cq,hh,fssq大纲导航中可调用到,stores答疑专区
	*/
	public function getFolderList($param){
		$sql = 'SELECT f.crid,f.foldername,f.img,f.summary,f.folderpath,f.folderlevel,f.folderid,f.coursewarenum,f.fprice,f.viewnum,f.grade,f.credit,f.showmode FROM ebh_folders f ';
        $wherearr = array();
 
		if(! empty ( $param ['folderid'] )){
			$wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
		}
		if(! empty ( $param ['crid'] )){
			$wherearr [] = ' f.crid = ' . $param ['crid'];
		}
		if(! empty ( $param ['uid'] )){
			$wherearr [] = ' f.uid = ' . $param ['uid'];
		}
		if(! empty ( $param ['status'] )){
			$wherearr [] = ' f.status = ' . $param ['status'];
		}
		if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
			$wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
		}
		if(! empty ( $param ['folderlevel'] )){
			$wherearr [] = ' f.folderlevel = ' . $param ['folderlevel'];
		}
		if(isset ( $param ['upid'] )){
			$wherearr [] = ' f.upid <> ' . $param ['upid'];
		}
		if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
			$wherearr [] = ' f.coursewarenum  > 0 ';
		}
		if(isset($param['filternum'])){
			$wherearr [] = ' f.coursewarenum > 0';
		}
		if(isset($param['nosubfolder'])){
			$wherearr [] = ' f.folderlevel = 2';
		}
		if(!empty($param['needpower'])){
			$wherearr [] = ' f.power = 0';
		}
		if(isset($param['isschoolfree'])){
			$wherearr [] = ' f.isschoolfree='.$param['isschoolfree'];
		}
		if(isset($param['q']))
			$wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
		$wherearr [] = 'f.del=0';
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        if(!empty($param['order'])) {
            $sql .= ' ORDER BY '.$param['order'];
        } else {
            $sql .= ' ORDER BY f.displayorder';
        }
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
	*课程数量（适用大纲导航数量）
	*/
	public function getCount($param){
		$count = 0;
		$sql = 'select count(*) count from ebh_folders f';
		$wherearr = array();
		if(! empty ( $param ['folderid'] )){
			$wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
		}
		if(! empty ( $param ['crid'] )){
			$wherearr [] = ' f.crid = ' . $param ['crid'];
		}
		if(! empty ( $param ['status'] )){
			$wherearr [] = ' f.status = ' . $param ['status'];
		}
		if(! empty ( $param ['folderlevel'] )){
			$wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
		}
		if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
			$wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
		}
		if(isset ( $param ['upid'] )){
			$wherearr [] = ' f.upid <> ' . $param ['upid'];
		}
		if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
			$wherearr [] = ' f.coursewarenum  > 0 ';
		}
		if(isset($param['filternum'])){
			$wherearr [] = ' f.coursewarenum > 0';
		}
		if(isset($param['nosubfolder'])){
			$wherearr [] = ' f.folderlevel = 2';
		}
		if(isset($param['q']))
			$wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
		$wherearr[] = 'f.del = 0';
		$sql .= ' WHERE '.implode(' AND ', $wherearr);
		$row = Ebh()->db->query($sql)->row_array();
		if(!empty($row))
			$count = $row['count'];
        return $count;
	}

	/**
	*获取学生网校课程列表（加上学生是否已选课）
	*/
	public function getmemberfolderlist($param){
		if(empty($param['uid']))
			return FALSE;
		$sql = 'SELECT f.foldername,f.img,f.summary,f.folderpath,f.folderid,f.coursewarenum,fa.fid FROM ebh_folders f '.
				'LEFT JOIN ebh_favorites fa ON f.crid=fa.crid AND fa.uid='.$param['uid'].' AND f.folderid=fa.folderid AND fa.type=3 ';
        $wherearr = array();
		if(! empty ( $param ['folderid'] )){
			$wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
		}
		if(! empty ( $param ['crid'] )){
			$wherearr [] = ' f.crid = ' . $param ['crid'];
		}
		if(! empty ( $param ['q'] )){	//按课程名称搜索
			$wherearr [] = ' f.foldername like \'%' . $param ['q'] .'%\'';
		}
		if(! empty ( $param ['status'] )){
			$wherearr [] = ' f.status = ' . $param ['status'];
		}
		if(! empty ( $param ['folderlevel'] )){
			$wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
		}
		if(isset ( $param ['upid'] )){
			$wherearr [] = ' f.upid <> ' . $param ['upid'];
		}
		if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
			$wherearr [] = ' f.coursewarenum  > 0 ';
		}
		if(isset($param['filternum'])){
			$wherearr [] = ' f.coursewarenum > 0';
		}
		if(isset($param['haschoose'])) {	//是否选课标示
			if($param['haschoose'] == 0) {	//未选课
				$wherearr [] = ' fa.fid IS NULL ';
			} else if($param['haschoose'] == 1) {	//已选课
				$wherearr [] = ' fa.fid IS NOT NULL ';
			}
		}
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        if(!empty($param['order'])) {
            $sql .= ' ORDER BY '.$param['order'];
        } else {
            $sql .= ' ORDER BY f.displayorder,f.folderid';
        }
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
	//大纲导航数量
	public function getmemberfoldercount($param){
		$count = 0;
		if(!empty($param['uid']))
			return $count;
		$sql = 'SELECT count(*) countFROM ebh_folders f '.
				'LEFT JOIN ebh_favorites fa ON f.crid=fa.crid AND fa.uid='.$param['uid'].' AND f.folderid=fa.folderid AND fa.type=3 ';
        $wherearr = array();
		if(! empty ( $param ['folderid'] )){
			$wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
		}
		if(! empty ( $param ['crid'] )){
			$wherearr [] = ' f.crid = ' . $param ['crid'];
		}
		if(! empty ( $param ['q'] )){	//按课程名称搜索
			$wherearr [] = ' f.foldername like \'%' . $param ['q'] .'%\'';
		}
		if(! empty ( $param ['status'] )){
			$wherearr [] = ' f.status = ' . $param ['status'];
		}
		if(! empty ( $param ['folderlevel'] )){
			$wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
		}
		if(isset ( $param ['upid'] )){
			$wherearr [] = ' f.upid <> ' . $param ['upid'];
		}
		if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
			$wherearr [] = ' f.coursewarenum  > 0 ';
		}
		if(isset($param['filternum'])){
			$wherearr [] = ' f.coursewarenum > 0';
		}
		if(isset($param['haschoose'])) {	//是否选课标示
			if($param['haschoose'] == 0) {	//未选课
				$wherearr [] = ' fa.fid IS NULL ';
			} else if($param['haschoose'] == 1) {	//已选课
				$wherearr [] = ' fa.fid IS NOT NULL ';
			}
		}
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
		$row = Ebh()->db->query($sql)->row_array();
		if(!empty($row))
			$count = $row['count'];
        return $count;
	}
	
	/*
	添加folder
	@param array $param
	*/
	public function addFolder($param){
	//	print_r($param);exit;
		if(!empty($param['uid']))
			$farr['uid'] = $param['uid'];
		if(!empty($param['crid']))
			$farr['crid'] = $param['crid'];
		if(!empty($param['foldername']))
			$farr['foldername'] = $param['foldername'];
		if(!empty($param['upid']))
			$farr['upid'] = $param['upid'];
		$farr['folderlevel'] = empty($param['folderlevel'])?2:$param['folderlevel'];
		
		if(!empty($param['displayorder']))
			$farr['displayorder'] = $param['displayorder'];
		if(!empty($param['summary']))
			$farr['summary'] = $param['summary'];
		if(!empty($param['img']))
			$farr['img'] = $param['img'];
		if(!empty($param['grade']))
			$farr['grade'] = $param['grade'];
		if(isset($param['fprice']))
			$farr['fprice'] = $param['fprice'];
		if(isset($param['speaker']))
			$farr['speaker'] = $param['speaker'];
		if(isset($param['detail']))
			$farr['detail'] = $param['detail'];
		if(isset($param['coursewarelogo']))
			$setarr['coursewarelogo'] = $param['coursewarelogo'];
		if(isset($param['isschoolfree']))
			$setarr['isschoolfree'] = $param['isschoolfree'];
		if(isset($param['power']))
			$setarr['power'] = $param['power'];
		if(isset($param['credit']))
			$farr['credit'] = $param['credit'];
		if(isset($param['creditmode']))
			$setarr['creditmode'] = $param['creditmode'];
		if(empty($param['iszjdlr'])){//非国土
			if(isset($param['cwcredit']))
				$setarr['cwcredit'] = $param['cwcredit'];
			if(isset($param['cwpercredit']))
				$setarr['cwpercredit'] = $param['cwpercredit'];
			if(isset($param['examcredit']))
				$setarr['examcredit'] = $param['examcredit'];
			if(isset($param['exampercredit']))
				$setarr['exampercredit'] = $param['exampercredit'];
		} else {//国土
			if(isset($param['creditrule']))
				$setarr['creditrule'] = $param['creditrule'];
		}
		if(isset($param['credittime']))
			$setarr['credittime'] = $param['credittime'];
		if(isset($param['playmode']))
			$setarr['playmode'] = $param['playmode'];
		if(isset($param['isremind']))
			$setarr['isremind'] = $param['isremind'];
		if(isset($param['remindmsg']))
			$setarr['remindmsg'] = $param['remindmsg'];
		if(isset($param['remindtime']))
			$setarr['remindtime'] = $param['remindtime'];
		if(isset($param['showmode']))
			$setarr['showmode'] = $param['showmode'];
		$setarr['detail'] = !isset($param['detail']) ? '' : trim($param['detail']);
		
		$farr['introduce'] = '';

		$folderid = Ebh()->db->insert('ebh_folders',$farr);
		if(!empty($param['folderpath'])){
			$setarr['folderpath'] = $param['folderpath'].$folderid.'/';
		}
		$wherearr['folderid'] = $folderid;
		Ebh()->db->update('ebh_folders',$setarr,$wherearr);
		return $folderid;
	}
	
	/*
	选择课程任课教师
	@param array $param
	*/
	public function chooseTeacher($param){
		Ebh()->db->begin_trans();
		$crid = $param['crid'];
		$folderid = $param['folderid'];
		if(!empty($folderid)){
			$wherearr['folderid'] = $folderid;
			//return $wherearr;
			Ebh()->db->delete('ebh_teacherfolders',$wherearr);
		}
		if(!empty($param['tids'])){
			$idarr = explode(',',$param['tids']);
			$insertsql = 'insert into ebh_teacherfolders (tid,folderid,crid) values ';
			$valuearr = array();
			foreach($idarr as $id){
				$valuearr[] = "($id,$folderid,$crid)";
			}
			$insertsql.= implode(',',$valuearr);
			Ebh()->db->query($insertsql);
		}
		if(Ebh()->db->trans_status()===FALSE) {
            Ebh()->db->rollback_trans();
            return FALSE;
        } else {
            Ebh()->db->commit_trans();
        }
		return TRUE;
	}

    /**
     * 添加任课教师
     * @param $param
     */
    public function addteacher($param){
        $idarr = $param['teacherids'];
        if(is_array($idarr) && count($idarr) > 0){
            foreach($idarr as $tid){
                if(intval($tid) > 0){
                    $sql = 'select crid from ebh_teacherfolders where crid = '.$param['crid'].' and tid = '.$tid.' and folderid = '.$param['folderid'];

                    if(!Ebh()->db->query($sql)->row_array()){
                        $tfarr = array('tid'=>$tid,'folderid'=>$param['folderid'],'crid'=>$param['crid']);
                        Ebh()->db->insert('ebh_teacherfolders',$tfarr);
                    }
                }

            }
        }
    }
	
	/*
	删除课程
	@param array $param
	*/
	public function deleteCourse($param, $delete_item = false){
        if (empty($param['folderid']) || empty($param['crid'])) {
            return false;
        }
        $folderid = (int) $param['folderid'];
        $crid = (int) $param['crid'];
		Ebh()->db->begin_trans();
        $wherearr['folderid'] = $param['folderid'];
        $wherearr['crid'] = $param['crid'];
        //Ebh()->db->delete('ebh_folders',$wherearr);
        $affectedRows = Ebh()->db->update('ebh_folders', array('del' => 1), $wherearr);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        if (!empty($affectedRows)) {
            Ebh()->db->delete('ebh_teacherfolders',$wherearr);
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
            if ($delete_item) {
                //删除开场内容
                Ebh()->db->delete('ebh_folder_intros', '`folderid`='.$folderid);
            }
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }

        if ($delete_item) {
            Ebh()->db->update('ebh_pay_items', array('status' => 2), "`folderid`=$folderid AND `crid`=$crid");
        }
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
		return TRUE;
	}
	/*
	编辑课程
	@param array $param
	*/
	public function editCourse($param){
		if(!empty($param['foldername']))
			$setarr['foldername'] = $param['foldername'];
		if(isset($param['displayorder']))
			$setarr['displayorder'] = $param['displayorder'];
		if(isset($param['summary']))
			$setarr['summary'] = $param['summary'];
		if(!empty($param['img']))
			$setarr['img'] = $param['img'];
		if(isset($param['fprice']))
			$setarr['fprice'] = $param['fprice'];
		if(isset($param['speaker']))
			$setarr['speaker'] = $param['speaker'];
		if(isset($param['detail']))
			$setarr['detail'] = $param['detail'];
		if(isset($param['grade']))
			$setarr['grade'] = $param['grade'];
		if(isset($param['coursewarelogo']))
			$setarr['coursewarelogo'] = $param['coursewarelogo'];
		if(isset($param['isschoolfree']))
			$setarr['isschoolfree'] = $param['isschoolfree'];
		if(isset($param['power']))
			$setarr['power'] = $param['power'];
		if(empty($param['iszjdlr'])){//非国土
			if(!empty($param['updatecredit'])){
				if(isset($param['credit']))
					$setarr['credit'] = $param['credit'];
				if(isset($param['creditmode']))
					$setarr['creditmode'] = $param['creditmode'];
				if(isset($param['cwcredit']))
					$setarr['cwcredit'] = $param['cwcredit'];
				if(isset($param['cwpercredit']))
					$setarr['cwpercredit'] = $param['cwpercredit'];
				if(isset($param['examcredit']))
					$setarr['examcredit'] = $param['examcredit'];
				if(isset($param['exampercredit']))
					$setarr['exampercredit'] = $param['exampercredit'];
				if(isset($param['credittime']))
					$setarr['credittime'] = $param['credittime'];
				if(!empty($param['creditdate']))
					$setarr['creditdate'] = $param['creditdate'];
			}
		} else {//国土
			if(isset($param['credit']))
				$setarr['credit'] = $param['credit'];
			if(isset($param['creditmode']))
				$setarr['creditmode'] = $param['creditmode'];
			if(isset($param['creditrule']))
				$setarr['creditrule'] = $param['creditrule'];
			if(isset($param['credittime']))
				$setarr['credittime'] = $param['credittime'];
		}
		
		if(isset($param['playmode']))
			$setarr['playmode'] = $param['playmode'];
		if(isset($param['isremind']))
			$setarr['isremind'] = $param['isremind'];
		if(isset($param['remindmsg']))
			$setarr['remindmsg'] = $param['remindmsg'];
		if(isset($param['remindtime']))
			$setarr['remindtime'] = $param['remindtime'];
		if(isset($param['showmode']))
			$setarr['showmode'] = $param['showmode'];
		if(!empty($param['introduce']))
			$setarr['introduce'] = $param['introduce'];
		$wherearr['crid'] = $param['crid'];
		$wherearr['folderid'] = $param['folderid'];
		return Ebh()->db->update('ebh_folders',$setarr,$wherearr);
	}
	/**
	* 移动课程的位置
	* @param int $flag 1为上移 0为下移
	*/
	public function move($crid,$folderid,$flag) {
		$sql = "SELECT f.folderid,f.upid,f.displayorder,f.folderlevel FROM ebh_folders f WHERE f.folderid=$folderid AND f.crid=$crid";
		$folder = Ebh()->db->query($sql)->row_array();
		if(empty($folder))
			return FALSE;
		$displayorder = $folder['displayorder'];
		// $upid = $folder['upid'];
		$folderlevel = $folder['folderlevel'];
		if($flag == 1) { //上移
			$upsql = "SELECT f.folderid,f.displayorder FROM ebh_folders f WHERE f.crid=$crid AND f.folderlevel=$folderlevel AND ((f.folderid<$folderid AND f.displayorder=$displayorder) OR f.displayorder<$displayorder) ORDER BY f.displayorder DESC,f.folderid DESC LIMIT 0,1";
			$next = Ebh()->db->query($upsql)->row_array();
		} else {	//下移
			$downsql = "SELECT f.folderid,f.displayorder FROM ebh_folders f WHERE f.crid=$crid AND f.folderlevel=$folderlevel AND ((f.folderid>$folderid AND f.displayorder=$displayorder) OR f.displayorder>$displayorder) ORDER BY f.displayorder,f.folderid LIMIT 0,1";
			$next = Ebh()->db->query($downsql)->row_array();
		}
		if(empty($next))	//已经是最大或最小
			return TRUE;
		if($displayorder != $next['displayorder']) {	//如果排序号没有相同，则只需呼唤两个displayorder即可
			$afrow1 = Ebh()->db->update('ebh_folders',array('displayorder'=>$displayorder),array('folderid'=>$next['folderid']));
			$afrows = Ebh()->db->update('ebh_folders',array('displayorder'=>$next['displayorder']),array('folderid'=>$folderid));
		} else {
			if($flag == 1) {
				$afrows = Ebh()->db->update('ebh_folders',array(),array('folderid'=>$folderid),array('displayorder'=>'displayorder-1'));
			} else {
				$afrows = Ebh()->db->update('ebh_folders',array(),array('folderid'=>$folderid),array('displayorder'=>'displayorder+1'));
			}
		}
		if ($afrows > 0) {
			return true;
		}
		return false;
	}

	/**
	*获取班级对应的教师课程
	*/
	public function getClassFolder($param) {
		$sql = 'select ct.uid from ebh_classteachers ct where ct.classid in ('.$param['classid'].')';
		$tidlist = Ebh()->db->query($sql)->list_array();
		$tids = '';
		if(!empty($tidlist)) {
			foreach($tidlist as $tid) {
				if(empty($tids))
					$tids = $tid['uid'];
				else
					$tids .= ','.$tid['uid'];
			}
		}
		if(!empty($param['grade'])){
			$gradestr = ' or f.grade = '.$param['grade'];
		}else{
			$gradestr = '';
		}
		if(!empty($tids) || !empty($param['grade'])) {
			if(empty($tids))
				$tids = '\'\'';
			$fsql = 'select f.folderid,f.foldername,f.coursewarenum,f.img,f.credit,f.creditrule,f.playmode,f.showmode,f.creditmode,f.credittime from ebh_folders f '.
					'where (f.folderid in(select tf.folderid from ebh_teacherfolders tf  '.
					'where tf.tid in ('.$tids.')) '.$gradestr.')and f.crid='.$param['crid'].' and f.power=0';
			if(!empty($param['order']))
				$fsql.= ' order by '.$param['order'];
			if(!empty($param['limit']))
				$fsql .= ' limit '.$param['limit'];
			else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$fsql .= ' limit ' . $start . ',' . $pagesize;
			}
			return Ebh()->db->query($fsql)->list_array();
		}
		return FALSE;
	}
	
	/*
	isschool!=7 ,只按年级获取课程
	*/
	public function getClassFolderWithoutTeacher($param){
		$sql = 'select f.folderid,f.foldername,f.coursewarenum,f.img,f.credit,f.creditrule,f.playmode,f.showmode,f.creditmode,f.credittime from ebh_folders f '.
					'where f.grade = '.$param['grade'].' and f.crid='.$param['crid'].' and f.power=0';
			
			if(!empty($param['order']))
				$sql.= ' order by '.$param['order'];
			if(!empty($param['limit']))
				$sql .= ' limit '.$param['limit'];
			else {
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
	/**
	*获取班级对应的教师课程记录数
	*/
	public function getClassFolderCount($param) {
		$count = 0;
		$sql = 'select ct.uid from ebh_classteachers ct where ct.classid='.$param['classid'];
		$tidlist = Ebh()->db->query($sql)->list_array();
		$tids = '';
		if(!empty($tidlist)) {
			foreach($tidlist as $tid) {
				if(empty($tids))
					$tids = $tid['uid'];
				else
					$tids .= ','.$tid['uid'];
			}
		}
		if(!empty($param['grade'])){
			$gradestr = ' or f.grade = '.$param['grade'];
		}else{
			$gradestr = '';
		}
		if(!empty($tids) || !empty($param['grade'])) {
			if(empty($tids))
				$tids = '\'\'';
			$fsql = 'select count(*) count from ebh_folders f '.
					'where (f.folderid in(select tf.folderid from ebh_teacherfolders tf  '.
					'where tf.tid in ('.$tids.')) '.$gradestr.') and f.crid='.$param['crid'].' and f.power=0';
			$countrow = Ebh()->db->query($fsql)->row_array();
			if(!empty($countrow))
				$count = $countrow['count'];
		}
		return $count;
	}
	/**
	*获取学校教师对应的课程列表
	*/
	public function getTeacherFolderList($param) {
		if(empty($param['uid']) && empty($param['crid']))
			return FALSE;
		$sql = 'SELECT u.uid,u.username,u.realname,u.face,u.sex FROM ebh_roomteachers rt '.
				'JOIN ebh_users u on (u.uid = rt.tid)';
		$wherearr = array();
		if(!empty($param['crid']))
			$wherearr[] = 'rt.crid='.$param['crid'];
		if(!empty($param['uid']))
			$wherearr[] = 'rt.tid='.$param['uid'];
		$sql .= ' WHERE '.implode(' AND ',$wherearr);
		$list = Ebh()->db->query($sql)->list_array();
		$ids = '';
		$teacherlist = array();
		foreach($list as $teacher) {
			$teacherlist[$teacher['uid']] = $teacher;
			$teacherlist[$teacher['uid']]['folder'] = array();
			if(empty($ids))
				$ids = $teacher['uid'];
			else
				$ids .= ','.$teacher['uid'];
		}
		if(!empty($ids)) {
			$fsql = 'SELECT f.folderid,f.foldername,tf.tid from ebh_folders f '.
					'join ebh_teacherfolders tf on (tf.folderid=f.folderid) '.
					'WHERE tf.crid='.$param['crid'].' and tf.tid in ('.$ids.')';
				if(isset($param['power']))
					$fsql.= ' and f.power in ('.$param['power'].')';
			$folders = Ebh()->db->query($fsql)->list_array();
			foreach($folders as $folder) {
				$teacherlist[$folder['tid']]['folder'][] = $folder;
			}
		}
		return $teacherlist;
	}
	/*
	教师的课程数
	*/
	public function getTeacherFolderCount($param){
		$sql = 'select count(*) count from ebh_folders f
			join ebh_teacherfolders tf on f.folderid = tf.folderid';
			$wherearr[]= 'f.crid='.$param['crid'];
			$wherearr[]= 'tf.tid='.$param['uid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$count = Ebh()->db->query($sql)->row_array();
		return $count['count'];
	}
	/*
	教师课程列表
	*/
	public function getTeacherFolderList1($param){
		$wherearr = array();
		$sql = 'SELECT f.uid,f.foldername,f.img,f.summary,f.folderpath,f.folderid,f.coursewarenum,f.viewnum 
		FROM ebh_folders f 
		join ebh_teacherfolders tf on f.folderid = tf.folderid';
		$wherearr[]= 'f.crid='.$param['crid'];
		$wherearr[]= 'tf.tid='.$param['uid'];
		if(isset($param['power']))
			$wherearr[]= 'f.power in ('.$param['power'].')';
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
	按课程名(和crid)获取课程信息
	*/
	public function getFolderByFoldername($param){
		if(empty($param['crid']) || empty($param['foldername']))
			return false;
		$sql = 'select folderid from ebh_folders where crid='.$param['crid'].' and foldername=\''.Ebh()->db->escape_str($param['foldername']).'\'';
		return Ebh()->db->query($sql)->row_array();
	}
	
	/*
	添加一个教师到课程
	*/
	public function addTeacherToFolder($foldertarr,$crid){
		// if(empty($param['tid']) || empty($param['crid']) || empty($param['folderid']))
			// return false;
		// $tfarr = array('tid'=>$param['tid'],'folderid'=>$param['folderid'],'crid'=>$param['crid']);
		// Ebh()->db->insert('ebh_teacherfolders',$tfarr);
		
		
		$sql = 'insert into ebh_teacherfolders (tid,folderid,crid) values ';
		$oldersql = $sql;
		foreach($foldertarr as $teacher){
			if(!empty($teacher['folderidarr'])){
				foreach($teacher['folderidarr'] as $folderid){
					// $folderid = $teacher['folderid'];
					$tid = $teacher['uid'];
					$sql.= "($tid,$folderid,$crid),";
				}
			}
		}
		if($sql == $oldersql){
			return;
		}
		$sql = rtrim($sql,',');
		Ebh()->db->query($sql);
	}
	/*
	获取子目录
	*/
	public function getSubFolder($crid,$folderid) {
        $sql = 'select f.folderid,f.foldername,f.img,f.coursewarenum from ebh_folders f where f.crid='.$crid.' and f.upid ='.$folderid;
        return Ebh()->db->query($sql)->list_array();
    }
	
	/*
	增加课程人气
	*/
	public function addviewnum($folderid,$num = 1) {
        $where = 'folderid='.$folderid;
        $setarr = array('viewnum'=>'viewnum+'.$num);
        Ebh()->db->update('ebh_folders',array(),$where,$setarr);
    }

	/*
	更新课程人气
	*/
	public function updateviewnum($folderid,$viewnum){
		$where = 'folderid='.$folderid;
        $setarr = array('viewnum'=>$viewnum);
        Ebh()->db->update('ebh_folders',array(),$where,$setarr);
	}
	
	/*
	设置人气数
	*/
	public function setviewnum($folderid, $num = 1) {
		$where = 'folderid=' . $folderid;
        $setarr = array('viewnum' => $num);
        Ebh()->db->update('ebh_folders', array(), $where, $setarr);
	}
	/**
	 *获取学校下面的所有的课程
	 */
	public function getSchoolFolder($crid = 0){
		$sql = 'SELECT f.folderid,f.foldername from ebh_folders f where crid = '.intval($crid).' AND `del`=0';
		return Ebh()->db->query($sql)->list_array();
	}
	/*
	* 获取有反馈的课程及课件
	*/
	public function getSubFolders($crid){
		$sql = 'select s.sid,s.folderid,s.sname,s.coursewarecount,fo.foldername,c.cwid,c.title,c.examnum ,c.cwurl,c.attachmentnum,f.fid,f.feedback,f.dateline 
		from ebh_feedbacks f 
		left join ebh_coursewares c on (c.cwid = f.cwid ) 
		left join ebh_roomcourses rc on (rc.cwid = c.cwid) 
		left join ebh_folders fo on (fo.folderid = rc.folderid) 
		left join ebh_sections s on (s.folderid = fo.folderid) 
		where s.crid = '.$crid.' group by f.fid order by s.folderid desc ';
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	一次设置多个viewnum
	*/
	public function setMultiViewnum($viewnumlist){
		$sql = 'update ebh_folders set viewnum= CASE folderid';
		$wtArr = array();
		$inArr = array();
		foreach($viewnumlist as $folderid=>$viewnum){
			if(!empty($folderid)){
				$wtArr[] = ' WHEN '.$folderid.' THEN '.$viewnum;
				$inArr[] = $folderid;
			}
		}
		$sql.= implode(' ', $wtArr).' END WHERE folderid IN ('.implode(',', $inArr).')';
		Ebh()->db->query($sql);
		return Ebh()->db->affected_rows();
	}
	
	public function getViewnumWithCW(){
		$sql = 'select f.folderid,sum(cw.viewnum) cwviewnum,f.viewnum from ebh_coursewares cw 
				join ebh_roomcourses rc on cw.cwid=rc.cwid 
				join ebh_folders f on f.folderid=rc.folderid 
				group by f.folderid
				having sum(cw.viewnum)>f.viewnum
				';
		$viewnumlist = Ebh()->db->query($sql)->list_array();
		return $viewnumlist;
	}

	/**
	 *获取学校的收费课程
	 */
	public function getNotFreeFolderList($crid = 0,$hideschoolfree=false){
		$sql = 'SELECT f.folderid,f.fprice FROM ebh_folders f where f.fprice >0 AND f.crid = '.$crid;
		if(!empty($hideschoolfree))
			$sql .= ' and isschoolfree=0';
		return Ebh()->db->query($sql)->list_array();
	}

	/**
	 *获取课程下的教师
	 */
	public function getFolderTeacher($folderid = 0){
		$sql = 'SELECT t.tid from ebh_teacherfolders t WHERE t.folderid = '.$folderid;
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	多个老师的所教课程
	*/
	public function getTeachersFolderList($param){
		$wherearr = array();
		$sql = 'SELECT tf.tid,f.foldername,f.img,f.folderid,f.grade,u.realname,u.uid 
		FROM ebh_folders f 
		join ebh_teacherfolders tf on f.folderid = tf.folderid
		join ebh_users u on tf.tid=u.uid
		';
		if(!empty($param['tids']))
			$wherearr[]= 'tf.tid in ('.$param['tids'].')';
		$wherearr[]= 'tf.crid='.$param['crid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' order by f.grade desc,uid';
		// echo $sql;exit;
		return Ebh()->db->query($sql)->list_array();
	}
	/*
	 *学校课程评论数
	*/
	public function getAllReviewnum($param){
		$sql = 'select sum(cw.reviewnum) from ebh_coursewares cw left join ebh_roomcourses rc on(rc.cwid = cw.cwid) left join ebh_folders fl on (fl.folderid = rc.folderid) where fl.crid = '.$param['crid'];
		$count = Ebh()->db->query($sql)->row_array();
		return $count['sum(cw.reviewnum)'];
	}
	
	/*
	多个老师的所教课程数
	*/
	public function getTeachersFolderCount($param){
		$sql = 'select count(*) foldernum,tid from ebh_folders f 
		join ebh_teacherfolders tf on f.folderid=tf.folderid ';
		if(!empty($param['crid']))
			$wherearr[] = 'f.crid='.$param['crid'];
		if(!empty($param['uids']))
			$wherearr[] = 'tf.tid in ('.$param['uids'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by tf.tid';
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	获取同级课程最小排序号
	*/
	public function getCurDisplayorder($param){
		$sql = 'select min(displayorder) mdis from ebh_folders';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'folderlevel='.$param['folderlevel'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$res = Ebh()->db->query($sql)->row_array();
		return $res['mdis'];
	}
	
	/*
	学习新后台课程移动
	*/
	public function moveit($param){
		if(empty($param['folderid']))
			return false;
		$sql = "SELECT f.folderid,f.upid,f.displayorder,f.folderlevel FROM ebh_folders f WHERE f.folderid=".$param['folderid']." and f.crid=".$param['crid'];
		$thisfolder = Ebh()->db->query($sql)->row_array();
		$sqlsameorder = "SELECT f.folderid,f.displayorder FROM ebh_folders f WHERE f.folderlevel=".$thisfolder['folderlevel']." and f.crid=".$param['crid']." and displayorder=".$thisfolder['displayorder']." and f.folderid<>".$thisfolder['folderid'];
		$sameorder = Ebh()->db->query($sqlsameorder)->row_array();
		if(!empty($sameorder)){
			if($param['compare'] == '<')
				$op = '-';
			else
				$op = '+';
			$sqlAllforone = 'update ebh_folders set displayorder=displayorder'.$op.'1 where crid='.$param['crid'].' and displayorder'.$param['compare'].'='.$thisfolder['displayorder'].' and folderlevel='.$thisfolder['folderlevel'].' and folderid<>'.$thisfolder['folderid'];
			Ebh()->db->query($sqlAllforone);
		}
		
		$sql2 = 'select f.folderid,f.upid,f.displayorder,f.folderlevel from ebh_folders f ';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'displayorder'.$param['compare'].$thisfolder['displayorder'];
		$wherearr[] = 'folderlevel='.$thisfolder['folderlevel'];
		$sql2 .= ' where '.implode(' AND ',$wherearr);
		$sql2 .= ' order by '.$param['order'];
		$sql2 .= ' limit 1';
		$desfolder = Ebh()->db->query($sql2)->row_array();
		if(empty($desfolder))
			return false;
		Ebh()->db->update('ebh_folders',array('displayorder'=>$desfolder['displayorder']),array('folderid'=>$thisfolder['folderid']));
        Ebh()->db->update('ebh_folders',array('displayorder'=>$thisfolder['displayorder']),array('folderid'=>$desfolder['folderid']));
        return true;
	}

    /**
     * 调整课程排序，非关键性操作，不启用事务
     * @param $params
     * @param $crid
     * @return bool
     */
	public function changeOrder($params, $crid) {
        if (!isset($params['pid']) || !isset($params['sid'])) {
            return false;
        }
        $pid = (int) $params['pid'];
        $sid = (int) $params['sid'];
        //移动方式：true-上移，false－下移
        $is_increase = !empty($params['is_increase']) ? true : false;
        //folderid=0时仅重置排序号，使排序号连续
        $folderid = !empty($params['folderid']) ? intval($params['folderid']) : 0;
        $crid = (int) $crid;
        if ($pid < 1 || $crid < 1 || $sid < 0) {
            return false;
        }
        $sql = "SELECT `b`.`displayorder`,`a`.`folderid` FROM `ebh_pay_items` `a` 
                JOIN `ebh_folders` `b` ON `a`.`folderid`=`b`.`folderid` 
                WHERE `a`.`pid`=$pid AND `a`.`sid`=$sid AND `a`.`crid`=$crid 
                GROUP BY `folderid` ORDER BY `b`.`displayorder`,`b`.`folderid` DESC";
        $folders = Ebh()->db->query($sql)->list_array();
        if (empty($folders)) {
            return false;
        }
        $displayorders = array_column($folders, 'displayorder');
        $displayorders = array_unique($displayorders);
        //有重复值，排序号需重置
        $reset = count($folders) > count($displayorders) || $folderid == 0;
        unset($displayorders);
        $folder_key = false;
        if ($reset) {
            foreach ($folders as $key => $folder) {
                $folders[$key]['displayorder'] = $key + 1;
                if ($folder['folderid'] == $folderid) {
                    $folder_key = $key;
                }
            }
        }
        if ($folder_key === false) {
            foreach ($folders as $key => $folder) {
                if ($folder['folderid'] == $folderid) {
                    $folder_key = $key;
                    break;
                }
            }
        }
        if ($is_increase && $folder_key !== false && isset($folders[$folder_key - 1])) {
            //上移
            $change_key = $folder_key - 1;
        } else if (!$is_increase && $folder_key !== false && isset($folders[$folder_key + 1])) {
            //下移
            $change_key = $folder_key + 1;
        }
        if (isset($change_key)) {
            $ex_displayorder = $folders[$change_key]['displayorder'];
            $folders[$change_key]['displayorder'] = $folders[$folder_key]['displayorder'];
            $folders[$folder_key]['displayorder'] = $ex_displayorder;
        }
        if ($reset) {
            //重置全部排序号
            foreach ($folders as $folderitem) {
                Ebh()->db->update('ebh_folders',
                    array('displayorder' => $folderitem['displayorder']),
                    "`folderid`={$folderitem['folderid']}");
            }
            return true;
        }
        if (!isset($change_key)) {
            return false;
        }
        //交换两个排序号
        Ebh()->db->update('ebh_folders',
            array('displayorder' => $folders[$folder_key]['displayorder']),
            "`folderid`=$folderid");
        Ebh()->db->update('ebh_folders',
            array('displayorder' => $folders[$change_key]['displayorder']),
            "`folderid`={$folders[$change_key]['folderid']}");
        return true;
    }
	
	/*
	学生作业答题情况
	*/
	public function getUserFolderExamCredit($param){
		$sql = 'select f.folderid,sum(a.totalscore/e.score) examcredit 
		from ebh_schexamanswers a 
		join ebh_schexams e on a.eid=e.eid 
		join ebh_folders f on f.folderid = e.folderid
		';
		$wherearr[] = 'a.uid='.$param['uid'];
		$wherearr[] = 'f.folderid in ('.$param['folderid'].')';
		$sql .= ' where '.implode(' AND ',$wherearr);
		$sql .= ' group by f.folderid';
		// echo $sql;
		return Ebh()->db->query($sql)->list_array();
		
	}
	
	/*
	课程的作业总数
	*/
	public function getFolderExamCount($param){
		$sql = 'select count(*) count,folderid from ebh_schexams e';
		$wherearr[] = ' e.folderid in ('.$param['folderid'].')';
		$sql .= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by e.folderid';
		return Ebh()->db->query($sql)->list_array();
		
	}
	/*
	课程下的新课件
	*/
	public function getnewcourselistcount($param){
		$sql = 'select count(*) count ,folderid from ebh_roomcourses r 
			join ebh_coursewares c on r.cwid=c.cwid';
		$wherearr[] = ' r.crid='.$param['crid'];
		$wherearr[] = ' c.status=1';
		if(!empty($param['subtimes'])){
			$orstr = '';
			foreach($param['subtimes'] as $folderid=>$subtime){
				if(empty($orstr))
					$orstr = '(folderid ='.$folderid .' and c.truedateline>'.$subtime.')';
				else
					$orstr .= ' or (folderid ='.$folderid .' and c.truedateline>'.$subtime.')';
			}
			$wherearr[] = '(' . $orstr . ')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		
		$sql.= ' group by folderid';
		// echo $sql;
		return Ebh()->db->query($sql)->list_array();
	}
	/**
	* 获取课程列表
	*/
	public function getFolderChapterList($param){
		$sql = 'SELECT f.crid,f.foldername,f.img,f.summary,f.folderpath,f.folderid,f.coursewarenum,f.fprice,f.viewnum,f.grade,f.credit,f.chapterid,c.chapterpath,c.chaptername FROM ebh_folders f LEFT JOIN ebh_schchapters c ON f.chapterid=c.chapterid';
        $wherearr = array();
 
		if(! empty ( $param ['folderid'] )){
			$wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
		}
		if(! empty ( $param ['crid'] )){
			$wherearr [] = ' f.crid = ' . $param ['crid'];
		}
		if(! empty ( $param ['uid'] )){
			$wherearr [] = ' f.uid = ' . $param ['uid'];
		}
		if(! empty ( $param ['status'] )){
			$wherearr [] = ' f.status = ' . $param ['status'];
		}
		if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
			$wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
		}
		if(! empty ( $param ['folderlevel'] )){
			$wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
		}
		if(isset ( $param ['upid'] )){
			$wherearr [] = ' f.upid <> ' . $param ['upid'];
		}
		if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
			$wherearr [] = ' f.coursewarenum  > 0 ';
		}
		if(isset($param['filternum'])){
			$wherearr [] = ' f.coursewarenum > 0';
		}
		if(isset($param['nosubfolder'])){
			$wherearr [] = ' f.folderlevel = 2';
		}
		if(!empty($param['needpower'])){
			$wherearr [] = ' f.power = 0';
		}
		if(isset($param['q']))
			$wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
		$wherearr[] = 'f.del = 0';
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        if(!empty($param['order'])) {
            $sql .= ' ORDER BY '.$param['order'];
        } else {
            $sql .= ' ORDER BY f.displayorder';
        }
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

    /**
     * 服务课程数
     * @param $param
     * @return int
     */
    public function getcountJoinItem($param) {
        $count = 0;
        $sql = 'select COUNT(DISTINCT `a`.`folderid`) count from `ebh_pay_items` `a` JOIN ebh_folders f  ON `a`.`folderid`=`f`.`folderid`';
        $wherearr = array();
        $wherearr[] = '`a`.`status`=0';
        if(! empty ( $param ['folderid'] )){
            $wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
        }
        if(! empty ( $param ['crid'] )){
            $wherearr [] = ' a.crid = ' . $param ['crid'];
        }
        if(! empty ( $param ['status'] )){
            $wherearr [] = ' f.status = ' . $param ['status'];
        }
        if(! empty ( $param ['folderlevel'] )){
            $wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
        }
        if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
            $wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
        }
        if(isset ( $param ['upid'] )){
            $wherearr [] = ' f.upid <> ' . $param ['upid'];
        }
        if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
            $wherearr [] = ' f.coursewarenum  > 0 ';
        }
        if(isset($param['filternum'])){
            $wherearr [] = ' f.coursewarenum > 0';
        }
        if(isset($param['nosubfolder'])){
            $wherearr [] = ' f.folderlevel = 2';
        }
        if(!empty($param['q']))
            $wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        $row = Ebh()->db->query($sql)->row_array();
        if(!empty($row))
            $count = $row['count'];
        return $count;
    }
    /**
     * 获取服务课程列表
     * @param $param
     * @return mixed
     */
	public function getFolderChapterJoinItemList($param) {
        $sql = 'SELECT `i`.`itemid`,f.crid,f.foldername,f.img,f.summary,f.folderpath,f.folderid,f.coursewarenum,f.fprice,f.viewnum,f.grade,f.credit,f.chapterid,c.chapterpath,c.chaptername FROM `ebh_pay_items` `i` JOIN ebh_folders f ON `i`.`folderid`=`f`.`folderid` LEFT JOIN ebh_schchapters c ON f.chapterid=c.chapterid';
        $wherearr = array();
        $wherearr[] = '`i`.`status`=0';
        if (!empty($param['sid_arr'])) {
            $param['sid_arr'][] = 0;
            $wherearr[] = '`i`.`sid` IN('.implode(',', $param['sid_arr']).')';
        }
        if(! empty ( $param ['folderid'] )){
            $wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
        }
        if(! empty ( $param ['crid'] )){
            $wherearr [] = ' `i`.crid = ' . $param ['crid'];
        }
        if(! empty ( $param ['uid'] )){
            $wherearr [] = ' f.uid = ' . $param ['uid'];
        }
        if(! empty ( $param ['status'] )){
            $wherearr [] = ' f.status = ' . $param ['status'];
        }
        if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
            $wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
        }
        if(! empty ( $param ['folderlevel'] )){
            $wherearr [] = ' f.folderlevel <> ' . $param ['folderlevel'];
        }
        if(isset ( $param ['upid'] )){
            $wherearr [] = ' f.upid <> ' . $param ['upid'];
        }
        if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
            $wherearr [] = ' f.coursewarenum  > 0 ';
        }
        if(isset($param['filternum'])){
            $wherearr [] = ' f.coursewarenum > 0';
        }
        if(isset($param['nosubfolder'])){
            $wherearr [] = ' f.folderlevel = 2';
        }
        if(!empty($param['needpower'])){
            $wherearr [] = ' f.power = 0';
        }
        if(!empty($param['q']))
            $wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        $sql .= ' GROUP BY `f`.`folderid`';
        if(!empty($param['order'])) {
            $sql .= ' ORDER BY '.$param['order'];
        } else {
            $sql .= ' ORDER BY f.displayorder,`i`.`itemid` desc';
        }
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
	获取folder
	*/
	function getfolder($crid,$tid = 0){
		$result = array();
		if(!empty($tid)){
			$sql = 'SELECT f.folderid,f.foldername,tf.tid FROM ebh_folders f left join  ebh_teacherfolders tf on f.folderid = tf.folderid  where tf.crid = '.$crid.' and f.folderlevel = 2 and f.power !=2 and tf.tid = '.$tid;
			$myfolderid = array();
			$result_a = Ebh()->db->query($sql)->list_array();
			foreach ($result_a as $row) {
				$myfolderid[] = $row['folderid'];
				$result[] = $row;
			}
		}

		if(!empty($myfolderid)){
			$notin = '('.implode(',', $myfolderid).')';
			$sql = 'SELECT foldername,folderid FROM ebh_folders WHERE crid='.$crid.' AND folderlevel=2 AND power!=2 AND folderid not in '.$notin;
		}else{
			$sql = 'SELECT foldername,folderid FROM ebh_folders WHERE crid='.$crid.' AND folderlevel=2 AND power !=2';
		}
		$result_b = Ebh()->db->query($sql)->list_array();
		foreach ($result_b as $row) {
			$result[] = $row;
		}
		return $result;
	}

	function getfolderbyids($folderids, $setKey = false){
		if(empty($folderids)){
			return false;
		}
		$sql = $sql = 'select f.folderid,f.foldername,f.displayorder,f.img,f.coursewarenum,f.summary,f.grade,f.district,f.upid,f.folderlevel,f.folderpath,f.fprice,f.speaker,f.detail,f.viewnum,f.coursewarelogo,f.power,f.credit,f.creditrule,f.playmode,f.isremind,f.remindmsg,f.remindtime,f.creditmode,f.credittime,f.showmode,f.introduce,f.isschoolfree,f.uid from ebh_folders f where f.folderid in ('.$folderids.')';
		if ($setKey) {
            return Ebh()->db->query($sql)->list_array('folderid');
        }
		return Ebh()->db->query($sql)->list_array();
	}
	/**
	 * 根据crid获取免费课
	 */
	function getfreefolderList($crid){
		if(empty($crid)){
			return false;
		}
		$sql = 'select folderid from ebh_folders where crid ='.$crid.' and (fprice = 0 or isschoolfree=1 )';
		return Ebh()->db->query($sql)->list_array();
	}

    /**
     * 获取本校免费课程
     * @param $folderid_arr
     * @param $crid
     * @return bool
     */
	function getSchoolFreeFolderidList($folderid_arr) {
        if (!is_array($folderid_arr)) {
            return false;
        }
        $folderid_arr = array_filter($folderid_arr, function($folderid) {
           return is_numeric($folderid) && $folderid > 0;
        });
        if (empty($folderid_arr)) {
            return false;
        }
        if (count($folderid_arr) == 1) {
            $folderid = current($folderid_arr);
            return Ebh()->db->query(
                "SELECT `folderid` FROM `ebh_folders` WHERE `folderid`=$folderid AND `isschoolfree`=1")
                ->row_array();
        }
        $folderid_arr_str = implode(',', $folderid_arr);
        return Ebh()->db->query(
            "SELECT `folderid` FROM `ebh_folders` WHERE `folderid` IN($folderid_arr_str) AND `isschoolfree`=1")
            ->list_field();
    }
	
	/*
	免费课程，课程价格0，或者服务项价格0
	*/
	function getPriceZeroList($crid){
		if(empty($crid))
			return array();
		$sql = 'select folderid from ebh_folders where fprice=0 and crid='.$crid;
		$f = Ebh()->db->query($sql)->list_array();
		$sql = 'select folderid from ebh_pay_items where iprice=0 and crid='.$crid;
		$i = Ebh()->db->query($sql)->list_array();
		return array_merge($f,$i);
	}
	/**
	 * 根据crid和grade获取课件的folderid
	 */
	function getfolderListByGrade($crid,$grade){
		if(empty($crid) || empty($grade)){
			return false;
		}
		$sql = 'select folderid from ebh_folders where crid='.$crid.' and grade in('.$grade.')';
		return Ebh()->db->query($sql)->list_array();
	}
	/**
	 * 根据uid和crid获取课件的folderid
	 */
	function getfolderListByuid($crid,$uid){
		if(empty($crid) || empty($uid)){
			return false;
		}
		$sql = 'select folderid from ebh_folders where crid='.$crid.' and grade =0 and uid in('.$uid.')';
		return Ebh()->db->query($sql)->list_array();
	}

    /**
     * 返回课程信息
     * @param $folderid
     * @param int $crid
     * @return bool
     */
	public function getFolderInfo($folderid, $crid = 0) {
        $folderid = (int) $folderid;
        $crid = (int) $crid;
        if ($folderid < 1 || $crid < 0) {
            return false;
        }
        $sql = "SELECT `folderid`,`foldername`,`showmode`,`fprice` FROM `ebh_folders` WHERE `folderid`=$folderid";
        if ($crid > 0) {
            $sql .= " AND `crid`=$crid";
        }
        return Ebh()->db->query($sql)->row_array();
    }

    public function getItemIdsForFolder($crid, $condition = null, $limit = 3000) {
        //服务包ID
        $sql = "SELECT `pid` FROM `ebh_pay_packages` WHERE `crid`=$crid AND `status`=1";
        $pids = Ebh()->db->query($sql)->list_field();
        if (empty($pids)) {
            return false;
        }
        if (!empty($condition['pid'])) {
            $pid = (int) $condition['pid'];
            if (in_array($pid, $pids)) {
                $pids = array($pid);
            } else {
                unset($pid);
            }
        }
        $pid_arr_str = implode(',', $pids);
        //零售分类ID
        $sql = "SELECT `sid` FROM `ebh_pay_sorts` WHERE `pid` IN($pid_arr_str) AND `showbysort`=0";
        $sids = Ebh()->db->query($sql)->list_field();
        $sids[] = 0;
        if (isset($pid) && !empty($condition['sid'])) {
            $sid = (int) $condition['sid'];
            if (in_array($sid, $sids)) {
                $sids = array($sid);
            } else {
                unset($sid);
            }
        }
        $sid_arr_str = implode(',', $sids);

    }
    /**
     * 分层网校课程列表
     * @param $crid
     * @param null $condition
     * @param int $limit
     * @return mixed
     */
    public function getFolderWithItem($crid, $condition = null, $limit = 3000) {
        $crid = intval($crid);
        $fields = array(
            '`a`.`folderid`', '`a`.`itemid`', '`a`.`iprice`', '`a`.`imonth`', '`a`.`iname`', '`a`.`iday`',
            '`a`.`pid`', '`a`.`sid`', '`b`.`foldername`', '`b`.`img`', '`b`.`summary`', '`b`.`folderpath`',
            '`b`.`coursewarenum`', '`b`.`fprice`', '`b`.`viewnum`', '`b`.`chapterid`' , '`b`.`uid`',
            'IFNULL(`e`.`grank`,0) AS `grank`', 'IFNULL(`e`.`prank`,0) AS `prank`' , 'IFNULL(`e`.`srank`,0) AS `srank`'
        );
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`crid`='.$crid,
            '`b`.`del`=0',
            '`b`.`folderlevel`=2',
            '`c`.`status`=1'
        );
        if (!empty($condition['pid'])) {
            $pid = (int) $condition['pid'];
            $wheres[] = '`a`.`pid`='.$pid;
            if (isset($condition['sid']) && $condition['sid']>0) {
                $sid = (int) $condition['sid'];
                $wheres[] = '`a`.`sid`='.$sid;
            }
        }
        if (isset($condition['q'])) {
            $k = trim($condition['q']);
            $wheres[] = '`a`.`iname` LIKE '.Ebh()->db->escape('%'.$k.'%');
        }
        if (isset($condition['itemids']) && $condition['itemids'] != '') {
            $itemis = trim($condition['itemids'], ',');
            $wheres[] = '`a`.`itemid` IN('.$itemis.')';
        }
        if (isset($condition['folderids']) && $condition['folderids'] != '') {
            $folderids = trim($condition['folderids'], ',');
            $wheres[] = '`a`.`folderid` IN('.$folderids.')';
        }
        $sql = 'SELECT '.implode(',', $fields).
            ' FROM `ebh_pay_items` `a` 
             JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid` 
             JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid` 
             LEFT JOIN `ebh_pay_sorts` `d` ON `d`.`sid`=`a`.`sid` 
             LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`a`.`folderid` AND `e`.`crid`=`a`.`crid` 
             WHERE '.implode(' AND ', $wheres).' GROUP BY `a`.`folderid`';
        if (is_array($limit)) {
            $page = !empty($limit['page']) ? intval($limit['page']) : 1;
            $page = max(1, $page);
            $pagesize = !empty($limit['pagesize']) ? intval($limit['pagesize']) : 20;
            $pagesize = max(1, $pagesize);
            $offset = ($page - 1) * $pagesize;
        } else {
            $offset = 0;
            $pagesize = max(1, intval($limit));
        }
        if (isset($sid)) {
            $sql .= " ORDER BY `srank` ASC,`folderid` DESC LIMIT $offset,$pagesize";
        } else if(isset($pid)) {
            $sql .= " ORDER BY `prank` ASC,`folderid` DESC LIMIT $offset,$pagesize";
        } else {
            $sql .= " ORDER BY `grank` ASC ,`folderid` DESC LIMIT $offset,$pagesize";
        }
        $ret = Ebh()->db->query($sql)->list_array();log_message($sql);
        if (!empty($ret)) {
            $chapterids = array_column($ret, 'chapterid');
            $chapterids = array_unique($chapterids);
            $chapterids = array_filter($chapterids, function($chapterid) {
                return $chapterid > 0;
            });
            if (!empty($chapterids)) {
                $chapterids_str = implode(',', $chapterids);
                $sql = "SELECT `chapterid`,`chapterpath`,`chaptername` FROM `ebh_schchapters` WHERE `chapterid` IN($chapterids_str)";
                $chapters = Ebh()->db->query($sql)->list_array('chapterid');
                if (empty($chapters)) {
                    return $ret;
                }
                foreach ($ret as $index => $item) {
                    if ($item['chapterid'] == 0 || !isset($chapters[$item['chapterid']])) {
                        continue;
                    }
                    $ret[$index]['chapterpath'] = $chapters[$item['chapterid']]['chapterpath'];
                    $ret[$index]['chaptername'] = $chapters[$item['chapterid']]['chaptername'];
                }
            }
        }
        return $ret;
    }
	/*
	分成网校课程数量
	*/
	public function getFolderCountWithItem($crid, $condition = null){
        $crid = intval($crid);
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
          //  '`a`.`defind_course`=1',
            '`b`.`crid`='.$crid,
            '`b`.`del`=0',
            '`b`.`folderlevel`=2',
            '`c`.`status`=1'
        );
        if (!empty($condition['pid'])) {
            $pid = (int) $condition['pid'];
            $wheres[] = '`a`.`pid`='.$pid;
            if (isset($condition['sid'])) {
                $sid = (int) $condition['sid'];
                $wheres[] = '`a`.`sid`='.$sid;
            }
        }
        if (isset($condition['q'])) {
            $k = trim($condition['q']);
            $wheres[] = '`a`.`iname` LIKE '.Ebh()->db->escape('%'.$k.'%');
        }
        if (isset($condition['itemids']) && $condition['itemids'] != '') {
            $itemis = trim($condition['itemids'], ',');
            $wheres[] = '`a`.`itemid` IN('.$itemis.')';
        }
        if (isset($condition['folderids']) && $condition['folderids'] != '') {
            $folderids = trim($condition['folderids'], ',');
            $wheres[] = '`a`.`folderid` IN('.$folderids.')';
        }
        $sql = 'SELECT COUNT(DISTINCT `a`.`folderid`) AS `c` 
             FROM `ebh_pay_items` `a` 
             LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid` 
             LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid` 
             LEFT JOIN `ebh_pay_sorts` `d` ON `d`.`sid`=`a`.`sid` 
             WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
	}
	
	/*
	获取folder信息.我的课件,课程信息
	*/
	public function getFolderByCwids($cwids){
		$sql = 'select f.folderid,foldername,cwid,count(cwid) countcw
				from ebh_folders f 
				join ebh_roomcourses rc on f.folderid=rc.folderid 
				where rc.cwid in ('.$cwids.')';
		$sql.= ' group by folderid';
		$sql.= ' order by f.displayorder';
		return Ebh()->db->query($sql)->list_array();
	}

	/**
     * 统计网校下的课程数
     * @param $crid
     * @param int $isschool 网校类型
     * @return bool
     */
    function getCountForRoom($crid, $isschool = 7)
    {
        $crid = (int) $crid;
        if ($isschool == 7) {
            $sql = "SELECT COUNT(1) AS `c` FROM `ebh_folders` `a` 
                    LEFT JOIN `ebh_pay_items` `b` ON `a`.`folderid`=`b`.`folderid` 
                    LEFT JOIN `ebh_pay_packages` `c` ON `b`.`pid`=`c`.`pid`
                    WHERE `c`.`crid`=$crid AND `a`.`del`=0 AND `b`.`status`=0";
        } else {
            $sql = "SELECT COUNT(1) AS `c` FROM `ebh_folders` WHERE `crid`=$crid AND `del`=0";
        }

        $ret = $this->db->query($sql)->row_array();
        if (isset($ret['c'])) {
            return $ret['c'];
        }
        return false;
    }
	
	/*
	点赞数
	*/
	public function zanCount($param){
		$sql = 'select count(*) count ,folderid 
			from ebh_userzan z 
			join ebh_roomcourses rc on z.cwid=rc.cwid 
			join ebh_coursewares cw on cw.cwid=z.cwid';
		$wherearr[] = ' folderid in ('.$param['folderid'].')';
		$wherearr[] = ' z.crid='.$param['crid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'cw.truedateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'cw.truedateline<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by folderid';
		return Ebh()->db->query($sql)->list_array('folderid');
	}
   
    /**
     * 热门课程
     * @param $crid 网校ID
     * @param $num 获取条数
     * @return array
     */
	public function hotList($crid) {
		$folders = Ebh()->cache->get('ebh_folders_'.$crid);
		if($folders === null){
			$fields = array(
		        '`a`.`folderid`','`a`.`foldername`','`a`.`img`','`a`.`viewnum`','`a`.`summary`'
	        );
		    $wheres = array(
		        '`a`.`crid`='.intval($crid),
	            '`a`.`folderlevel`>1',
	            '`a`.`del`=0'
	        );
	        $sql = 'SELECT '.implode(',', $fields).
	            ' FROM `ebh_folders` `a` WHERE '.implode(' AND ', $wheres);
	        $folders = $this->db->query($sql)->list_array();
	        foreach($folders as $key=>$value){
	        	
	        	$sql = 'select tid from ebh_teacherfolders where folderid='.intval($value['folderid']);
	        	$tids = Ebh()->db->query($sql)->list_array();
	        	//$tidStr = '';
	        	$teachers = array();
	            foreach($tids as $val){
	            	//$tidStr .= intval($val['tid']).',';
	            	$sql = 'select realname,nickname,sex,teacherid as tid from ebh_teachers where teacherid='.intval($val['tid']);
	            	$teachers[] = Ebh()->db->query($sql)->row_array();
	            }
	            //$tidStr = substr($tidStr,0,-1);
	            //$sql = 'select realname,nickname,sex,teacherid as tid from ebh_teachers where teacherid in '."($tidStr)";
	            //$teachers = Ebh()->db->query($sql)->list_array();
	            if(!is_array($teachers)){
	            	$teachers = array();
	            }
	            foreach($teachers as $k=>$v){
	            	$teachers[$k]['folderid'] = $value['folderid'];
	            	$sql = 'select face from ebh_users where uid='.intval($v['tid']);
	            	$face = Ebh()->db->query($sql)->row_array();
	            	$teachers[$k]['face'] = $face['face'];
	            }
	            $folders[$key]['teachers'] = $teachers;
	        }
	        Ebh()->cache->set('ebh_folders_'.$crid,$folders,300);
		}
        return empty($folders) ? array() : $folders;
    }

    /**
     * [获取所有课程的ID]
     * @return [array] [folderid 数组]
     */
    public function getAllFolderId($crid){
    	$sql = 'select folderid from ebh_folders where crid='.intval($crid);
    	$folderidList = Ebh()->db->query($sql)->list_array();
    	return $folderidList ? $folderidList : false;
    }

    /**
     * 移动课程改变排序号，当课程的排序值有重复或强制重新排序时排序号从１开始连接排序
     * @param $folderid 课程ID
     * @param $crid 网校ID
     * @param int $step 排序变化值，负数表示优先级提高位数，正数表示优先级降低位数
     * @param int $scope 排序范围参数:0或其他-全局排序，1-服务包内排序，2-服务包分类中排序
     * @param bool $reset 全部从1开始连续排序
     * @return bool
     */
    public function rankCourse($folderid, $crid, $step, $scope = 0, $reset =  false) {
        $step = intval($step);
        if ($step == 0) {
            return false;
        }
        $folderid = intval($folderid);
        $crid = intval($crid);
        $params = array(
            '`a`.`crid`='.$crid,
          //  '`a`.`defind_course`=1',
            '`a`.`folderid`='.$folderid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`c`.`status`=1'
        );
        $sql = 'SELECT `a`.`folderid`,`a`.`pid`,`a`.`sid` FROM `ebh_pay_items` `a`
                LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                WHERE '.implode(' AND ', $params);
        $self = $this->db->query($sql)->row_array();
        if (empty($self)) {
            return false;
        }
        $pid = $self['pid'];
        $sid = $self['sid'];
        $params = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
          //  '`a`.`defind_course`=1',
            '`b`.`del`=0',
            '`c`.`status`=1'
        );
        if ($scope == 2) {
            $params[] = '`a`.`sid`='.$sid;
            $params[] = '`a`.`pid`='.$pid;
            $rankColumn = 'srank';
            //服务包分类中排序
            $sql = 'SELECT `a`.`folderid`,IFNULL(`d`.`srank`,0) AS `rank`,IFNULL(`d`.`folderid`,0) AS `rfolderid`
                    FROM `ebh_pay_items` `a`
                    LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                    LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                    LEFT JOIN `ebh_courseranks` `d` ON `d`.`folderid`=`a`.`folderid` AND `d`.`crid`=`a`.`crid`
                    WHERE '.implode(' AND ', $params).' GROUP BY `a`.`folderid` ORDER BY `rank` ASC,`a`.`itemid` DESC';
        } else if ($scope == 1) {
            //服务包中排序
            $rankColumn = 'prank';
            $params[] = '`a`.`pid`='.$pid;
            $sql = 'SELECT `a`.`folderid`,IFNULL(`d`.`prank`,0) AS `rank`,IFNULL(`d`.`folderid`,0) AS `rfolderid`
                    FROM `ebh_pay_items` `a`
                    LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                    LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                    LEFT JOIN `ebh_courseranks` `d` ON `d`.`folderid`=`a`.`folderid` AND `d`.`crid`=`a`.`crid`
                    WHERE '.implode(' AND ', $params).' GROUP BY `a`.`folderid` ORDER BY `rank` ASC,`a`.`itemid` DESC';
        } else {
            //全局排序
            $rankColumn = 'grank';
            $sql = 'SELECT `a`.`folderid`,IFNULL(`d`.`grank`,0) AS `rank`,IFNULL(`d`.`folderid`,0) AS `rfolderid`
                    FROM `ebh_pay_items` `a`
                    LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                    LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                    LEFT JOIN `ebh_courseranks` `d` ON `d`.`folderid`=`a`.`folderid` AND `d`.`crid`=`a`.`crid`
                    WHERE '.implode(' AND ', $params).' GROUP BY `a`.`folderid` ORDER BY `rank` ASC,`a`.`itemid` DESC';
        }

        $ranks = $this->db->query($sql)->list_array('folderid', 'r');
        $len = count($ranks);
        //获取课程原先的位置号
        $pos = 0;
        foreach ($ranks as $k => $rank) {
            if ($rank['folderid'] == $folderid) {
                break;
            }
            $pos++;
        }
        //目标位置
        $dstPos = $pos + $step;
        $dstPos = max(0, $dstPos);
        $dstPos = min($dstPos, $len - 1);
        if ($dstPos == $pos) {
            //位置无实际变动
            return false;
        }
        //移动课程位置
        $self = $ranks['r'.$folderid];
        unset($ranks['r'.$folderid]);
        $front = array_slice($ranks, 0, $dstPos, true);
        $end = array_diff_key($ranks, $front);
        $front['r'.$folderid] = $self;
        $ranks = array_merge($front, $end);
        unset($front, $end);
        $indexs = array_column($ranks, 'rank');
        $indexs = array_flip($indexs);
        if ($reset || count($indexs) != $len) {
            //强制重新连续编辑编号或排序编号有重复，重新连续编辑编号
            $start = 1;
        } else {
            $ranks = array_slice($ranks, min($dstPos, $pos), abs($dstPos - $pos) + 1, true);
            $indexs = array_column($ranks, 'rank');
            $start = min($indexs);
        }
        //批量更新排序号
        $whenGroup = array();
        $whereGroup = array();
        $insertGroup = array();
        foreach ($ranks as $rankitem) {
            if (empty($rankitem['rfolderid'])) {
                //无排序号
                $grank = $scope != 1 && $scope != 2 ? ($start++) : 0;
                $prank = $scope == 1 ? ($start++) : 0;
                $srank = $scope == 2 ? ($start++) : 0;
                $values = array(
                    $rankitem['folderid'],
                    $crid,
                    $grank,
                    $prank,
                    $srank
                );
                $insertGroup[] = '('.implode(',', $values).')';
                continue;
            }
            $whenGroup[] = ' WHEN '.$rankitem['folderid'].' THEN '.($start++);
            $whereGroup[] = $rankitem['folderid'];
        }
        if (!empty($insertGroup)) {
            Ebh()->db->query('INSERT INTO `ebh_courseranks`(`folderid`,`crid`,`grank`,`prank`,`srank`) VALUES'.implode(',', $insertGroup));
        }
        if (empty($whenGroup)) {
            return false;
        }
        $sql = 'UPDATE `ebh_courseranks` SET `'.$rankColumn.'`=CASE `folderid`'.implode(' ', $whenGroup).' END WHERE `folderid` IN('.implode(',', $whereGroup).') AND `crid`='.$crid;
        unset($rank, $whereGroup, $whenGroup, $insertGroup);
        return Ebh()->db->query($sql, false);
    }

    /**
     * 批量设置课程的排序号，未指定的值设为0,排序号从1开始
     * @param array $ranks 排序参数，二维数组，格式 array(
        folderid => array(
     *      'folderid' => 课程ID
     *      'grank' => 全局排序号
     *      'prank' => 包中的排序号
     *      'srank' => 分类中的排序号
     *    )
     * )
     * @param int $crid 网校ID
     * @return bool
     */
    public function batchRankCourses($ranks = array(), $crid) {
        $crid = intval($crid);
        $params = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`crid`='.$crid,
            '`c`.`status`=1'
        );
        $sql = 'SELECT `a`.`folderid`,`a`.`itemid`,`a`.`pid`,`a`.`sid`,IFNULL(`d`.`grank`,0) AS `grank`,IFNULL(`d`.`prank`,0) AS `prank`,IFNULL(`d`.`srank`,0) AS `srank`,IFNULL(`d`.`folderid`,0) AS `rfolderid`
                FROM `ebh_pay_items` `a`
                JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_courseranks` `d` ON `d`.`folderid`=`a`.`folderid` AND `d`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_pay_sorts` `e` ON `e`.`sid`=`a`.`sid`
                WHERE '.implode(' AND ', $params).' GROUP BY `a`.`folderid`';
        $courses = Ebh()->db->query($sql)->list_array('folderid', 'f');
        //合并设置
        foreach ($ranks as $k => $r) {
            if (!isset($courses['f'.$k])) {
                continue;
            }
            $courses['f'.$k]['grank'] = isset($r['grank']) ? intval($r['grank']) : 0;
            $courses['f'.$k]['prank'] = isset($r['prank']) ? intval($r['prank']) : 0;
            $courses['f'.$k]['srank'] = isset($r['srank']) ? intval($r['srank']) : 0;
            $courses['f'.$k]['reset'] = true;
        }
        unset($ranks);
        $whengs = array();
        $whenps = array();
        $whenss = array();
        $wheres = array();
        $inserts = array();
        //全局排序
        $start = 1;
        $orders = $itemids = $resets = array();
        foreach ($courses as $course) {
            $orders[] = $course['grank'];
            $itemids[] = $course['itemid'];
            $resets[] = isset($course['reset']) ? 1 : 0;
        }
        array_multisort($orders, SORT_ASC, SORT_NUMERIC,
            $resets, SORT_DESC, SORT_NUMERIC,
            $itemids, SORT_DESC, SORT_NUMERIC, $courses);
        foreach ($courses as $k => $cours) {
            $courses[$k]['grank'] = $start++;
        }

        //服务包中排序
        $start = 1;
        $orders = $pids = $itemids = $resets = array();
        foreach ($courses as $course) {
            $orders[] = $course['prank'];
            $pids[] = $course['pid'];
            $itemids[] = $course['itemid'];
            $resets[] = isset($course['reset']) ? 1 : 0;
        }
        array_multisort($pids, SORT_DESC, SORT_NUMERIC,
            $orders, SORT_ASC, SORT_NUMERIC,
            $resets, SORT_DESC, SORT_NUMERIC,
            $itemids, SORT_DESC, SORT_NUMERIC, $courses);
        $pid = -1;
        foreach ($courses as $k => $cours) {
            if ($pid != $cours['pid']) {
                $pid = $cours['pid'];
                $start = 1;
            }
            $courses[$k]['prank'] = $start++;
        }
        //服务包分类中排序
        $pid = $sid = -1;
        $start = 1;
        $pids = $sids = $orders = $itemids = $resets = array();
        foreach ($courses as $course) {
            $orders[] = $course['srank'];
            $pids[] = $course['pid'];
            $sids[] = $course['sid'];
            $itemids[] = $course['itemid'];
            $resets[] = isset($course['reset']) ? 1 : 0;
        }
        array_multisort($pids, SORT_DESC, SORT_NUMERIC,
            $sids, SORT_DESC, SORT_NUMERIC,
            $orders, SORT_ASC, SORT_NUMERIC,
            $resets, SORT_DESC, SORT_NUMERIC,
            $itemids, SORT_DESC, SORT_NUMERIC, $courses);
        foreach ($courses as $k => $cours) {
            if ($pid != $cours['pid'] || $sid != $cours['sid']) {
                $pid = $cours['pid'];
                $sid = $cours['sid'];
                $start = 1;
            }
            $courses[$k]['srank'] = $start++;
        }
        unset($orders, $itemids, $pids, $sid);
        foreach ($courses as $rank) {
            if (empty($rank['rfolderid'])) {
                //增加排序数据
                $values = array($rank['folderid'], $crid, $rank['grank'], $rank['prank'], $rank['srank']);
                $inserts[] = '('.implode(',', $values).')';
                continue;
            }
            $whengs[] = ' WHEN '.$rank['folderid'].' THEN '.$rank['grank'];
            $whenps[] = ' WHEN '.$rank['folderid'].' THEN '.$rank['prank'];
            $whenss[] = ' WHEN '.$rank['folderid'].' THEN '.$rank['srank'];
            $wheres[] = $rank['folderid'];
        }
        if (!empty($inserts)) {
            $sql = 'INSERT INTO `ebh_courseranks`(`folderid`,`crid`,`grank`,`prank`,`srank`) VALUES '.implode(',', $inserts);
            Ebh()->db->query($sql, false);
            unset($inserts);
        }
        if (empty($whengs)) {
            return true;
        }
        $sql = 'UPDATE `ebh_courseranks` SET `grank`=CASE `folderid` '.implode('', $whengs).' END,`prank`=CASE `folderid` '.implode('', $whenps).' END,`srank`=CASE `folderid` '.implode('', $whenss).' END WHERE `folderid` IN('.implode(',', $wheres).') AND `crid`='.$crid;
        unset($wheres, $whengs, $whenps, $whenss);
        return Ebh()->db->query($sql, false);
    }

    /**
     * 根据课程主类、课程子类、课程名称获取课程ID
     * @param array $ranks
     * @param $crid
     * @return array
     */
    public function getFolderids($ranks = array(), $crid) {
        $crid = intval($crid);
        if ($crid < 1 || empty($ranks) || !is_array($ranks)) {
            return array();
        }
        $wheres = array();
        foreach ($ranks as $r) {
            $pname = Ebh()->db->escape_str($r['pname']);
            $sname = Ebh()->db->escape_str($r['sname']);
            $fname = Ebh()->db->escape_str($r['foldername']);
            $wheres[] = '`b`.`pname`=\''.$pname.'\' AND IFNULL(`c`.`sname`,\'\')=\''.$sname.'\' AND `d`.`foldername`=\''.$fname.'\'';
        }
        $sql = 'SELECT `a`.`folderid`,`d`.`foldername`,`b`.`pname`,IFNULL(`c`.`sname`,\'\') AS `sname` FROM `ebh_pay_items` `a` 
                LEFT JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid` 
                LEFT JOIN `ebh_folders` `d` ON `d`.`folderid`=`a`.`folderid` 
                WHERE `a`.`status`=0 AND `d`.`del`=0 AND `b`.`status`=1  AND `a`.`crid`='.$crid.' AND 
                ('.implode(' OR ', $wheres).')';
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 课程排序模板
     * @param $pid 服务包ID
     * @param $sid 服务分类ID
     * @param $crid 网校ID
     * @return array
     */
    public function batchRankTpl($pid, $sid, $crid) {
        $pid = intval($pid);
        $crid = intval($crid);
        $params = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
          //  '`a`.`defind_course`=1',
            '`b`.`del`=0',
            '`b`.`crid`='.$crid,
            '`c`.`status`=1',
            'IFNULL(`e`.`showbysort`,0)=0'
        );
        if ($sid !== NULL && $pid > 0) {
            $params[] = '`a`.`sid`='.$sid;
            $params[] = '`a`.`pid`='.$pid;
        } else if ($pid > 0) {
            $params[] = '`a`.`pid`='.$pid;
        }
        $sql = 'SELECT `b`.`foldername`,`c`.`pname`,IFNULL(`e`.`sname`,\'\') AS `sname`,IFNULL(`d`.`grank`,0) AS `grank`,IFNULL(`d`.`prank`,0) AS `prank`,IFNULL(`d`.`srank`,0) AS `srank`
                FROM `ebh_pay_items` `a`
                LEFT JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                LEFT JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_courseranks` `d` ON `d`.`folderid`=`a`.`folderid` AND `d`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_pay_sorts` `e` ON `e`.`sid`=`a`.`sid`
                WHERE '.implode(' AND ', $params).' GROUP BY `a`.`folderid` ORDER BY `grank` ASC,`a`.`itemid` DESC';
        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 获取指定班级学生课程
     * @param $param
     * @return mixed
     */
    public function getClassStudentFolders($param){
        $sql = 'SELECT distinct f.folderid,f.foldername,f.img ,f.coursewarenum ,f.fprice,f.grade,f.district,f.summary,f.viewnum,f.playmode,f.uid,f.isschoolfree,f.speaker FROM ebh_folders f join ebh_classcourses cc on cc.folderid=f.folderid left join ebh_pay_items pi on pi.folderid = f.folderid';

        if(isset($param['classid'])){
            $wherearr [] = ' cc.classid = '.$param['classid'];
        }

        if(!empty($param['q'])){
            $wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
        }
        if(isset($param['pid']) && $param['pid'] > 0){
            $wherearr [] = ' pi.pid = '.$param['pid'];
        }
        if(isset($param['sid']) && $param['sid'] > 0){
            $wherearr [] = ' pi.sid = '.$param['sid'];
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }

        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by f.displayorder';
        }

        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }

        return Ebh()->db->query($sql)->list_array();

    }


    /**
     * 读取学生已经开通过的课程
     * @param $param
     * @return mixed
     */
    public function getStudentFolders($param){
        $sql = 'SELECT f.folderid,f.foldername,f.img ,f.coursewarenum ,f.fprice,f.grade,f.district,f.summary,f.viewnum,f.playmode,f.uid FROM ebh_folders f join ebh_userpermisions up on up.folderid=f.folderid';

        if(isset($param['uid'])){
            $wherearr [] = ' up.uid = '.$param['uid'];
        }

        $wherearr[] = ' up.startdate <= '.SYSTIME;
        $wherearr[] = ' up.enddate >= '.SYSTIME;

        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }

        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by f.displayorder';
        }

        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }

        return Ebh()->db->query($sql)->list_array();

    }
    /**
     * 读取老师的课程列表
     * @return mixed
     */
    public function getTeacherFolders($param){
        $sql = 'SELECT distinct f.folderid,f.foldername,f.img ,f.coursewarenum ,f.fprice,f.grade,f.district,f.summary,f.viewnum,f.playmode,f.uid,f.isschoolfree,f.speaker FROM ebh_folders f join ebh_teacherfolders tf on tf.folderid=f.folderid left join ebh_pay_items pi on pi.folderid = f.folderid';

        if(isset($param['crid'])){
            $wherearr [] = ' tf.crid = '.$param['crid'];
        }
        if(isset($param['tid'])){
            $wherearr [] = ' tf.tid = '.$param['tid'];
        }

        if(!empty($param['q'])){
            $wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
        }
        if(isset($param['pid']) && $param['pid'] > 0){
            $wherearr [] = ' pi.pid = '.$param['pid'];
        }
        if(isset($param['sid']) && $param['sid'] > 0){
            $wherearr [] = ' pi.sid = '.$param['sid'];
        }

        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }

        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by f.displayorder';
        }

        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }

        return Ebh()->db->query($sql)->list_array();
    }


    public function getFolders($param){
        $sql = 'SELECT distinct f.folderid,f.foldername,f.img ,f.coursewarenum ,f.fprice,f.grade,f.district,f.summary,f.viewnum,f.playmode,f.uid,f.isschoolfree,f.speaker FROM ebh_folders f left join ebh_pay_items pi on pi.folderid = f.folderid';
        $wherearr = array();

        if(! empty ( $param ['folderid'] )){
            $wherearr [] = 'f.folderid IN (' . $param ['folderid'] . ')';
        }
        if(! empty ( $param ['crid'] )){
            $wherearr [] = ' f.crid = ' . $param ['crid'];
        }
        if(! empty ( $param ['uid'] )){
            $wherearr [] = ' f.uid = ' . $param ['uid'];
        }
        if(! empty ( $param ['status'] )){
            $wherearr [] = ' f.status = ' . $param ['status'];
        }
        if(! empty ( $param ['folderids'] )){	//folderid组合以逗号隔开，如3033,3034
            $wherearr [] = ' f.folderid in (' . $param ['folderids'].')';
        }
        if(! empty ( $param ['folderlevel'] )){
            $wherearr [] = ' f.folderlevel = ' . $param ['folderlevel'];
        }
        if(isset ( $param ['upid'] )){
            $wherearr [] = ' f.upid <> ' . $param ['upid'];
        }
        if(! empty ( $param ['coursewarenum '] )){	//过滤课程下课件数为0的课程
            $wherearr [] = ' f.coursewarenum  > 0 ';
        }
        if(isset($param['filternum'])){
            $wherearr [] = ' f.coursewarenum > 0';
        }
        if(isset($param['nosubfolder'])){
            $wherearr [] = ' f.folderlevel = 2';
        }
        if(!empty($param['needpower'])){
            $wherearr [] = ' f.power = 0';
        }
        if(isset($param['isschoolfree'])){
            $wherearr [] = ' f.isschoolfree='.$param['isschoolfree'];
        }
        if(!empty($param['q'])){
            $wherearr [] = 'f.foldername like \'%'.Ebh()->db->escape_str($param['q']).'%\'';
        }
        if(isset($param['pid']) && $param['pid'] >= 0){
            $wherearr [] = ' pi.pid = '.$param['pid'];
        }
        if(isset($param['sid']) && $param['sid'] >= 0){
            $wherearr [] = ' pi.sid = '.$param['sid'];
        }
        $wherearr [] = 'f.del=0';
        $sql .= ' WHERE '.implode(' AND ', $wherearr);
        if(!empty($param['order'])) {
            $sql .= ' ORDER BY '.$param['order'];
        } else {
            $sql .= ' ORDER BY f.displayorder';
        }
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


    /**
     *获取用户已开通的课程
     */
    public function getUserPayFolderList($param = array()) {
        if(empty($param['uid']))
            return FALSE;
        $sql = "select p.pid,p.itemid,p.crid,p.folderid,p.folderid as fid from ebh_userpermisions p join ebh_pay_items i on i.itemid=p.itemid";
        $wherearr = array();
        $wherearr[] = 'p.uid='.$param['uid'];
        if(!empty($param['crid'])) {
            $wherearr[] = 'p.crid='.$param['crid'];
            $wherearr[] = 'i.crid='.$param['crid'];
        }
        if(!empty($param['filterdate'])) {	//过滤已过期
            $wherearr[] = 'p.enddate>'.SYSTIME;
        }
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array();
    }
	/**
     * 统计课程课件数量
     * @param $folderid 课程ID
     * @param $crid 网校ID
     */
    public function statsCourseware($folderid, $crid) {
        $folderid = intval($folderid);
        $crid = intval($crid);
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_roomcourses` `a` LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE `a`.`crid`='.
            $crid.' AND `a`.`folderid`='.$folderid.' AND `b`.`status`=1';
        $c = Ebh()->db->query($sql)->row_array();
        Ebh()->db->update('ebh_folders', array('coursewarenum' => $c['c']), '`folderid`='.$folderid);
    }

    /**
     * 调整数据完整性，folderlevel=1课程丢失，重新加回,调整现存课程的folderpath与upid字段
     * @param $crname 网校名称
     * @param $crid 网校ID
     * @param $uid 网校管理员ID
     * @return bool
     */
    public function initIntact($crname, $crid, $uid) {
        $crid = intval($crid);
        $uid = intval($uid);
        $folderid = $this->addFolder(array(
            'foldername' => $crname,
            'crid' => $crid,
            'uid' => $uid,
            'folderlevel' => 1,
            'folderpath' => '/0/',
            'detail' => ''
        ));
        if (empty($folderid)) {
            return false;
        }
        $sql = 'SELECT `folderpath`,`folderid`,`upid`,`folderlevel` FROM `ebh_folders` WHERE `crid`='.$crid.' AND `folderlevel`>1';
        $folders = Ebh()->db->query($sql)->list_array('folderid');
        if (empty($folders)) {
            return true;
        }
        $whenls = array();
        $whens = array();
        $wheres = array();
        foreach ($folders as $fid => $folder) {
            $whenls[] = ' WHEN '.$fid.' THEN '.($folder['folderlevel'] == 2 ? $folderid : $folder['upid']);
            $path = trim($folder['folderpath'], '/');
            $path = explode('/', $path);
            $path[1] = $folderid;
            $path = '/'.implode('/', $path).'/';
            $whens[] = ' WHEN '.$fid.' THEN \''. $path.'\'';
            $wheres[] = $fid;
        }
        $wheres = implode(',', $wheres);
        $sql = 'UPDATE `ebh_folders` SET `upid`=CASE `folderid` '.implode('',$whenls).
            ' END,`folderpath`=CASE `folderid`'.implode('', $whens).' END WHERE `folderid` IN('.$wheres.')';
        Ebh()->db->query($sql, false);
        return true;
    }

    /**
     * 获取用户已开通的企业选课
     * @param array $param
     * @return mixed
     */
    public function getUserPaySchSourceFolderList($param = array()){
        $sql = 'select s.sourceid,si.itemid,si.folderid,si.price,si.month,si.del,s.crid,s.sourcecrid,s.name,f.folderid,f.foldername,f.img ,f.coursewarenum ,f.fprice,f.grade,f.district,f.summary,f.viewnum,f.playmode,f.uid,f.isschoolfree from ebh_userpermisions p 
              join ebh_schsourceitems si on si.itemid=p.itemid 
              join ebh_schsources s on s.crid=si.crid and s.sourcecrid=si.sourcecrid
            join ebh_folders f on f.folderid=si.folderid';
        $wherearr = array();
        $wherearr[] = 'p.uid='.$param['uid'];
        if(!empty($param['crid'])) {
            $wherearr[] = 'p.crid='.$param['crid'];
            $wherearr[] = 'si.crid='.$param['crid'];
        }
        if(!empty($param['filterdate'])) {	//过滤已过期
            $wherearr[] = 'p.enddate>'.SYSTIME;
        }
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array();
    }
	
	/*
	无效的课程数据列表(没有服务项)
	*/
	public function getUnfitCourseList($param){
		$sql = 'select f.folderid,f.foldername,1 as unfit from ebh_folders f left join ebh_pay_items i on f.folderid=i.folderid';
		$wherearr[] = 'f.crid='.$param['crid'];
		$wherearr[] = 'f.del=0';
		$wherearr[] = 'f.folderlevel=2';
		$wherearr[] = 'i.crid is null';
		if(!empty($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = 'foldername like \'%'.$q.'%\'';
		}
		$sql .= ' WHERE '.implode(' AND ',$wherearr);
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
	无效的课程数量(没有服务项)
	*/
	public function getUnfitCourseCount($param){
		$sql = 'select count(*) count from ebh_folders f left join ebh_pay_items i on f.folderid=i.folderid';
		$wherearr[] = 'f.crid='.$param['crid'];
		$wherearr[] = 'f.del=0';
		$wherearr[] = 'f.folderlevel=2';
		$wherearr[] = 'i.crid is null';
		if(!empty($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = 'foldername like \'%'.$q.'%\'';
		}
		$sql .= ' WHERE '.implode(' AND ',$wherearr);
		$count = Ebh()->db->query($sql)->row_array();
		return $count['count'];
	}

	/**
     * 课程目录列表
     * @param $crid
     * @param $folderid
     * @param int $pageindex
     * @param int $pagesize
     */
    public function getFolderDirectories($crid, $folderid, $pageindex = 1, $pagesize = 20, $q='') {
        $crid = (int) $crid;
        $folderid = (int) $folderid;
        $pageindex = max(1, intval($pageindex));
        $pagesize = max(1, intval($pagesize));
        $offset = ($pageindex - 1) * $pagesize;
        $sql = "SELECT COUNT(1) AS `c` 
                FROM `ebh_roomcourses` `a` 
                JOIN `ebh_coursewares` `b` ON `a`.`cwid`=`b`.`cwid` 
                WHERE `a`.`folderid`=$folderid AND `b`.`status`=1";
        if ($q) {
            $sql .= " AND `b`.`title` LIKE '%".Ebh()->db->escape_str($q)."%'";
        }
        $c = Ebh()->db->query($sql)->row_array();
        if (empty($c['c'])) {
            return false;
        }

        if ($q) {
            $sql = "SELECT `a`.`looktime`,`a`.`cprice`,`a`.`cwid`,`a`.`cwpay`,`b`.`cwurl`,`b`.`viewnum`,`b`.`islive`,`b`.`attachmentnum`,`b`.`logo`,`b`.`summary`,`b`.`title`,`b`.`dateline`,`b`.`reviewnum`,`c`.`coursewarecount`,`c`.`sid`,IFNULL(`c`.`sname`,'其他') AS `sname`,`d`.`username`,`d`.`realname`,`d`.`sex`,`d`.`face`,`d`.`groupid`,IFNULL(`c`.`displayorder`,10000) AS `sdisplayorder` 
                FROM `ebh_roomcourses` `a` 
                JOIN `ebh_coursewares` `b` ON `a`.`cwid`=`b`.`cwid` 
                LEFT JOIN `ebh_sections` `c` ON `a`.`sid`=`c`.`sid` 
                JOIN `ebh_users` `d` ON `b`.`uid`=`d`.`uid`
                LEFT JOIN `ebh_folders` `e` ON `e`.`folderid`=`a`.`folderid`
                WHERE `a`.`folderid`=$folderid AND `b`.`status`=1 AND `b`.`title` LIKE '%".Ebh()->db->escape_str($q)."%' AND `e`.`del`=0
                ORDER BY `sdisplayorder`,`c`.`sid`,`a`.`cdisplayorder`,`b`.`displayorder` ASC,`b`.`cwid` DESC LIMIT $offset,$pagesize";
        } else {
            $sql = "SELECT `a`.`looktime`,`a`.`cprice`,`a`.`cwid`,`a`.`cwpay`,`b`.`cwurl`,`b`.`viewnum`,`b`.`islive`,`b`.`attachmentnum`,`b`.`logo`,`b`.`summary`,`b`.`title`,`b`.`dateline`,`b`.`reviewnum`,`c`.`coursewarecount`,`c`.`sid`,IFNULL(`c`.`sname`,'其他') AS `sname`,`d`.`username`,`d`.`realname`,`d`.`sex`,`d`.`face`,`d`.`groupid`,IFNULL(`c`.`displayorder`,10000) AS `sdisplayorder` 
                FROM `ebh_roomcourses` `a` 
                JOIN `ebh_coursewares` `b` ON `a`.`cwid`=`b`.`cwid` 
                LEFT JOIN `ebh_sections` `c` ON `a`.`sid`=`c`.`sid` 
                JOIN `ebh_users` `d` ON `b`.`uid`=`d`.`uid`
                LEFT JOIN `ebh_folders` `e` ON `e`.`folderid`=`a`.`folderid`
                WHERE `a`.`folderid`=$folderid AND `b`.`status`=1 AND `e`.`del`=0
                ORDER BY `sdisplayorder`,`c`.`sid`,`a`.`cdisplayorder`,`b`.`displayorder` ASC,`b`.`cwid` DESC LIMIT $offset,$pagesize";
        }
        $coursewares = Ebh()->db->query($sql)->list_array();
        //log_message(print_r($coursewares,1));
        $list = array();
        $mediatype = array('flv','mp4','avi','mpeg','mpg','rmvb','rm','mov');
        foreach ($coursewares as $courseware) {
            if (empty($list[$courseware['sid']])) {
                $list[$courseware['sid']] = array();
                $list[$courseware['sid']]['section'] = $courseware['sname'];
                $list[$courseware['sid']]['count'] = 0;
            }

            $arr = explode('.',$courseware['cwurl']);
            $type = $arr[count($arr)-1];
            $isVideotype = in_array($type,$mediatype);

            if (empty($courseware['logo']) && $isVideotype){
                $courseware['logo'] = !empty($courseware['islive']) ?
                    'http://static.ebanhui.com/ebh/tpl/2014/images/livelogo.jpg' :
                    'http://static.ebanhui.com/ebh/tpl/2014/images/defaultcwimggray.png';
            }
            if (!$isVideotype) {
                if (strstr($type,'ppt')){
                    $playimg = 'ppt';
                } elseif (strstr($type,'doc')) {
                    $playimg = 'doc';
                } elseif ($type == 'rar' || $type == 'zip' || $type == '7z'){
                    $playimg = 'rar';
                } elseif ($type == 'mp3'){
                    $playimg = 'mp3';
                } else {
                    $playimg = 'attach';
                }
                $courseware['logo'] = "http://static.ebanhui.com/ebh/tpl/2014/images/$playimg.png";
            }
            $list[$courseware['sid']]['items'][] = $courseware;
            $list[$courseware['sid']]['count']++;
        }
        return array($c['c'], $list,$coursewares);
    }

    /*
	获取课件列表
	*/
    public function getCWByFolderid($param){
        $sql = 'select cw.cwid,rc.folderid,cw.cwurl,cw.title,rc.sid from ebh_coursewares cw join ebh_roomcourses rc on cw.cwid=rc.cwid';
        $wherearr = array();
        $wherearr[] = 'rc.folderid in('.$param['folderid'].')';
        $wherearr[] = 'cw.status=1';
        $wherearr[] = 'cw.ism3u8=1';
        // $wherearr[] = '(right(cw.cwurl,4)=\'.flv\' or right(cw.cwurl,5)=\'.ebhp\')';
        $sql.= ' where '.implode(' AND ',$wherearr);
        if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        }
        else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        // echo $sql.'____________';
        return $this->db->query($sql)->list_array();
    }
    /**
     * @describe:查询网校教师课程关联主类
     * @User:tzq
     * @Date:2017/11/21
     * @param $params
     * @param int $crid 网校id
     * @return array/null 正确返回结果数组、错误返回null
     */
    public function getClass($params){
        $field   = array(
            '`pa`.`pid`',
            '`pa`.`pname`'
        );
        $where[] = '`fo`.`del`=0';
        $where[] = '`it`.`status`=0';
        $where[] = '`pa`.`status`=1';               //服务包状态,1为有效,0为失效
        $where[] = '`pa`.`crid`='.$params['crid'];
        $where[] = '`tf`.`crid`=' . $params['crid'];
        $where[] = '`tf`.`tid`=' . $params['uid'];
        $where[] = '`fo`.`power`<2' ;

//        $where[] = '`tf`.`tid`='.$params['uid'];
        $sql     = 'SELECT ' . implode(',', $field) . '  FROM `ebh_folders` `fo` INNER JOIN `ebh_pay_items` `it` ON 
`fo`.`folderid`=`it`.`folderid` INNER JOIN `ebh_pay_packages` `pa` ON `pa`.`pid`=`it`.`pid` ';
        $sql     .= 'INNER JOIN `ebh_teacherfolders` `tf` ON `tf`.`folderid`= `fo`.`folderid` ';
        $sql     .= ' WHERE ' . implode(' AND ', $where);
        $sql     .= ' GROUP BY `pa`.`pid` ORDER BY NULL';
        //log_message($sql);
        $list = $this->db->query($sql)->list_array();
        return $list;

    }
    /**
     * @describe:通过课程id获取时长，学分
     * @User:tzq
     * @Date:2017/12/1
     * @param string $folderids 课程id多个用,号隔开
     * @param int    $crid      网校id
     * @return  array
     */
    public function getFolderidMsg($params){
        //学分

        $sql = 'SELECT `credit`,`folderid` FROM `ebh_folders` WHERE `folderid` IN('.$params['folderids'].')';
        $credits = $this->db->query($sql)->list_array();
        $credit_arr = array();
        foreach ($credits as $item){
            $credit_arr[$item['folderid']] = $item['credit'];
        }

        $filed = array(
            '`ro`.`folderid`',//学习时长
            '`fo`.`credit` `credit`',//学习时长
            'SUM(`co`.`cwlength`) `cwlength`',//学习时长


        );
        $where = array();
        $where[] = '`ro`.`crid` = '.$params['crid'];
        $where[] = '`ro`.`folderid` IN ('.$params['folderids'].') ';
        $sql = 'SELECT '.implode(',',$filed).' FROM `ebh_roomcourses` `ro` ';//课程课件关联表
        $sql .= 'JOIN `ebh_folders` `fo` ON `fo`.`folderid` = `ro`.`folderid` ';//课件表
        $sql .= 'JOIN `ebh_coursewares` `co` ON `ro`.`cwid` = `co`.`cwid` ';//课件表
        $sql .= 'GROUP BY `ro`.`folderid` ORDER BY NULL';
        $list = $this->db->query($sql)->list_array('folderid');
//        $list_arr = array();
//        foreach ($list as $item){
//            $list_arr[$item['folderid']]['cwlength'] = $item['cwlength'];
//        }

//        $lists = explode(',',$params['folderids']);
//        $ret = array();
//        foreach ($lists as $item) {
//            $key = $item;
//
//
//            if(isset($list[$key])){
//                $ret[$key]['cwlength'] = isset($list_arr[$key]['cwlength'])?$list_arr[$key]['cwlength']:0;//课件时长赋值
//            }
//            if(isset($credit_arr[$key]))
//                $ret[$key]['credit'] = isset($credit_arr[$key])?$credit_arr[$key]:0;//学分赋值
//
//        }
        //log_message(json_encode($ret));
        return $list;

	}

    /**
     * @describe:用课程获取教师的信息
     * @User:tzq
     * @Date:2017/11/22
     * @param string $folderid 课程id多个用,号隔开
     * @return  array/null;
     */
    public function getTeacherHead($params){
        $field = [
            '`u`.`face`',//头像地址
            '`u`.`uid`',//用户uid
            '`u`.`username`',//用户账号
            '`u`.`realname`',//用户真实姓名
            '`u`.`sex`',//性别
            '`te`.`folderid`',//课程id
        ];
        $where[] = '`te`.`folderid` IN ('.$params['folderid'].')';
        $sql  = 'SELECT '.implode(',',$field).' FROM `ebh_teacherfolders` `te` ';//教师与课程关联主表
        $sql  .= 'LEFT JOIN `ebh_users` `u` ON `te`.`tid`=`u`.`uid` ';//关联用户表
        $sql  .= 'WHERE '.implode(' AND ',$where);
        //log_message($sql);
        $teacherList = $this->db->query($sql)->list_array();
        $temp = explode(',',$params['folderid']);
        $lists = array();
        if(is_array($temp)){
            foreach ($temp as $item) {
                $lists[$item] = array();
            }
        }else{
            $lists[$params['folderid']] = array();

        }
        foreach ($teacherList as $v){
            if(empty($v['face'])){
                $v['face'] = $v['sex']==0?'http://static.ebanhui.com/ebh/tpl/default/images/t_man_50_50.jpg':'http://static.ebanhui.com/ebh/tpl/default/images/t_woman_50_50.jpg';
            }
            array_push($lists[$v['folderid']],$v);
        }

        return $lists;
    }

    /**
     * @describe:课程-学生排名
     * @User:tzq
     * @Date:2017/11/22
     * @param $params
     * @param $orderBy 排序
     * 1 积分从高到低
     * 2 积分从低到高
     * 3 学分从高到低
     * 4 学分从低到高
     * 5 学时从高到低
     * 6 学时从低到高
     * @param $type 1 以list数组顺序为主 2 以socre数组为主排序
     * @param int $folderid 课程ic
     * @return  array
     */
    public function courseStudentSort($params){
        $field   = [
            //'`cit`.`cityname` `province`',//用户所在省
            //'`ci`.`cityname` `city`',//用户所在市
            '`u`.`username`',//用户账号
            '`u`.`realname`',//用户真实姓名
            '`u`.`sex`',//用户性别
            '`u`.`face`',//用户头像
            '`u`.`uid`',//用户头像
           # '`ca`.`classid`',//用户班级
            //'`ca`.`path`',//用户班级路径
            '`logs`.`logid`',//用户注册信息id
            'MIN(`logs`.`logid`) `lid`',//取第一记录id即注册id
            '`logs`.`dateline`',//注册时间
            '`logs`.`ip`',//注册ip
        ];
        $where[] = '`us`.`folderid`=' . $params['folderid'];//筛选课程
        //$where[] = '`cr`.`crid`=' . $params['crid'];//筛选当前网校的积分
        $having  = ' HAVING `logs`.`logid`=`lid`';//筛选第一次记录
        //获取学生信息
        $sql = ' SELECT ' . implode(',', $field) . ' FROM `ebh_users` `u` ';//班级关联课程表
        $sql .= 'JOIN `ebh_userpermisions` `us` ON `u`.`uid`=`us`.`uid` ';//学生关联班级表 获取学生uid
//        $sql .= 'LEFT JOIN `ebh_classstudents` `cs` ON `cs`.`uid`=`u`.`uid` ';//学生关联班级表 获取学生uid
//        $sql .= 'LEFT JOIN `ebh_classcourses` `cl` ON `cs`.`classid`=`cl`.`classid` ';//用户表 获取用户信息
//        $sql .= 'JOIN `ebh_folders` `fo` ON `us`.`folderid`=`fo`.`folderid` ' ;
//       # $sql .= 'JOIN `ebh_classes` `ca` ON (`ca`.`classid`=`us`.`classid` AND `ca`.`crid`='.$params['crid'].') ';//班级详情 获取用户班级
        $sql .= 'LEFT JOIN `ebh_loginlogs` logs ON `u`.`uid`=`logs`.`uid` ';//连接注册日志表用户信息
        $sql .= ' WHERE ' . implode(' AND ', $where) . ' ';
        $sql .= 'GROUP BY `u`.`uid` ';
        $sql .= $having;



        $list = $this->db->query($sql)->list_array();//获取数据结果

        $uids = array_column($list,'uid');

        //获取学生该网校的积分
        if ($uids) {//如果数组不为空再查下学分
            $where   = array();
            $where[] = '`toid` IN (' . implode(',', $uids) . ')';
            $where[] = '`crid` = ' . $params['crid'];
            $sql     = 'SELECT `toid`,SUM(`credit`) `credit` FROM  `ebh_creditlogs`';
            $sql     .= ' WHERE ' . implode(' AND ', $where);
            $sql    .= ' GROUP BY `toid` ORDER BY NULL';
            //log_message('积分'.$sql);
            $credits = $this->db->query($sql)->list_array('toid');

            // 获取用户的班级
            $sql = 'SELECT `cl`.`uid`,`cs`.`classname`,`cs`.`path` FROM `ebh_classstudents` `cl` ';
            $sql  .= 'JOIN `ebh_classes` `cs` ON `cs`.`classid`=`cl`.`classid` ';
            $sql  .= 'WHERE `cs`.`crid`='.$params['crid'].' AND `cl`.`uid` IN('.implode(',',$uids).')';
            $classidArr = $this->db->query($sql)->list_array('uid');
        }else{
            return array();
        }
        //查询该课程下的所有课件
        $sql = 'SELECT `cwid` FROM `ebh_roomcourses` WHERE folderid='.$params['folderid'];
        $cwids = $this->db->query($sql)->list_array();

        if(empty($cwids)){
            return array();
        }
        $cwids = array_column($cwids,'cwid');
        //统计学时
        $sql = 'SELECT SUM(`ltime`) `ltime`,`uid` FROM  `ebh_playlogs` WHERE `cwid` IN ('.implode(',',$cwids).') AND `totalflag`=0 GROUP BY `uid` ORDER BY NULL';
       // log_message('学时'.$sql);
        $ltimes = $this->db->query($sql)->list_array('uid');


        //获取学分

        $sql = 'SELECT `uid`,SUM(`score`) `score` FROM `ebh_studycreditlogs` WHERE folderid=' . $params['folderid'] . ' 
GROUP BY uid';

       // log_message('学分：'.$sql);
        $scores = $this->db->query($sql)->list_array('uid');
//        $score_arr = array();
//        foreach ($scores as $score) {//重新组装学分
//            $score_arr[$score['uid']] = $score['score'];
//        }
        //log_message(json_encode($list,JSON_UNESCAPED_UNICODE));
        $logArr     = array_column($list, 'logid');//获取日志表id
        if($classidArr){
        foreach ($classidArr as &$item) {//组装班级名称
            if (!empty($item['path']) && isset($params['school_type']) && $params['school_type'] != 3) {
                $item['path']      = trim($item['path'], '/');
                $item['path']      = preg_replace('/.*\//U', '', $item['path'], 1);//替换多余字符
                $item['classname'] = empty($item['path']) ? $item['classname'] : $item['path'];
            }
         }
        }
        unset($classes);
        //获取地址
        if ($logArr) {

            $field   = [
                '`cit`.`cityname` `province`',//用户所在省
                '`ci`.`cityname` `city`',//用户所在市
                '`logs`.`logid`',//日志表id
            ];
            $where   = array();
            $where[] = ' WHERE `logs`.`logid` IN (' . (is_array($logArr) ? implode(',', $logArr) : $logArr) . ') ';//添加
            $sql     = 'SELECT ' . implode(',', $field) . ' FROM `ebh_loginlogs` logs ';
            $sql     .= 'LEFT JOIN `ebh_cities` `cit` ON `logs`.`parentcode`=`cit`.`citycode` ';//地址表获取省
            $sql     .= 'LEFT JOIN `ebh_cities` `ci` ON `logs`.`citycode`=`ci`.`citycode` ';//获取市
            $sql     .= implode(' AND ', $where);
            $citys   = $this->db->query($sql)->list_array('logid');

//            $address_arr  = array();
//            foreach ($citys as $city) {
//                $address_arr[$city['logid']] = $city;//将地址信息赋给已logid为key的新数组中
//            }
        }
        //将数组拼装在一起
        foreach ($list as &$item){
            $item['ltime']     = isset($ltimes[$item['uid']]) ? $ltimes[$item['uid']]['ltime'] : 0;//处理学习时长
            $item['ltime']     = round($item['ltime'] / 3600, 2);
            $item['score']     = isset($scores[$item['uid']]) ? $scores[$item['uid']]['score'] : 0;//赋值学分
            $item['classname'] = isset($classidArr[$item['uid']]) ? $classidArr[$item['uid']]['classname'] : '';//赋值班级名称
            $item['credit']    = isset($credits[$item['uid']]) ? $credits[$item['uid']]['credit'] : 0;//赋值班级名称
            //$item['credit']                  = $creditArr[$item['uid']];//积分
            $item['province'] = isset($citys[$item['logid']]['province']) ? $citys[$item['logid']]['province'] : '';//赋值省
            $item['city']     = isset($citys[$item['logid']]['city']) ? $citys[$item['logid']]['city'] : '';//赋值市县
        }
        if($params['orderBy']){//排序
           $list =  arraySequence($list,$params['orderBy'][0],$params['orderBy'][1]);
        }
        //log_message(json_encode($list,JSON_UNESCAPED_UNICODE));
        return ['list' => $list];

    }

    /**
     * @describe:获取学生学分，班级，地址，注册时间
     * @User:tzq
     * @Date:2017/11/12
     * @param array $data
     * @param array $list 用户uid为key的数组包含 classid,logid
     * @param int $folderid 课程id
     * @return array
     *
     */
    public function getCoreClass($data)
    {
        $userArr    = $data['list'];
        $classidArr = array_column($userArr, 'classid');//获取班级id
        $classidArr = array_unique($classidArr);//有多个学生可能是一个班级去除重复的数据
        $logArr     = array_column($userArr, 'logid');
        $sql        = ' SELECT `classid`,`classname`,`path` FROME `ebh_classes` WHERE classid IN (' . implode(',',
                $classidArr);
        $class      = $this->db->query($sql)->list_array();//获取班级名称
        $classArr   = array();
        foreach ($class as $item) {
            if (!empty($item['path'])) {
                $item['path']      = trim($item['path'], '/');
                $item['path']      = preg_replace('/\/.*\//U', '', $item['path'], 1);//替换多余字符
                $item['classname'] = empty($item['path']) ? $item['classname'] : $item['path'];
            }
            $classArr[$item['classid']] = $item['classname'];//班级赋值
        }
        //获取地址
        $field   = [
            '`cit`.`cityname` `province`',//用户所在省
            '`ci`.`cityname` `city`',//用户所在市
            '`logs`.`logid`',//日志表id
        ];
        $where[] = ' WHERE `logs`.`logid` IN (' . (is_array($logArr) ? implode(',', $logArr) : $logArr) . ') ';//添加
        $sql     = 'SELECT ' . implode(',', $field) . ' `ebh_loginlogs` logs ';
        $sql     .= 'LEFT JOIN `ebh_cities` `cit` ON `logs`.`parentcode`=`cit`.`citycode` ';//地址表获取省
        $sql     .= 'LEFT JOIN `ebh_cities` `ci` ON `logs`.`citycode`=`ci`.`citycode` ';//获取市
        $citys   = $this->db->query($sql)->list_array();
        $cityArr = array();
        foreach ($citys as $city) {
            $cityArr[$city['logid']] = $city;//将地址信息赋给已logid为key的新数组中
        }
        foreach ($userArr as &$item) {
            $item['classname'] = $classArr[$item['classid']];//班级赋值
            $item['province']  = $cityArr[$item['logid']]['province'];//省赋值
            $item['city']      = $cityArr[$item['logid']]['city'];//市、县赋值
        }
        return $userArr;


    }


    /**
     * @describe:课程-文件统计
     * @User:tzq
     * @Date:2017/11/24
     * @param int $folderid 课程id
     * @return array/null
     */
    public function fileCount($params){
        //要获取的字段
        $field             = [
            '`co`.`title`',//课件标题
            '`co`.`cwurl`',//课件url地址判断课件类型
            '`co`.`liveid`',//是否是直播标识
            'SUM(`co`.`cwlength`) `ltime`',//学习时长
            '`co`.`viewnum`',//课件人气
           // 'COUNT(`re`.`logid`) `reviewnum`',//课件评论次数
            '`ro`.`cwid`',//课件id
            '`fo`.`foldername`',//课程名称
          //  '`fo`.`coursewarenum`',//课件数
        //    '`fo`.`credit`',//课件数

        ];
        /**
         *      $sql = 'select count(*) count ,rc.folderid
        from ebh_reviews r join ebh_roomcourses rc on rc.cwid=r.toid';
         */
        $where[]           = '`fo`.`folderid` = ' . $params['folderid'];
       // $where[]           = '`fo`.`coursewarenum` > 0 ';
        $sql               = 'SELECT ' . implode(',', $field) . ' FROM `ebh_roomcourses` `ro` ';//课程关联课件
        $sql               .= 'INNER JOIN `ebh_coursewares` `co` ON `ro`.`cwid`=`co`.`cwid` ';//关联课件
        $sql               .= 'LEFT JOIN `ebh_folders` `fo` ON `ro`.`folderid`=`fo`.`folderid` ';//关联课程表

        $sql               .= 'WHERE '.implode(' AND ',$where).' ';
        $sql               .= 'GROUP BY cwid ';
        //log_message($sql);
        $cwList            = $this->db->query($sql)->list_array();
        $sql               = 'SELECT `credit`,`coursewarenum` FROM `ebh_folders` WHERE `folderid`=' . $params['folderid'];

        $cwcredit          = $this->db->query($sql)->row_array();//获取课程学分，单独获取避免重复
        //获取学习次数
        $sql        = 'SELECT COUNT(*) as `count` FROM `ebh_playlogs` WHERE `totalflag`=0  AND `folderid`='.$params['folderid'];
        $creditnum  = $this->db->query($sql)->row_array();

        //$data['courseNum'] = isset($cwcredit['coursewarenum']) ? $cwcredit['coursewarenum'] : 0;
        $data['cwcredit']  = isset($cwcredit['credit']) && $cwcredit['credit']>0? $cwcredit['credit'] : 0;//学分
        $data['creditNum'] = isset($creditnum['count'])?$creditnum['count']:0;
        $data['zan']       = 0;//点赞数
        $data['ltime']     = 0;//时长
        $data['reNum']     = 0;//评论数
        $data['maxCredit'] = ['cwNum' => 0, 'title' => ''];//最大学习次数课件
        $data['minCredit'] = ['cwNum' => 0, 'title' => ''];//最小学习次数课件
        $data['zb']        = 0;//直播课件数
        $data['other']     = 0;//其他类型课件数
        $data['mp3']       = 0;//mp3课件数
        $data['flv']       = 0;//flv课件数
        $data['doc']       = 0;//doc课件数
        $data['ppt']       = 0;//ppt课件数
        $data['mp4']       = 0;//mp4课件数
        $data['pdf']       = 0;//pdf课件数

//
//        $temp = array_column($cwList, 'creditNum');//取出所有学习次数
//        $temp = array_unique($temp);
//        if($temp){
//
//        if(count($temp) == 1){
//            //全是0的时候
//            $key = [0,rand(0,count($temp)-1)];
//
//        }else{
//            sort($temp);//升序排列数组
//            $data['minCredit']['cwNum'] = reset($temp);//取数组第一个
//            $data['maxCredit']['cwNum'] = end($temp);//去数组最后一个
//
//        }
//        }else{
//            $data['minCredit']['cwNum'] = 0;
//            $data['minCredit']['title'] = '暂无内容';
//            $data['maxCredit']['cwNum'] = 0;
//            $data['maxCredit']['title'] = '暂无内容';
//
//        }

        foreach ($cwList as $k=>&$item) {

            $data['ltime'] += $item['ltime'];
            $redis = Ebh()->cache;  //redis对象
            $nativeRedis = $redis->getRedis();  //原生redis对象，可以操作redis支持的方法，一般临时的功能才用
            $rviewnum =$nativeRedis->hGet('coursewareviewnum',$item['cwid']);
            if($rviewnum == 0){
                $nativeRedis->hSet('coursewareviewnum',$item['cwid'],$item['viewnum']);
            }else{
                $item['viewnum'] = $rviewnum;
            }
            //$data['reNum'] += $item['reviewnum'];
            //$data['creditNum']+=$item['creditNum'];

            //获取各类型课件数量
            if ($item['liveid'] == 1) {
                $data['zb']++;
                continue;
            }
            if (!empty($item['cwurl'])) {
                $endName = explode('.', $item['cwurl']);
                if (is_array($endName)) {
                    $endName = end($endName);
                    $endName = strtolower($endName);//将字符转小写
                    if ('mp3' == $endName) {
                        $data['mp3']++;//添加mp3课件
                    } elseif ('flv' == $endName) {
                        $data['flv']++;//添加flv课件
                    } elseif ('doc' == $endName) {
                        $data['doc']++;//添加doc课件
                    } elseif ('ppt' == $endName) {
                        $data['ppt']++;//添加ppt课件
                    } elseif ('mp4' == $endName) {
                        $data['mp4']++;//添加mp4课件
                    } elseif ('pdf' == $endName) {
                        $data['pdf']++;//添加pdf课件
                    } else {
                        $data['other']++;//添加其他类型课件
                    }

                } else {
                    $data['other']++;//添加其他类型课件
                }

            } else {
                $data['other']++;//添加其他类型课件
            }


        }
        //处理最大最小人气课件
        $cwList = arraySequence($cwList,'viewnum','SORT_ASC');
         $max_arr = end($cwList);
         $min_arr = reset($cwList);
        $data['maxCredit'] =array(
            'title'=>isset($max_arr['title'])?$max_arr['title']:'暂无内容',
            'cwNum'=>isset($max_arr['viewnum'])?$max_arr['viewnum']:0
        );
        $data['minCredit']= array(
            'title'=>isset($min_arr['title'])?$min_arr['title']:'暂无内容',
            'cwNum'=>isset($min_arr['viewnum'])?$min_arr['viewnum']:0
        );
        $data['ltime'] = getTimeToString($data['ltime']);//将秒数转为字符
        return $data;


    }
    /**
     * @describe:查询网校教师关联课程列表
     * @User:tzq
     * @Date:2017/11/21
     * @param int $pid           课程主类id
     * @param int $sid           课程子类id
     * @param int $curr          当前页码
     * @param int $listRows      每页显示条数
     * @param string $orderBy    排序规则
     * @param string $q          课程名搜索
     * @param int $crid          网校id
     * @param int $uid          教师id
     * @return array/null        错误返回null,正常返回结果数组
     */
    public function teacherCourseList($params){

        $field = [
            '`fo`.`folderid`',//课程id
            '`it`.`iprice` `price`',//课程价格
            '`it`.`pid`',//课程主类id
            '`it`.`imonth`',//课程服务期月
            '`it`.`iday`',//课程服务期天
            '`fo`.`foldername`',//课程服务期天
            '`fo`.`credit` credit',//课程学分
            '`fo`.`img`',//课程封面图片
            '`fo`.`coursewarenum` `number`',//课程封面图片
            '`fo`.`coursewarenum`',//课程封面图片
          //  'COUNT(IF(ISNULL( `uz`.`zid`),0,1)) `fabulous`',//点赞数
        //    ' `fo`.`viewnum` `popularity`',//学习次数
        //    '`fo`.`coursewarenum` `number`',//课件数
          //  'COUNT(IF(ISNULL( `re`.`logid`),0,1)) `comment`',//评论数
          //  'SUM( `co`.`cwlength`) `timeLength`',//课件时长

        ];
        //筛选网校
        $where[] = '`tf`.`crid`=' . $params['crid'];//网校判断
        $where[] = '`fo`.`del`=0';//课程是否删除判断
        $where[] = '`it`.`status`=0';//服务详情是否正常判断
        $where[] = '`pa`.`status`=1';//服务包是否正常判断
        $where[] = '`tf`.`tid`='.$params['uid'];
        if($params['pid'] > 0){//有课程主类筛选
            $where[] = '`it`.`pid`='.$params['pid'];

        }
        if($params['sid'] > 0){//有课程子类筛选
            $where[] = '`it`.`sid`='.$params['sid'];
        }
        $sql = 'SELECT %s FROM `ebh_teacherfolders` `tf` ';//教师网校课程关联表
        $sql .= 'LEFT JOIN `ebh_pay_items` `it` ON `tf`.`folderid` = `it`.`folderid` ';//服务包详情
        $sql .= 'LEFT JOIN `ebh_pay_packages` `pa` ON `it`.`pid`=`pa`.`pid` ';//服务包套餐表
        $sql .= 'LEFT JOIN `ebh_folders` `fo` ON `fo`.`folderid`=`tf`.`folderid` ';//课程详细信息表
      //  $sql .= 'left JOIN `ebh_roomcourses` `ro`  ON `tf`.`folderid`=`ro`.`folderid`  ';//网校，课程，课件关联表
      //  $sql .= 'LEFT JOIN `ebh_coursewares` `co` ON `ro`.`cwid`=`co`.`cwid` ';//课件表
       // $sql .= 'LEFT JOIN `ebh_userzan` `uz` ON `ro`.`cwid`=`uz`.`cwid` ';//点赞表
       // $sql .= 'LEFT JOIN `ebh_playlogs` `pl` ON `ro`.`cwid`=`pl`.`cwid` ';//学习记录表
      //  $sql .= 'LEFT JOIN `ebh_reviews` `re` ON `ro`.`cwid`=`re`.`toid` ';//评论表
        $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY `tf`.`folderid`';//以课程分组

        if($params['curr'] > 0){//处理分页
            $count = '`tf`.`folderid` ';
            $pagesql = sprintf($sql,$count);//组装分页sql
            //log_message('分页语句：'.$pagesql);
            $total = $this->db->query($pagesql)->list_array();
            $total = count($total);
            $page  = getPage($total,$params['listRows']>0?$params['listRows']:20,$params['curr']);
            $start = ($page['curr']-1)*$page['listRows'];//计算开始查询条数
            $sql .= ' LIMIT '.$start.','.$page['listRows'];
        }


        $sql   = sprintf($sql,implode(',',$field));//组装查询语句
        //log_message('查询语句： '.$sql);
        $lists = $this->db->query($sql)->list_array();//课程列表数据

        $folderids = array_column($lists,'folderid');//获取课程id
        if(empty($folderids)){
            $teachs = array();
        }else{
         $teachs    = $this->getTeacherHead(['folderid'=>implode(',',$folderids)]);//获取每个课程的教师信息

        }
        foreach ($lists as &$item){
            $item['teachers'] = !empty($teachs)?$teachs[$item['folderid']]:array();//将教师信息插入数组中
            $item['credit']   = $item['credit']>0?$item['credit']:0;

        }
        //$packs  = $this->getClass($params);
        //log_message(json_encode($packs));
        return array('list'=>$lists,'page'=>isset($page)?$page:array());
    }

    /**
     * @describe:用课件获取点赞数，评论数，课件价格，学习时长
     * @User:tzq
     * @Date:2017/12/2
     * @param string $cwids 课件id多个用,号隔开
     */
    public function getCoursesMsg($cwids){
        $field   = array(
            '`co`.`cwid`',//课件售价
            '`ro`.`cprice` `price`',//课件售价
            '`ro`.`cmonth`',//课件服务期月
            '`ro`.`cday`',//课件服务期天
            '`co`.`viewnum`', //课件人气
            '`co`.`cwlength`', //课件时长
            '`co`.`reviewnum`', //课件评论数
          //  'COUNT(`uz`.`zid`) `zannum`', //课件点赞数
            'COUNT(`pl`.`logid`) `creditnum`', //课件学习次数

        );
        $where   = array();
        $where[] = '`co`.`cwid` IN(' . $cwids . ')';
        $sql     = 'SELECT ' . implode(',', $field) . ' FROM `ebh_coursewares` `co` ';
        $sql     .= 'LEFT JOIN `ebh_roomcourses` `ro` ON `co`.`cwid`=`ro`.`cwid` ';
        $sql     .= 'LEFT JOIN `ebh_playlogs` `pl` ON `co`.`cwid`=`pl`.`cwid`  AND `pl`.`totalflag`=0';
       // $sql     .= 'LEFT JOIN `ebh_userzan` `uz` ON `co`.`cwid`=`uz`.`cwid` ';
        $sql     .= ' WHERE ' . implode(' AND ', $where);
        $sql     .= ' GROUP BY `co`.`cwid` ORDER BY NULL';
        //log_message($sql);
        $cwlist  = $this->db->query($sql)->list_array('cwid');
//
//            $cw_arr = explode(',',$cwids);
//        if(!empty($cwlist)){
//            foreach ($cwlist as $item) {
//               $cw_arr[$item['cwid']] = $item;
//            }
//        }


        return $cwlist;
    }

    /**
     * @describe:用课程id统计时长
     * @User:tzq
     * @Date:2017/12/2
     * @param string $folderids or $cwids  课程id或者课程id 支持多个
     * @param int  $crid 网校id
     * @param array $map 附加条件
     * @return  array
     */
    public function cwlengthCountToFolderid($params){

        //课程id查找
        $where   = array();
        $where[] = ' `ro`.`folderid` IN (' . $params['folderids'] . ')';//课程筛选条件
        $where[] = '`ro`.`crid` = ' . $params['crid'];//网校筛选
        if(isset($params['map'])){ //将附件条件传入条件数组
            foreach ($params as $param) {
                $where[] = $param;
            }
        }
        $field   = array(
            '`ro`.`folderid`',//课程id
            'SUM(`co`.`cwlength`) `count`'//课件时长
        );

        $sql  = 'SELECT ' . implode(',', $field) . ' FROM `ebh_coursewares` `co` JOIN `ebh_roomcourses` `ro` ON `co`.`cwid`=`ro`.`cwid` WHERE ' . implode(' AND ', $where);
        $sql  .= ' GROUP BY `ro`.`folderid` ORDER BY NULL';
        $list = $this->db->query($sql)->list_array('folderid');

        return $list;

    }

}

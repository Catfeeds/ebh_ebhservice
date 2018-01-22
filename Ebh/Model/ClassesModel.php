<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ClassesModel{
    /**
     * 查看指定班级名称是否存在
     * @param $crid
     * @param $className
     * @return mixed
     */
    public function exists($crid,$className,$classId){
        if($classId > 0){
            $where = ' and classid != '.$classId;
            $sql = 'select 1 from ebh_classes where crid='.$crid.' and classname='."'".$className."'".$where.' limit 1';
        }else{
            $sql = 'select 1 from ebh_classes where crid='.$crid.' and classname='."'".$className."'".' limit 1';
        }

        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 读取班级列表
     * @param $param
     * @return mixed
     */
    public function getList($param){
        $sql = 'select c.classid,c.grade,c.classname,c.crid,c.stunum,c.dateline,c.status,c.headteacherid,ifnull((select group_concat(realname) from ebh_users where uid in (select uid from ebh_classteachers where classid=c.classid)),\'\') as teachers,ifnull((select group_concat(uid) from ebh_users where uid in (select uid from ebh_classteachers where classid=c.classid)),\'\') as teacherids from ebh_classes c';
        if(!empty($param['crid'])){
            $wherearr[] = 'c.crid='.$param['crid'];
        }
        if(!empty($param['headteacherid'])){
            $wherearr[] = 'c.headteacherid='.$param['headteacherid'];
        }
        if(!empty($param['q'])){
            $wherearr[] = ' c.classname like \'%' . Ebh()->db->escape_str($param['q']) . '%\'';
        }
        if (isset($param['roomType']) && $param['roomType'] == 'edu') {
            $wherearr[] = 'c.category=0';
        }
        if (isset($param['classids'])) {
            $wherearr[] = 'c.classid in('.implode(',', $param['classids']).')';
        }

        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by c.classid asc';
        }

        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 读取班级详情
     * @param $groupid
     * @return mixed
     */
    public function getDetail($classid){
        $sql = 'select c.classid,c.classname,c.crid,c.stunum,c.dateline,c.status,c.headteacherid from ebh_classes c where c.classid='.$classid;
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取数量
     * @param $param
     * @return mixed
     */
    public function getCount($param){
        $sql = 'select count(c.classid) as count from ebh_classes c';
        if(!empty($param['crid'])){
            $wherearr[] = 'c.crid='.$param['crid'];
        }
        if(!empty($param['q'])){
            $wherearr[] = ' c.classname like \'%' . Ebh()->db->escape_str($param['q']) . '%\'';
        }
        if (isset($param['roomType']) && $param['roomType'] == 'edu') {
            $wherearr[] = 'c.category=0';
        }
        if (isset($param['classids'])) {
            $wherearr[] = 'c.classid in('.implode(',', $param['classids']).')';
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }
        $res = Ebh()->db->query($sql)->row_array();
        return $res['count'];

    }

    /**
     * 修改班级信息
     * @param $tid
     * @param $param
     * @return mixed
     */
    public function editClasses($classid,$param){
        return Ebh()->db->update('ebh_classes',$param,array('classid'=>$classid));
    }

    /**
     * 添加班级
     * @param $param
     * @return int
     */
    public function addClasses($param){
        if(empty($param)){
            return 0;
        }
        $param['dateline'] = time();
        return Ebh()->db->insert('ebh_classes',$param);
    }


    /**
     * 获取网校下删选后的班级数量
     */
    public function getSchoolClassCount($crid){
        if(empty($crid)){
            return false;
        }
        $sql = 'select count(c.classid) as count from ebh_classes c '.
            'where c.crid='.intval($crid).' and c.`status`=0 ';
        $res =  Ebh()->db->query($sql)->row_array();
        return $res['count'];
    }


    /**
     * 删除班级
     * @param $param
     * @return int
     */
    public function del($param){
        if(empty($param)){
            return 0;
        }
        return Ebh()->db->delete('ebh_classes',$param);
    }


    /**
     *获取一个班级的老师
     *
     */
    public function getClassTeacherByClassid($classid = 0){
        $sql = 'select uid,classid,folderid from ebh_classteachers where classid = '.$classid;
        return Ebh()->db->query($sql)->list_array();
    }

    /*
	选择班级的任课教师
	@param array $param  classid,tids
	*/
    public function setTeachers($param){
        if(!empty($param['classid'])){
            $wherearr['classid'] = $param['classid'];
            //return $wherearr;
            Ebh()->db->delete('ebh_classteachers',$wherearr);
        }
        foreach($param['tids'] as $id){
            if (!empty($id))
            {
                $ctarr = array('uid'=>$id,'classid'=>$param['classid']);
                Ebh()->db->insert('ebh_classteachers',$ctarr);
            }
        }
        return true;
    }

    /**
     * @param $param classid headteacherid
     * @return bool
     */
    public function setHeadTeacherId($param){
        if(empty($param['classid'])){
            return false;
        }

        return Ebh()->db->update('ebh_classes',array('headteacherid'=>$param['headteacherid']),array('classid'=>$param['classid']));
    }

    /*
	添加学生到classstudent表
	@param array $param crid classid uid
	*/
    public function addClassStudent($param){
        if (!isset($param['uid']) || !isset($param['classid']) || !isset($param['crid'])) {
            return false;
        }
        $wheres = array(
            '`a`.`uid`='.$param['uid'],
            '`b`.`crid`='.$param['crid'],
            '`b`.`status`=0'
        );
        $sql = 'SELECT `a`.`classid` FROM `ebh_classstudents` `a` JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` WHERE '.implode(' AND ', $wheres);
        $exists = Ebh()->db->query($sql)->row_array();
        if (!empty($exists['classid'])) {
            return true;
        }
        $setarr['uid'] = $param['uid'];
        $setarr['classid'] = $param['classid'];
        Ebh()->db->update('ebh_classes',array(),array('classid'=>$param['classid']),array('stunum'=>'stunum+1'));
        Ebh()->db->update('ebh_classrooms',array(),array('crid'=>$param['crid']),array('stunum'=>'stunum+1'));
        return Ebh()->db->insert('ebh_classstudents',$setarr);
    }


    /*
	删除班级学生
	@param array $param crid classid uid
	*/
    public function deleteStudent($param){
        $affected_rows = 0;
        Ebh()->db->begin_trans();
        if(!empty($param['classid']) && !empty($param['uid'])){
            $classarr['classid'] = $param['classid'];
            $classarr['uid'] = $param['uid'];
            Ebh()->db->update('ebh_classes',array(),array('classid'=>$param['classid']),array('stunum'=>'stunum-1'));
            $affected_rows = Ebh()->db->delete('ebh_classstudents',$classarr);
        }
        if(!empty($param['crid']) && !empty($param['uid'])){
            $affected_rows += Ebh()->db->query('DELETE FROM `ebh_classstudents` WHERE `uid`='.$param['uid'].' AND EXISTS (SELECT `classid` FROM `ebh_classes` WHERE `classid`=`ebh_classstudents`.`classid` AND `crid`='.$param['crid'].')', false);
            $roomarr['crid'] = $param['crid'];
            $roomarr['uid'] = $param['uid'];
            Ebh()->db->update('ebh_classrooms',array(),array('crid'=>$param['crid']),array('stunum'=>'stunum-'.$affected_rows));
            Ebh()->db->delete('ebh_roomusers',$roomarr);
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
     * 获取班级课程统计条数
     * @param $crid
     * @param array $param
     * @return int
     */
    public function classFolderReportCount($crid,$param = array()){
        $crid = intval($crid);
        if($crid <= 0){
            return 0;
        }

        if(!empty($param['starttime'])){
            $startPlaylogsSql = ' and startdate >='.$param['starttime'];
        }

        if(!empty($param['endtime'])){
            $endPlaylogsSql = ' and startdate <='.$param['endtime'];

        }
        $sql = "select count(c.classid) as count
                from ebh_classes c
                join ebh_classcourses cc on c.classid=cc.classid
                join ebh_folders f on (f.folderid=cc.folderid)";

        $wherearr[] = 'c.crid='.$crid;
        if (isset($param['lft']) && isset($param['rgt'])) {
            $wherearr[] = 'c.lft>='.$param['lft'];
            $wherearr[] = 'c.rgt<='.$param['rgt'];
        } else if(!empty($param['classid'])){
            if (!empty($param['isenterprise'])) {
                $root = $this->getDept($param['classid'], $crid);
                if (empty($root)) {
                    return array();
                }
                $wherearr[] = 'c.lft>='.$root['lft'];
                $wherearr[] = 'c.rgt<='.$root['rgt'];
            } else {
                $wherearr[] = 'c.classid='.$param['classid'];
            }
        } else if (!empty($param['classids'])) {
            $wherearr[] = 'c.classid in('.implode(',', $param['classids']).')';
        }
        if (!empty($param['isenterprise'])) {
            $wherearr[] = 'c.category=0';
        }
        if(!empty($param['folderid'])){
            $wherearr[] = 'f.folderid='.$param['folderid'];
        }

        if (!empty($param['q'])){
            $wherearr[] = ' (c.classname like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or f.foldername like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }

        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }


    /**
     * 获取班级课程统计
     * @param $crid
     * @param array $param
     * @return array
     */
    public function classFolderReport($crid,$param = array()){
        $crid = intval($crid);
        if($crid <= 0){
            return array();
        }
        $startPlaylogsSql = $endPlaylogsSql = '';
        if(!empty($param['starttime'])){
            $startPlaylogsSql = ' and pl.startdate >='.$param['starttime'];


        }

        if(!empty($param['endtime'])){
            $endPlaylogsSql = ' and pl.startdate <='.$param['endtime'];

        }
        $sql = "select c.classid,c.classname,c.stunum,f.folderid,f.foldername,".(!empty($param['isenterprise']) ? 'c.path,' : '')."
                (select count(distinct(pl.uid)) from ebh_playlogs pl left join ebh_classstudents cs on (cs.uid=pl.uid) where pl.folderid=f.folderid and pl.totalflag=0 and cs.classid=c.classid {$startPlaylogsSql} {$endPlaylogsSql} ) as peoplenum,
                (select count(pl.uid) from ebh_playlogs pl left join ebh_classstudents cs on (cs.uid=pl.uid)  where pl.folderid=f.folderid and pl.totalflag=0 and cs.classid=c.classid {$startPlaylogsSql} {$endPlaylogsSql}) as studynum,
                ifnull((select sum(pl.ltime) from ebh_playlogs pl left join ebh_classstudents cs on (cs.uid=pl.uid) where pl.folderid=f.folderid and pl.totalflag=0 and cs.classid=c.classid {$startPlaylogsSql} {$endPlaylogsSql}) ,0)as studytime,
                ifnull((select group_concat(t.realname) from ebh_teacherfolders tf left join ebh_teachers t on(tf.tid=t.teacherid) where tf.folderid=f.folderid),'') as teachername
                from ebh_classes c
                join ebh_classcourses cc on c.classid=cc.classid
                join ebh_folders f on (cc.folderid=f.folderid)";

        $wherearr[] = 'c.crid='.$crid;
        if (isset($param['lft']) && isset($param['rgt'])) {
            $wherearr[] = 'c.lft>='.$param['lft'];
            $wherearr[] = 'c.rgt<='.$param['rgt'];
        } else if(!empty($param['classid'])){
            if (!empty($param['isenterprise'])) {
                $root = $this->getDept($param['classid'], $crid);
                if (empty($root)) {
                    return array();
                }
                $wherearr[] = 'c.lft>='.$root['lft'];
                $wherearr[] = 'c.rgt<='.$root['rgt'];
            } else {
                $wherearr[] = 'c.classid='.$param['classid'];
            }
        } else if (!empty($param['classids'])) {
            $wherearr[] = 'c.classid in('.implode(',',  $param['classids']).')';
        }
        if (!empty($param['isenterprise'])) {
            $wherearr[] = 'c.category=0';
        }

        if(!empty($param['folderid'])){
            $wherearr[] = 'f.folderid='.$param['folderid'];
        }

        if (!empty($param['q'])){
            $wherearr[] = ' (c.classname like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or f.foldername like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }

        return Ebh()->db->query($sql)->list_array();


    }

    /**
     * 统计网校的有效班级数
     * @param $crid
     * @return bool
     */
    public function getCountForRoom($crid)
    {
        $crid = (int) $crid;
        $sql = "SELECT COUNT(1) AS `c` FROM `ebh_classes` WHERE `crid`=$crid AND `status`=0";
        $ret = Ebh()->db->query($sql)->row_array();
        if (isset($ret['c'])) {
            return $ret['c'];
        }
        return false;
    }

    /**
     * 添加部门
     * @param int $superiorId 上级部门ID
     * @param string $deptname 部门名称
     * @param string $code 部门编号
     * @param int $crid 所属网校ID
     * @param int $displayorder 同级部门优先级，降序
     * @return bool
     */
    public function addDeptment($superiorId, $deptname, $code, $crid, $displayorder = 0) {
        Ebh()->db->set_con(0);
        $crid = intval($crid);
        $superiorId = intval($superiorId);
        $superior = Ebh()->db->query(
            'SELECT `classid`,`lft`,`rgt`,`path`,`stunum` FROM `ebh_classes` WHERE `classid`='.$superiorId.' AND `crid`='.$crid)
            ->row_array();
        if (empty($superior)) {
            return false;
        }
        $wheres = array(
            '`crid`='.$crid
        );
        if ($code == '') {
            $wheres[] = '`superior`='.$superiorId;
            $wheres[] = '`classname`='.Ebh()->db->escape($deptname);
        } else {
            $wheres[] = '(`classname`='.Ebh()->db->escape($deptname).' AND `superior`='.$superiorId.' OR `code`='.Ebh()->db->escape($code).')';
        }
        $brothers = Ebh()->db->query('SELECT `classid`,`classname`,`code`,`superior` FROM `ebh_classes` WHERE '.implode(' AND ', $wheres))->list_array();
        if (!empty($brothers)) {
            //部门名称/部门编号不能重复
            foreach ($brothers as $brother) {
                //部门编号全网校唯一
                if ($code == $brother['code']) {
                    return -2;
                }
                //部门名称同一级唯一
                if ($brother['classname'] == $deptname) {
                    return -1;
                }
            }
        }
        if ($code == '') {
            $code = $this->getNextDeptCode($crid);
        }

        $lft = intval($superior['rgt']);
        $rgt = $lft + 1;
        $params = array(
            'classname' => $deptname,
            'category' => 0,
            'crid' => $crid,
            'superior' => $superiorId,
            'displayorder' => intval($displayorder),
            'lft' => $lft,
            'rgt' => $rgt,
            'code' => $code,
            'dateline' => SYSTIME,
            'path' => $superior['path'].'/'.$deptname
        );
        Ebh()->db->begin_trans();
        Ebh()->db->query('UPDATE `ebh_classes` SET `lft`=`lft`+2 WHERE `lft`>='.$lft.' AND `crid`='.$crid, false);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->query('UPDATE `ebh_classes` SET `rgt`=`rgt`+2 WHERE `rgt`>='.$lft.' AND `crid`='.$crid, false);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        $newId = Ebh()->db->insert('ebh_classes', $params);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return $newId;
    }

    /**
     * 添加顶级部门
     * @param string $name 部门名称
     * @param int $rgt 右值
     * @param int $crid 网校ID
     * @return mixed
     */
    public function addRoot($name, $rgt, $crid) {
        $sets = array();
        $sets['classname'] = $name;
        $sets['lft'] = 1;
        $sets['rgt'] = $rgt;
        $sets['path'] = '/'.$name;
        $sets['category'] = 1;
        $sets['superior'] = 0;
        $sets['dateline'] = SYSTIME;
        $sets['code'] = 0;
        $sets['crid'] = $crid;
        return Ebh()->db->insert('ebh_classes', $sets);
    }

    /**
     * 修改部门
     * @param int $classid 部门ID
     * @param int $crid 所属网校ID
     * @param $params
     * @return bool
     */
    public function updateDeptment($classid, $crid, $params) {
        $crid = intval($crid);
        $classid = intval($classid);
        $superior = Ebh()->db->query(
            'SELECT `classname`,`classid`,`lft`,`rgt`,`superior`,`path` FROM `ebh_classes` WHERE `classid`='.$classid.' AND `crid`='.$crid)
            ->row_array();
        if (empty($superior)) {
            return false;
        }
        if (isset($params['classname']) || isset($params['code'])) {
            $brothers = Ebh()->db->query('SELECT `classid`,`classname`,`code`,`superior` FROM `ebh_classes` WHERE `crid`='.$crid)->list_array('classid');
            if (!empty($brothers)) {
                unset($brothers[$classid]);
                foreach ($brothers as $brother) {
                    if ($brother['superior'] == $superior['superior'] && $brother['classname'] == $params['classname']) {
                        return -1;
                    }
                    if ($brother['code'] == $params['code']) {
                        return -2;
                    }
                }
            }
        }
        $setArr = array();
        $root_path = '';
        if (!empty($params['classname']) && $params['classname'] != $superior['classname']) {
            $setArr['classname'] = $params['classname'];
            $pathInfo = explode('/', trim($superior['path'], '/'));
            $maxIndex = count($pathInfo) - 1;
            $pathInfo[$maxIndex] = $setArr['classname'];
            $root_path = $setArr['path'] = '/'.implode('/', $pathInfo);
        }
        if (isset($params['displayorder'])) {
            $setArr['displayorder'] = intval($params['displayorder']);
        }
        if (isset($params['code'])) {
            $setArr['code'] = $params['code'];
        }
        if (empty($root_path)) {
            //部门名称未更改，只需修改本部门数据
            return Ebh()->db->update('ebh_classes', $setArr, '`classid`='.$classid.' AND `crid`='.$crid);
        }
        //查询下级部门
        $children = Ebh()->db->query(
            'SELECT `classid`,`classname`,`superior`,`path` FROM `ebh_classes` WHERE `crid`='.
            $crid.' AND `lft`>'.$superior['lft'].' AND `rgt`<'.$superior['rgt'].' ORDER BY `lft` ASC')
            ->list_array('classid');
        if (empty($children)) {
            //不存在子部门，只需修改本部门数据
            return Ebh()->db->update('ebh_classes', $setArr, '`classid`='.$classid.' AND `crid`='.$crid);
        }
        //更新所有下级部门path
        $upsql = array();
        foreach ($children as $deptid => $child) {
            if ($child['superior'] == $superior['classid']) {
                $children[$deptid]['path'] = $root_path.'/'.$child['classname'];
                $upsql[] = 'WHEN '.$deptid.' THEN '.Ebh()->db->escape($root_path.'/'.$child['classname']);
                continue;
            }
            if (isset($children[$child['superior']])) {
                $children[$deptid]['path'] = $children[$child['superior']]['path'].'/'.$child['classname'];
                $upsql[] = 'WHEN '.$deptid.' THEN '.Ebh()->db->escape($children[$child['superior']]['path'].'/'.$child['classname']);
            }
        }
        $deptids = array_keys($children);
        Ebh()->db->begin_trans();
        $affected_rows = Ebh()->db->update('ebh_classes', $setArr, '`classid`='.$classid.' AND `crid`='.$crid);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        $sql = 'UPDATE `ebh_classes` SET `path`=CASE `classid` '.implode(' ', $upsql).' END WHERE `classid` IN('.implode(',', $deptids).') AND `crid`='.$crid;
        Ebh()->db->query($sql, false);
        $affected_rows += Ebh()->db->affected_rows();
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return $affected_rows;
    }

    /**
     * 删除部门
     * @param $classid
     * @param $crid
     */
    public function removeDeptment($classid, $crid) {
        $crid = intval($crid);
        $classid = intval($classid);
        $dept = $this->getDept($classid, $crid);
        if (empty($dept)) {
            return false;
        }
        $sql = 'SELECT `c`.`uid` FROM `ebh_classteachers` `a` LEFT JOIN `ebh_classes` `b` ON `a`.`classid`=`b`.`classid`
            LEFT JOIN `ebh_users` `c` ON `a`.`uid`=`c`.`uid` WHERE `b`.`crid`='.$crid.' AND IFNULL(`c`.`uid`, 0)>0'.
            ' AND `b`.`lft`>='.$dept['lft'].' AND `b`.`rgt`<='.$dept['rgt'].' LIMIT 1';
        $user = Ebh()->db->query($sql)->row_array();
        if (!empty($user)) {
            //部门下存在讲师，禁止删除
            return -100;
        }

        $sql = 'SELECT `ru`.`uid` 
			FROM ebh_roomusers ru
			LEFT JOIN ebh_users u ON ru.uid = u.uid
			JOIN ebh_members m on u.uid=m.memberid
			LEFT JOIN ebh_classstudents st ON u.uid=st.uid
			LEFT JOIN ebh_classes cl ON st.classid = cl.classid';

        $wherearr[]= 'ru.crid = '.$crid;
        $wherearr[]= 'cl.crid = '.$crid;
        $wherearr[]= 'cl.status = 0';
        $wherearr[]= 'cl.classid = '.$classid;
        $wherearr[] = 'cl.lft>='.$dept['lft'];
        $wherearr[] = 'cl.rgt<='.$dept['rgt'];

        $sql .= ' WHERE '.implode(' AND ', $wherearr).' LIMIT 1';
        $user = Ebh()->db->query($sql)->row_array();
        if (!empty($user)) {
            //部门下存在员工，禁止删除
            return -101;
        }
        $where = array(
            '`crid`='.$crid,
            '`lft`>='.$dept['lft'],
            '`rgt`<='.$dept['rgt']
        );
        $where = implode(' AND ', $where);
        Ebh()->db->begin_trans();
        $dc = Ebh()->db->delete('ebh_classes', $where);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->query('UPDATE `ebh_classes` SET `lft`=`lft`-2 WHERE `crid`='.$crid.' AND `lft`>'.$dept['rgt']);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->query('UPDATE `ebh_classes` SET `rgt`=`rgt`-2 WHERE `crid`='.$crid.' AND `rgt`>'.$dept['rgt']);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return $dc;
    }

    /**
     * 初始化部门，默认顶级部门名为网校名
     * @param $crid 网校ID
     * @param $crname 网校名称
     * @return mixed
     */
    public function initDeptment($crid, $crname) {
        $crid = intval($crid);
        $whereArr = array(
            '`crid`='.$crid,
            '`category`=1',
            '`superior`=0'
        );
        $sql = 'SELECT `classid` FROM `ebh_classes` WHERE '.implode(' AND ', $whereArr).' LIMIT 1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['classid'];
        }
        $params = array(
            'crid' => $crid,
            'classname' => $crname,
            'category' => 1,
            'lft' => 1,
            'rgt' => 2,
            'dateline' => SYSTIME,
            'path' => '/'.$crname
        );
        $rootId = Ebh()->db->insert('ebh_classes', $params);
        if ($rootId > 0) {
            Ebh()->db->update('ebh_classes', array('superior' => $rootId), '`crid`='.$crid.' AND `category`=0');
        }
        return $rootId;
    }

    /**
     * 部门列表
     * @param $crid
     * @param bool $setKey
     * @return mixed
     */
    public function getDeptmentTree($crid, $setKey = false) {
        Ebh()->db->set_con(0);
        $crid = intval($crid);
        $whereArr = array(
            '`crid`='.$crid,
            '`status`=0'
        );
        $sql = 'SELECT `classid`,`classname`,`stunum`,`category`,`superior`,`code`,`lft`,`rgt`,`displayorder` FROM `ebh_classes` WHERE '.implode(' AND ', $whereArr).' ORDER BY `lft` ASC';
        return Ebh()->db->query($sql)->list_array($setKey ? 'classid' : '');
    }

    /**
     * 班级老师列表
     * @param $classids 班级ID
     * @param int $uid 老师ID
     */
    public function getTeachers($classids, $uid = 0) {
        if (is_array($classids)) {
            $classids = array_map('intval', $classids);
        } else {
            $classids = array(intval($classids));
        }
        $fields = array(
            '`a`.`uid`',
            '`a`.`classid`',
            '`b`.`username`',
            '`b`.`realname`',
            '`b`.`sex`',
            '`b`.`groupid`',
            '`b`.`face`'
        );
        $wheres = array(
            '`a`.`classid` IN('.implode(',', $classids).')'
        );
        if ($uid > 0) {
            $wheres[] = '`a`.`uid`='.$uid;
        }
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_classteachers` `a` JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid`'.
            ' WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 部门信息
     * @param $classid 部门ID
     * @param $crid 网校ID
     * @return mixed
     */
    public function getDept($classid, $crid) {
        $classid = intval($classid);
        $crid = intval($crid);
        $sql = 'SELECT `classid`,`classname`,`lft`,`rgt`,`superior`,`category` FROM `ebh_classes` WHERE `classid`='.$classid.' AND `crid`='.$crid;
        return Ebh()->db->query($sql)->row_array();
    }

    /*
     * 讲师所在部门
     * @param $crid 网校ID
     * @param $uid 老师ID
    */
    public function getTeacherDepts($crid,$uid){
        if(empty($crid) || empty($uid)){
            return FALSE;
        }
        $sql = 'select c.classid,uid,c.lft,c.rgt from ebh_classteachers ct 
			join ebh_classes c on ct.classid=c.classid
				where crid='.$crid.' and uid='.$uid;
        return Ebh()->db->query($sql)->list_array('classid');
    }

    /*
     * 部门课程
     * @param $classid 部门ID
    */
    public function getDeptCourse($classid,$crid){
        if(empty($crid) || empty($classid)){
            return FALSE;
        }
        $sql = 'select folderid from ebh_classcourses cc
				join ebh_classes c on cc.classid=c.classid
				where c.classid='.$classid.' and crid='.$crid;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 判断是否终级部门
     * @param $classid
     */
    public function isLastDept($classid) {
        $sql = 'SELECT `classid` FROM `ebh_classes` WHERE `superior`='.intval($classid);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret['classid'])) {
            return false;
        }
        return true;
    }

    /*
     * 获取所有子部门
    */
    public function getSubDepartment($param){
        if(empty($param['classid']) || empty($param['crid'])){
            return FALSE;
        }
        $sql = 'select classid,lft,rgt from ebh_classes where classid='.$param['classid'].' and crid='.$param['crid'];
        $class = Ebh()->db->query($sql)->row_array();

        if(!empty($class)){
            $sql = 'select classid from ebh_classes where crid='.$param['crid'].' and lft>'.$class['lft'].' and rgt<'.$class['rgt'];
            return Ebh()->db->query($sql)->list_array();
        } else {
            return FALSE;
        }
    }

    /**
     * 批量修改员工部门
     * @param $classid
     * @param $staffid
     * @param $crid
     * @return bool
     */
    public function batchChangeDept($classid, $staffid, $crid) {
        $classid = intval($classid);
        $crid = intval($crid);
        $dept = $this->getDept($classid, $crid);
        if (empty($dept) || $dept['category'] == 1) {
            return false;
        }
        /*$sql = 'SELECT `classid` FROM `ebh_classes` WHERE `superior`='.$classid.' AND `crid`='.$crid.' LIMIT 1';
        $subDept = Ebh()->db->query($sql)->row_array();
        if (!empty($subDept)) {
            return -1;
        }*/
        if (is_array($staffid)) {
            $staffid = array_map('intval', $staffid);
        } else {
            $staffid = array(intval($staffid));
        }
        $staffidStr = implode(',', $staffid);
        $sql = 'SELECT DISTINCT `a`.`classid` FROM `ebh_classstudents` `a` 
              LEFT JOIN `ebh_classes` `b` ON `a`.`classid`=`b`.`classid` 
              WHERE `b`.`crid`='.$crid.' AND `a`.`uid` IN('.$staffidStr.')';
        $sourceClassids = Ebh()->db->query($sql)->list_field();
        if (empty($sourceClassids)) {
            return false;
        }
        Ebh()->db->begin_trans();
        $sql = 'UPDATE `ebh_classstudents` SET `classid`='.$classid.' WHERE `uid` IN('.
            $staffidStr.') AND EXISTS(SELECT 1 FROM `ebh_classes` WHERE `ebh_classes`.`classid`=`ebh_classstudents`.`classid` AND `ebh_classes`.`crid`='.
            $crid.')';
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        $affectedRows = Ebh()->db->affected_rows();
        $sourceClassidStr = implode(',', $sourceClassids);
        $sql = 'UPDATE `ebh_classes` SET `stunum`=(SELECT COUNT(1) FROM `ebh_classstudents` `a` LEFT JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid` LEFT JOIN `ebh_members` `c` ON `a`.`uid`=`c`.`memberid` LEFT JOIN `ebh_roomusers` `d` ON `a`.`uid`=`d`.`uid` WHERE `a`.`classid`=`ebh_classes`.`classid` AND `d`.`crid`='.$crid.' AND IFNULL(`b`.`uid`,0)>0 AND IFNULL(`c`.`memberid`,0)>0) WHERE `classid` IN('.$sourceClassidStr.')';
        Ebh()->db->query($sql);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->query('UPDATE `ebh_classes` SET `stunum`=(SELECT COUNT(1) FROM `ebh_classstudents` `a` LEFT JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid` LEFT JOIN `ebh_members` `c` ON `a`.`uid`=`c`.`memberid` LEFT JOIN `ebh_roomusers` `d` ON `a`.`uid`=`d`.`uid` WHERE `a`.`classid`='.$classid.' AND `d`.`crid`='.$crid.' AND IFNULL(`b`.`uid`,0)>0 AND IFNULL(`b`.`uid`,0)>0 AND IFNULL(`c`.`memberid`,0)>0) WHERE `classid`='.$classid);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return $affectedRows;
    }

    /*
     * 获取子部门员工
    */
    public function getSubDeptUsers($param){
        if(empty($param['classids']) || empty($param['crid'])){
            return FALSE;
        }
        $sql = 'select cs.uid,u.username,u.face,u.realname,u.sex,u.groupid,cs.classid,u.email,u.mobile,ru.mobile as smobile from ebh_classstudents cs
				join ebh_roomusers ru on cs.uid=ru.uid
				join ebh_users u on cs.uid=u.uid
				join ebh_classes c on cs.classid=c.classid';
		$wherearr[] = 'cs.classid in ('.$param['classids'].')';
		$wherearr[] = 'ru.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'ru.cstatus=1';
		$wherearr[] = 'u.status=1';
		if(!empty($param['uids'])){
			$not = !empty($param['bind']) && $param['bind'] == 2?'not':'';
			$wherearr[] = 'cs.uid '.$not.' in ('.$param['uids'].')';
		}
		if(!empty($param['q'])){
			$q = Ebh()->db->escape_str($param['q']);
			$wherearr[] = '(u.realname like \'%'.$q.'%\' or u.username like \'%'.$q.'%\')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(empty($param['nolimit'])){
			if(!empty($param['limit'])) {
				$sql .= ' limit '. $param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
		}
        return Ebh()->db->query($sql)->list_array();
    }

    /*
     *多个部门学生数量
    */
    public function getDeptUserCount($param){
        if(empty($param['classids']) || empty($param['crid'])){
            return FALSE;
        }
        $sql = 'select count(*) count,cs.classid from ebh_classstudents cs
				join ebh_roomusers ru on cs.uid=ru.uid
				join ebh_users u on cs.uid=u.uid
				join ebh_classes c on cs.classid=c.classid';
		$wherearr[] = 'cs.classid in ('.$param['classids'].')';
		$wherearr[] = 'ru.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		if(!empty($param['uids'])){
			$not = !empty($param['bind']) && $param['bind'] == 2?'not':'';
			$wherearr[] = 'cs.uid '.$not.' in ('.$param['uids'].')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by cs.classid';
        return Ebh()->db->query($sql)->list_array('classid');
    }

    /**
     *获取用户所在的班级信息
     *@param int $crid教室编号
     *@param int $uid 用户编号
     */
    public function getClassByUid($crid,$uid,$needlist=false) {
        if(empty($crid) || empty($uid)){
            return FALSE;
        }
        $sql = "SELECT cs.classid,c.classname,c.grade,c.district,c.headteacherid from  ebh_classstudents cs ".
            "JOIN ebh_classes c on (c.classid = cs.classid) ".
            "WHERE c.crid=$crid and cs.uid = $uid";
        if($needlist === TRUE){
            $classinfo = Ebh()->db->query($sql)->list_array();
        }else{
            $classinfo = Ebh()->db->query($sql)->row_array();
        }
        return $classinfo;
    }

    /**
     *获取教室下默认的班级信息，一般是最新添加的班级
     */
    public function getDefaultClass($crid,$grade=0,$district=0) {
        if(!empty($grade) || !empty($district)){
            $sql = "select classid,classname from ebh_classes where crid=$crid and status=0 and grade=$grade and district=$district order by classid asc limit 1";
        }else{
            $sql = "select classid,classname from ebh_classes where crid=$crid and status=0 order by classid asc limit 1";
        }
        return Ebh()->db->query($sql)->row_array();

    }

    /*
	添加班级
	@param array $param crid,classname
	@return int $classid 班级号
	*/
    public function addclass($param){
        $setarr['crid'] = $param['crid'];
        $setarr['classname'] = trim($param['classname'],' ');
        $setarr['classname'] = str_replace('　','',$setarr['classname']);
        if(isset($param['grade']))
            $setarr['grade'] = $param['grade'];
        if(isset($param['district']))
            $setarr['district'] = $param['district'];
        $setarr['dateline'] = SYSTIME;
        return Ebh()->db->insert('ebh_classes',$setarr);
    }
	
	/*
	 *多个部门绑定数量
	*/
	public function getBindCount($param){
		if(empty($param['classids']) || empty($param['crid']) || empty($param['uids']) || empty($param['bind'])){
			return FALSE;
		}
		$sql = 'select count(distinct cs.uid) count,cs.classid from ebh_classstudents cs
				join ebh_roomusers ru on cs.uid=ru.uid
				join ebh_users u on cs.uid=u.uid
				join ebh_classes c on cs.classid=c.classid';
		$wherearr[] = 'cs.classid in('.$param['classids'].')';
		$wherearr[] = 'ru.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		if($param['bind'] == 1){//绑定
			$wherearr[] = 'cs.uid in ('.$param['uids'].')';
		} else {//未绑定
			$wherearr[] = 'cs.uid not in ('.$param['uids'].')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by cs.classid';
		return Ebh()->db->query($sql)->list_array('classid');
	}
	
	/**
     * 获取已经绑定的学生
     */
    public function getBindStudent($param){
		$this->ethdb = getOtherDb('ethdb');
    	$sql = 'select distinct uid from ebh_binds';
		$wherearr[] = 'crid ='.$param['crid'];
		if(!empty($param['uids'])){
			$wherearr[] = 'uid in ('.$param['uids'].')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
    	$rows = $this->ethdb->query($sql)->list_array('uid');
    	return $rows;
    }

    /**
     * 获取用户的班级信息
     */
    public function getClassInfoByCrid($crid,$uidarr){
        if(empty($crid) || empty($uidarr)){
            return false;
        }
        $sql = 'select c.classname,c.classid,cs.uid from `ebh_classes` c left join `ebh_classstudents` cs on(c.classid = cs.classid) where c.crid ='.intval($crid).' and cs.uid in('.implode(',',$uidarr).')';
        return Ebh()->db->query($sql)->list_array();
    }
	
	/*
	企业版教师有权限的课程
	*/
	public function getTeacherCourse($param){
		if(empty($param['crid']) || empty($param['uid'])){
			return FALSE;
		}
		$crid = $param['crid'];
		//教师的部门
		$sql = 'select c.classid,lft,rgt 
				from ebh_classes c 
				join ebh_classteachers ct on c.classid=ct.classid';
		$wherearr[] = 'c.crid='.$crid;
		$wherearr[] = 'ct.uid='.$param['uid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$classes = Ebh()->db->query($sql)->list_array();
		if(empty($classes)){
			return array();
		}
		//教师部门的下级
		$sql = 'select classid from ebh_classes';
		$wherearr = array();
		$wherearr[] = 'crid='.$crid;
		foreach($classes as $class){
			$orarr[]= '(lft >= '.$class['lft'].' and rgt <= '.$class['rgt'].')';
		}
		$wherearr[] = '('.implode(' OR ',$orarr).')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$classes = Ebh()->db->query($sql)->list_array();
		//课程
		$classids = array_column($classes,'classid');
		$classids = implode(',',$classids);
		$sql = 'select folderid from ebh_classcourses cc
				join ebh_classes c on cc.classid=c.classid
				where c.classid in('.$classids.') and c.crid='.$crid;
        return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	部门有权限的教师（部门老师及上级部门教师）
	*/
	public function deptTeacherList($param){
		if(empty($param['crid']) || empty($param['classid'])){
			return array();
		}
		//当前选择部门
		$sql = 'select lft,rgt from ebh_classes where classid='.$param['classid'].' and crid='.$param['crid'];
		$thisclass = Ebh()->db->query($sql)->row_array();
		if(empty($thisclass)){
			return array();
		}
		//该部门及其上级部门
		$sql = 'select classid from ebh_classes 
				where lft<='.$thisclass['lft'].' 
				and rgt>='.$thisclass['rgt'].' 
				and crid='.$param['crid'];
		$classes = Ebh()->db->query($sql)->list_array();
		$classids = array_column($classes,'classid');
		
		//部门教师
		$sql = 'select uid from ebh_classteachers where classid in('.implode(',',$classids).')';
		return Ebh()->db->query($sql)->list_array();
	}

    /**
     * 获取学校的班级列表
     * @param type $crid
     * @param type $classid
     * @return array
     */
    public function getRoomClassList($crid,$classid = 0,$limit='') {
        $sql = 'select c.classid,c.classname,c.stunum,c.grade,c.headteacherid,c.code,c.superior from ebh_classes c '.
            'where c.crid='.$crid.' and c.`status`=0 ';
        if($classid>0){
            $sql .= 'and c.classid=' .$classid;
        }else{
            $sql .= ' order by c.classid';
        }
        if(!empty($limit)) {
            $sql .= ' limit '. $limit;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /*
	班级学生的id
	*/
    public function getClassStudentUid($classid = 0){
        $sql = 'select uid from ebh_classstudents where classid='.$classid;
        return Ebh()->db->query($sql)->list_array();
    }

    public function getClassTeacherUid($classid = 0){
        $sql = 'select uid from ebh_classteachers where classid = '.$classid;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 重置部门数据
     * @param array $depts 部门数组
     * @param int $crid 所在网校
     * @return bool
     */
    public function resetDeptment($depts, $crid) {
        if (!is_array($depts)) {
            return false;
        }
        $categorys = $superiors = $lfts = $rgts = $paths = $whens = $codes = array();
        $code = 1001;
        foreach ($depts as $dept) {
            $categorys[] = ' WHEN '.$dept['classid'].' THEN '.$dept['category'];
            $superiors[] = ' WHEN '.$dept['classid'].' THEN '.$dept['superior'];
            $lfts[] = ' WHEN '.$dept['classid'].' THEN '.$dept['lft'];
            $rgts[] = ' WHEN '.$dept['classid'].' THEN '.$dept['rgt'];
            $paths[] = ' WHEN '.$dept['classid'].' THEN '.Ebh()->db->escape($dept['path']);
            if ($dept['category'] == 0) {
                $codes[] = ' WHEN '.$dept['classid'].' THEN '.($code++);
            } else {
                $codes[] = ' WHEN '.$dept['classid'].' THEN 0';
            }
            $whens[] = $dept['classid'];
        }
        $sql = 'UPDATE `ebh_classes` SET `category`=CASE `classid`'.implode('', $categorys).
            ' END,`superior`=CASE `classid`'.implode('', $superiors).
            ' END,`lft`=CASE `classid`'.implode('', $lfts).
            ' END,`rgt`=CASE `classid`'.implode('', $rgts).
            //' END,`code`=CASE `classid`'.implode('', $codes).
            ' END,`path`=CASE `classid`'.implode('', $paths).
            ' END WHERE `classid` IN('.implode(',', $whens).') AND `crid`='.intval($crid);
        return Ebh()->db->query($sql, false);
    }

    /**
     * 部门列表
     * @param int $crid 网校ID
     * @param string $deptName 部门名称
     * @param mixed $limit 限量条件
     * @return array
     */
    public function getDeptList($crid, $deptName = '', $limit = null) {
        $wheres = array('`crid`='.$crid, '`status`=0', '`category`=0');
        if ($deptName != '') {
            $wheres[] = '`classname` LIKE '.Ebh()->db->escape('%'.$deptName.'%');
        }
        $sql = 'SELECT `classname` FROM `ebh_classes` WHERE '.implode(' AND ', $wheres).' ORDER BY `dateline` DESC,`code` ASC';
        $offset = 0;
        $top = 0;
        if (!empty($limit)) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $pagesize = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = $pagesize = max(1, $pagesize);
                $offset = ($page - 1) * $pagesize;
            } else if (is_numeric($limit) && $limit > 0) {
                $top = intval($limit);
            }
        }
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_field();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 验证部门是否有效，有效返回部门ID
     * @param string $deptName 部门名称
     * @param string $code 部门编号
     * @param int $crid 网校ID
     * @return mixed
     */
    public function verify($deptName, $code, $crid) {
        $wheres = array(
            '`crid`='.$crid,
            '`classname`='.Ebh()->db->escape($deptName),
            '`code`='.Ebh()->db->escape($code),
            '`category`=0',
            '`status`=0'
        );
        $sql = 'SELECT `classid` FROM `ebh_classes` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        return $ret['classid'];
    }

    /**
     * 获取顶级部门
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getRootDept($crid) {
        $wheres = array(
            '`crid`='.$crid,
            '`category`=1',
            '`status`=0'
        );
        $sql = 'SELECT `classid`,`classname`,`category`,`superior`,`lft`,`rgt`,`code`,`path`,`stunum` FROM `ebh_classes` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return array(
                'classid' => -1,
                'classname' => '顶级部门',
                'category' => 1,
                'superior' => 0,
                'lft' => 1,
                'rgt' => 2,
                'code' => 0,
                'path' => '/顶级部门',
                'stunum' => 0
            );
        }
        return $ret;
    }

    /**
     * 获取下级部门下一编号
     * @param int $crid 网校ID
     * @return string
     */
    public function getNextDeptCode($crid) {
        Ebh()->db->set_con(0);
        $codes = Ebh()->db->query('SELECT `code` FROM `ebh_classes` WHERE `crid`='.$crid)->list_field();
        if (empty($codes)) {
            return '10001';
        }
        $codeIndexs = array_map(function($code) {
            if (is_numeric($code)) {
                return max(0, intval($code));
            }
            if (preg_match('/^([a-zA-Z0-9]*?)(\d+)$/', $code, $matchs)) {
                return intval($matchs[2]);
            }
            return 0;
        }, $codes);
        $k = max($codeIndexs);
        $codeIndexs = array_flip($codeIndexs);
        $code = $codes[$codeIndexs[$k]];
        if (is_numeric($code)) {
            if ($code <= 0) {
                return 10001;
            }
            $len = strlen($code);
            return str_pad(intval($code) + 1, $len, '0', STR_PAD_LEFT);
        }
        if (preg_match('/^([a-zA-Z0-9]*?)(\d+)$/', $code, $matchs)) {
            $len = strlen($matchs[2]);
            return $matchs[1].str_pad(intval($matchs[2] + 1), $len, '0', STR_PAD_LEFT);
        }
        return 10001;
    }

    /**
     * 导入部门
     * @param int $crid 网校ID
     * @param int superiorId 导入上级部门ID
     * @param array $depts 部门集
     * @return mixed
     */
    public function importDeptments($crid, $superiorId, $depts) {
        Ebh()->db->set_con(0);
        $superior = Ebh()->db->query(
            'SELECT `classid`,`lft`,`rgt`,`path` FROM `ebh_classes` WHERE `classid`='.$superiorId.' AND `crid`='.$crid)
            ->row_array();
        if (empty($superior)) {
            return false;
        }
        $datas = Ebh()->db->query('SELECT `code`,`classname`,`superior` FROM `ebh_classes` WHERE `crid`='.$crid.' AND `status`=0')->list_array();
        $codes = array_column($depts, 'code');
        $codes = array_filter($codes, function($code) {
           return $code != '';
        });
        $names = array_column($depts, 'deptname');
        if (!empty($datas)) {
            $dataCodes = array_column($datas, 'code');
            $datas = array_filter($datas, function($code) use($superiorId) {
                return $code['superior'] == $superiorId;
            });
            $dataNames = array_column($datas, 'classname');
            unset($datas);
            $repeatCodes = array_intersect($codes, $dataCodes);
            $repeatNames = array_intersect($names, $dataNames);
            $errs = '';
            if (!empty($repeatCodes)) {
                $errs = '重复部门编号：'.implode(', ', $repeatCodes);
            }
            if (!empty($repeatNames)) {
                $errs .= '; 重复部门名称：'.implode(', ', $repeatNames);
            }
            if (!empty($errs)) {
                return ltrim($errs, ';');
            }
            $codes = array_merge($codes, $dataCodes);
            unset($repeatCodes, $repeatNames);
        }
        $codeIndexs = array_map(function($code) {
            if (is_numeric($code)) {
                return max(0, intval($code));
            }
            if (preg_match('/^([a-zA-Z0-9]*?)(\d+)$/', $code, $matchs)) {
                return intval($matchs[2]);
            }
            return 0;
        }, $codes);
        $k = max($codeIndexs);
        $codeIndexs = array_flip($codeIndexs);
        $code = $codes[$codeIndexs[$k]];
        unset($codeIndexs);
        $prx = '';
        $step = 10000;
        $len = 5;
        $base = $lft = $superior['rgt'];
        $newLen = count($depts) * 2;
        if (preg_match('/^([a-zA-Z0-9]*?)(\d+)$/', $code, $matchs)) {
            if (!empty($matchs[1])) {
                $prx = $matchs[1];
            }
            $len = strlen($matchs[2]);
            $step = intval($matchs[2]);
        }
        $sql = array();
        foreach ($depts as $index => $dept) {
            $args = array(
                'classname' => Ebh()->db->escape($dept['deptname']),
                'grade' => 0,
                'crid' => $crid,
                'year' => 0,
                'stunum' => 0,
                'dateline' => SYSTIME,
                'status' => 0,
                'district' => 0,
                'headteacherid' => 0,
                'category' => 0,
                'superior' => $superiorId,
                'lft' => $lft++,
                'rgt' => $lft++,
                'displayorder' => 0,
                'path' => Ebh()->db->escape($superior['path'].'/'.$dept['deptname'])
            );
            $code = $dept['code'] == '' ? $prx.str_pad(++$step, $len, '0', STR_PAD_LEFT) : $dept['code'];;
            $args['code'] = Ebh()->db->escape($code);
            $sql[] = '('.implode(',', $args).')';
        }
        Ebh()->db->query('UPDATE `ebh_classes` SET `lft`=`lft`+'.$newLen.' WHERE `lft`>='.$base.' AND `crid`='.$crid, false);
        Ebh()->db->query('UPDATE `ebh_classes` SET `rgt`=`rgt`+'.$newLen.' WHERE `rgt`>='.$base.' AND `crid`='.$crid, false);
        $sql = 'INSERT INTO `ebh_classes`(`classname`,`grade`,`crid`,`year`,`stunum`,`dateline`,`status`,`district`,`headteacherid`,`category`,`superior`,`lft`,`rgt`,`displayorder`,`path`,`code`) VALUES '.implode(',', $sql);
        Ebh()->db->query($sql, false);
        return true;
    }
}
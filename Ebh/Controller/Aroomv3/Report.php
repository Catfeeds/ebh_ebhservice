<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ReportController extends Controller{


    public function parameterRules(){
        return array(
            'teacherAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'classid'  =>  array('name'=>'classid','default'=>0,'type'=>'int'),
                'groupid'  =>  array('name'=>'groupid','default'=>0,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'isenterprise' => array('name'=>'isenterprise','type'=>'int','default'=>0)
            ),
            'tCoursewaresAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'folderid'  =>  array('name'=>'folderid','default'=>0,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            ),
            'classFolderAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'classid'  =>  array('name'=>'classid','default'=>0,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','default'=>0,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'isenterprise' => array('name'=>'isenterprise','type'=>'int','default'=>0),
                'uid' => array('name' => 'uid', 'type' => 'int', 'default' => 0)
            ),
            'studentAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'classid'  =>  array('name'=>'classid','default'=>0,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'isenterprise' => array('name'=>'isenterprise','type'=>'int','default'=>0),
                'uid' => array('name' => 'uid', 'type' => 'int', 'default' => 0)
            ),
            'studentFolderAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            ),
            'studentCourseAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            )
        );
    }

    /**
     * 教师统计
     * @return array
     */
    public function teacherAction(){
        $parameters = array();
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }

        if($this->classid > 0){
            $parameters['classid'] = $this->classid;
        }

        if($this->groupid > 0){
            $parameters['groupid'] = $this->groupid;
        }

        if($this->starttime > 0){
            $parameters['starttime'] = $this->starttime;
        }
        if($this->endtime > 0){
            $parameters['endtime'] = $this->endtime;
        }
        $parameters['pagesize'] = $this->pagesize;

        if ($this->isenterprise > 0) {
            $parameters['isenterprise'] = 1;
        }

        $teacherModel = new TeacherModel();

        $total = $teacherModel->teacherReportCount($this->crid,$parameters);

        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $cacheKey = md5( 'report_teacher_'.http_build_query($parameters).'_'.$this->crid);
        $ret = Ebh()->cache->get($cacheKey);
        if($ret){
            return $ret;
        }
        $list = $teacherModel->teacherReport($this->crid,$parameters);
        if ($this->isenterprise > 0 && !empty($list)) {
            array_walk($list, function(&$dept) {
                $depts = explode(',', $dept['class']);
                foreach ($depts as &$v) {
                    $v = trim($v, '/');
                    $v = substr(strstr($v, '/'), 1);
                    $v = preg_replace('/\//', '>', $v);
                    $v = urldecode($v);
                }
                $dept['class'] = implode(',', $depts);
            });
        }


        $ret =  array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
        Ebh()->cache->set($cacheKey,$ret);
        return $ret;
    }

    /**
     * 教师课件统计
     * @return array
     */
    public function tCoursewaresAction(){
        $parameters = array();
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }

        if($this->folderid > 0){
            $parameters['folderid'] = $this->folderid;
        }
        if($this->starttime > 0){
            $parameters['starttime'] = $this->starttime;
        }
        if($this->endtime > 0){
            $parameters['endtime'] = $this->endtime;
        }

        $parameters['pagesize'] = $this->pagesize;

        $teacherModel = new TeacherModel();

        $total = $teacherModel->teacherCoursewaresReportCount($this->crid,$parameters, true);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $cacheKey = md5( 'report_tcoursewares_v2_'.http_build_query($parameters).'_'.$this->crid);
        $ret = Ebh()->cache->get($cacheKey);
        if($ret){
            return $ret;
        }
        $list = $teacherModel->teacherCoursewaresReport($this->crid,$parameters, true);



        $ret =  array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
        Ebh()->cache->set($cacheKey,$ret);
        return $ret;
    }

    public function classFolderAction(){
        $parameters = array();
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }

        if($this->folderid > 0){
            $parameters['folderid'] = $this->folderid;
        }
        if ($this->uid > 0) {
            //非网校管理员用户，读取权限范围
            $teacherroleModel = new TeacherRoleModel();
            $role = $teacherroleModel->getTeacherRole($this->uid, $this->crid);
            if (is_numeric($role) && $role != 2) {
                //非系统管理员角色
                return array();
            }
            if (!empty($role['limitscope'])) {
                //自定义的权限受限管理员角色
                $classTeacherModel = new ClassTeacherModel();
                if ($this->isenterprise == 1) {
                    $teacherDepts = $classTeacherModel->getDeptsForTeacher($this->uid, $this->crid);
                    if (empty($teacherDepts)) {
                        return array();
                    }
                    $parents = array();
                    while ($parent = array_shift($teacherDepts)) {
                        $parents[] = $parent;
                        $teacherDepts = array_filter($teacherDepts, function($dept) use($parent) {
                            return $dept['rgt'] > $parent['rgt'] || $dept['lft'] < $parent['lft'];
                        });
                    }
                    $classes = $classTeacherModel->getDeptsForTeacherWithPath($this->crid, $parents);
                } else {
                    $classes = $classTeacherModel->getClassesForTeacher($this->uid, $this->crid);
                }
                if (empty($classes)) {
                    return array();
                }
            }
        }

        if($this->classid > 0){
            if (!empty($classes) && !isset($classes[$this->classid])) {
                return array(
                    'total' =>  0,
                    'list'  =>  array(),
                    'nowPage'   =>  1,
                    'totalPage' =>  0
                );
            }
            if (isset($classes[$this->classid]['lft'])) {
                $parameters['lft'] = $classes[$this->classid]['lft'];
                $parameters['rgt'] = $classes[$this->classid]['rgt'];
            } else if ($this->isenterprise == 1) {
                $classModel = new ClassesModel();
                $dept = $classModel->getDept($this->classid, $this->crid);
                if (empty($dept)) {
                    return array(
                        'total' =>  0,
                        'list'  =>  array(),
                        'nowPage'   =>  1,
                        'totalPage' =>  0
                    );
                }
                if ($dept['superior'] > 0) {
                    $parameters['lft'] = $dept['lft'];
                    $parameters['rgt'] = $dept['rgt'];
                }
            } else {
                $parameters['classid']  = $this->classid;
            }
        }
        if (empty($this->classid) && !empty($classes)) {
            $parameters['classids'] = array_keys($classes);
        }
        /*if($this->classid > 0){
            $parameters['classid'] = $this->classid;
        }*/
        if($this->starttime > 0){
            $parameters['starttime'] = $this->starttime;
        }
        if($this->endtime > 0){
            $parameters['endtime'] = $this->endtime;
        }
        $parameters['pagesize'] = $this->pagesize;
        if ($this->isenterprise > 0) {
            $parameters['isenterprise'] = 1;
        }

        $classesModel = new ClassesModel();
        $total = $classesModel->classFolderReportCount($this->crid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $cacheKey = md5( 'report_classfolder_'.http_build_query($parameters).'_'.$this->crid);
        $ret = Ebh()->cache->get($cacheKey);
        if($ret){
            return $ret;
        }
        $list = $classesModel->classFolderReport($this->crid,$parameters);
        if (!empty($list) && $this->isenterprise > 0) {
            array_walk($list, function(&$v) {
                /*$v['classname'] = trim($v['path'], '/');
                $v['classname'] = substr(strstr($v['classname'], '/'), 1);
                $v['classname'] = preg_replace('/\//', '>', $v['classname']);
                $v['classname'] = urldecode($v['classname']);
                unset($v['path']);*/
            });
        }



        $ret =  array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
        Ebh()->cache->set($cacheKey,$ret);
        return $ret;
    }


    /**
     * 学生统计
     * @return array
     */
    public function studentAction(){
        $parameters = array();
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }
        if ($this->uid > 0) {
            //非网校管理员用户，读取权限范围
            $teacherroleModel = new TeacherRoleModel();
            $role = $teacherroleModel->getTeacherRole($this->uid, $this->crid);
            if (is_numeric($role) && $role != 2) {
                //非系统管理员角色
                return array();
            }
            if (!empty($role['limitscope'])) {
                //自定义的权限受限管理员角色
                $classTeacherModel = new ClassTeacherModel();
                if ($this->isenterprise == 1) {
                    $teacherDepts = $classTeacherModel->getDeptsForTeacher($this->uid, $this->crid);
                    if (empty($teacherDepts)) {
                        return array();
                    }
                    $parents = array();
                    while ($parent = array_shift($teacherDepts)) {
                        $parents[] = $parent;
                        $teacherDepts = array_filter($teacherDepts, function($dept) use($parent) {
                            return $dept['rgt'] > $parent['rgt'] || $dept['lft'] < $parent['lft'];
                        });
                    }
                    $classes = $classTeacherModel->getDeptsForTeacherWithPath($this->crid, $parents);
                } else {
                    $classes = $classTeacherModel->getClassesForTeacher($this->uid, $this->crid);
                }
                if (empty($classes)) {
                    return array();
                }
            }
        }

        if($this->classid > 0){
            if (!empty($classes) && !isset($classes[$this->classid])) {
                return array(
                    'total' =>  0,
                    'list'  =>  array(),
                    'nowPage'   =>  1,
                    'totalPage' =>  0
                );
            }
            if (isset($classes[$this->classid]['lft'])) {
                $parameters['lft'] = $classes[$this->classid]['lft'];
                $parameters['rgt'] = $classes[$this->classid]['rgt'];
            } else if ($this->isenterprise == 1) {
                $classModel = new ClassesModel();
                $dept = $classModel->getDept($this->classid, $this->crid);
                if (empty($dept)) {
                    return array(
                        'total' =>  0,
                        'list'  =>  array(),
                        'nowPage'   =>  1,
                        'totalPage' =>  0
                    );
                }
                if ($dept['superior'] > 0) {
                    $parameters['lft'] = $dept['lft'];
                    $parameters['rgt'] = $dept['rgt'];
                }
            } else {
                $parameters['classid']  = $this->classid;
            }
        }
        if (empty($this->classid) && !empty($classes)) {
            $parameters['classids'] = array_keys($classes);
        }
        if($this->starttime > 0){
            $parameters['starttime'] = $this->starttime;
        }
        if($this->endtime > 0){
            $parameters['endtime'] = $this->endtime;
        }
        if ($this->isenterprise > 0) {
            $parameters['isenterprise'] = 1;
        }
        $parameters['pagesize'] = $this->pagesize;
        $roomUserModel = new RoomUserModel();

        $total = $roomUserModel->roomStudentReportCount($this->crid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $cacheKey = md5( 'report_student_'.http_build_query($parameters).'_'.$this->crid);
        $ret = Ebh()->cache->get($cacheKey);
        if($ret){
            return $ret;
        }
        $list = $roomUserModel->roomStudentReport($this->crid,$parameters);
		$uids = array_column($list,'uid');
		if(!empty($uids)){
			$uids = implode(',',$uids);
			$slmodel = new StudycreditlogsModel();
			$scoresum = $slmodel->getUserSum(array('crid'=>$this->crid,'uids'=>$uids));
			foreach($list as &$user){
				if(!empty($scoresum[$user['uid']])){
					$user['totalscore'] = $scoresum[$user['uid']]['scores'];
				}
			}
		}
		
        $ret =  array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
        Ebh()->cache->set($cacheKey,$ret);
        return $ret;
    }

    /**
     * 学生课程统计
     * @return array
     */
    public function studentFolderAction(){
        $parameters = array();
        $roomUserModel = new RoomUserModel();

        $total = $roomUserModel->roomStudentFolderReportCount($this->crid,$this->uid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $roomUserModel->roomStudentFolderReport($this->crid,$this->uid,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 学生课件统计
     * @return array
     */
    public function studentCourseAction(){
        $parameters = array();
        $roomUserModel = new RoomUserModel();

        $total = $roomUserModel->roomStudentCourseReportCount($this->crid,$this->folderid,$this->uid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $roomUserModel->roomStudentCourseReport($this->crid,$this->folderid,$this->uid,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }
}
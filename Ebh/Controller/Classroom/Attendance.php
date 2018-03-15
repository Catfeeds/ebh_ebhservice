<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 11:38
 *
 * 考勤出勤控制模块
 */
class AttendanceController extends Controller{


    public function parameterRules(){
        return array(
            'addAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
            ),
            'courseAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid','type'=>'int','default'=>0),
                'begindate'  =>  array('name'=>'begindate','type'=>'int','default'=>0),
                'enddate'  =>  array('name'=>'enddate','type'=>'int','default'=>0),
                'name'  =>  array('name'=>'name','default'=>''),
				'todayfirst'=>array('name'=>'todayfirst','default'=>false,'type'=>'boolean')
            ),
            'checkAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'classid'  =>  array('name'=>'classid','type'=>'int','default'=>0),
                'name'  =>  array('name'=>'name','default'=>''),
                'begindate'  =>  array('name'=>'begindate','type'=>'int','default'=>0),
                'enddate'  =>  array('name'=>'enddate','type'=>'int','default'=>0),
                'state'  =>  array('name'=>'state','type'=>'int','default'=>0),
                'all'  =>  array('name'=>'all','type'=>'int','default'=>0),
            ),
            'countAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'classid'  =>  array('name'=>'classid','type'=>'int','default'=>0),
            ),
			'courseAllAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid','type'=>'int','default'=>0),
                'begindate'  =>  array('name'=>'begindate','type'=>'int','default'=>0),
                'enddate'  =>  array('name'=>'enddate','type'=>'int','default'=>0),
                'name'  =>  array('name'=>'name','default'=>''),
				'exportall'=> array('name'=>'exportall','type'=>'int','default'=>0),
            ),
            //班级下的课件出勤统计
            'classCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'require' => true
                ),
                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'int',
                    'default' => 0
                ),
                's' => array(
                    'name' => 's',
                    'type' => 'string'
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 0
                ),
                'startstamp' => array(
                    'name' => 'startstamp',
                    'type' => 'int'
                ),
                'endstamp' => array(
                    'name' => 'endstamp',
                    'type' => 'int'
                )
            )
        );
    }

    /**
     * 提交考勤记录
     */
    public function addAction(){
        $attendanceModel = new AttendancesModel();
        $params['uid'] = $this->uid;
        $params['cwid'] = $this->cwid;
        $params['crid'] = $this->crid;
        if(!$attendanceModel->exist($params)){
            $attendanceModel->insert($params);
        }
        return array();
    }

    /**
     * 考勤出勤课件列表
     * (直播课件列表)
     * 课件列表
     */
    public function courseAction(){
        $courseModel = new CoursewareModel();
        $params['status'] = 1;
        $params['crid'] = $this->crid;
        $params['order'] = ' c.submitat DESC,c.cwid desc';
        $params['live'] = 1;

        if($this->folderid > 0){
            $params['folderids'] = $this->folderid;
        }

        if($this->begindate){
            $params['truedatelinefrom'] = $this->begindate;
        }
        if($this->enddate){
            $params['truedatelineto'] = $this->enddate;
        }
		
		//今天的排在前面
		if(!empty($this->todayfirst)){
			$today = Date('Ymd',SYSTIME);
            $params['order'] = "from_unixtime(c.truedateline,'%Y%m%d')=$today desc,c.truedateline desc";
        }
		
        //名字搜索
        if(!empty($this->name)){
            $params['name'] = $this->name;
        }
        $total = $courseModel->getNewCourseCount($params);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $params['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $courseList = $courseModel->getNewCourseList($params);


        return array(
            'total' =>  $total,
            'list'  =>  $courseList,
        );
    }


    /**
     * 考勤列表
     */
    public function checkAction(){

        $result = array(
            'total' =>  0,
            'list'  =>  array(),
            'classes'   =>  array()
        );
        //获取班级信息 管理员获取所有班级
        $uid = $this->uid;
        $classroomModel = new ClassRoomModel();


        $classroom = $classroomModel->getModel($this->crid);
        //如果不是网校管理员 则增加班级过滤
        //获取班级信息
        $classesMoldel = new ClassesModel();
        if($uid == $classroom['uid']){
            $classes = $classesMoldel->getList(array('crid'=>$this->crid));
        }else{
            $classes = $classesMoldel->getList(array('crid'=>$this->crid,'headteacherid'=>$uid));
        }


        //如果班级信息为空 直接返回
        if(empty($classes)){
            return $result;
        }


        $result['classes'] = $classes;
        $classIds = array();

        foreach ($classes as $class){
            $classIds[] = $class['classid'];
        }
        //获取学生数据开始
        $courseModel = new CoursewareModel();

        $course = $courseModel->getCourseByCwid($this->cwid);

        if(!$course){
            return $result;
        }

        $folderId = $course['folderid'];
        $attendanceModel = new AttendancesModel();
        //获取课程的学生列表
        $params = array('crid'=>$this->crid,'folderid'=>$folderId,'cwid'=>$this->cwid);
        //班级过滤
        if($this->classid > 0 && in_array($this->classid,$classIds)){
            $params['classids'][] = $this->classid;
        }else if(!empty($classIds)){
            $params['classids'] = $classIds;
        }

        //名字搜索
        if(!empty($this->name)){
            $params['name'] = $this->name;
        }

        //时间筛选

        if($this->begindate){
            $params['begindate'] = $this->begindate;
        }
        if($this->enddate){
            $params['enddate'] = $this->enddate;
        }
        //学习状态筛选

        if($this->state > 0){
            $params['state'] = $this->state;
        }
        $total = $attendanceModel->getUserCount($params);

        if($this->all == 0){
            $pageClass  = new Page($total,getConfig('system.page.listRows'));
            $params['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        }

        $userList = $attendanceModel->getUserList($params);


        $result['total'] = $total;
        $result['list'] = $userList;
        $result['course'] = $course;
        return $result;
    }

    /**
     * 出勤统计
     */
    public function countAction(){
        $result = array(
            'list'  =>  array(),
            'classes'   =>  array()
        );
        //获取班级信息 管理员获取所有班级
        $uid = $this->uid;
        $classroomModel = new ClassRoomModel();


        $classroom = $classroomModel->getModel($this->crid);
        //如果不是网校管理员 则增加班级过滤
        //获取班级信息
        $classesMoldel = new ClassesModel();
        if($uid == $classroom['uid']){
            $classes = $classesMoldel->getList(array('crid'=>$this->crid));
        }else{
            $classes = $classesMoldel->getList(array('crid'=>$this->crid,'headteacherid'=>$uid));
        }

        //如果班级信息为空 直接返回
        if(empty($classes)){
            return $result;
        }
		//获取学生数据开始
        $courseModel = new CoursewareModel();

        $course = $courseModel->getCourseByCwid($this->cwid);

		//如果班级选课了，则把其他的班级去掉
		if(!empty($course['classids'])){
			$cwclassids = explode(',',$course['classids']);
			foreach($classes as $k=>$class){
				if(!in_array($class['classid'],$cwclassids)){
					unset($classes[$k]);
				}
			}
		}
        $result['classes'] = $classes;
        $classIds = array_column($classes,'classid');


        
        if(!$course){
            return $result;
        }

        $folderId = $course['folderid'];

        $attendanceModel = new AttendancesModel();
        $params = array('crid'=>$this->crid,'folderid'=>$folderId,'cwid'=>$this->cwid);

        //班级过滤

        if($this->classid > 0 && in_array($this->classid,$classIds)){

            $params['classids'][] = $this->classid;;
        }else if(!empty($classIds)){
            $params['classids'] = $classIds;
        }

        $list = $attendanceModel->getClassAttendance($params);

        $result['list'] = $list;
        $result['course'] = $course;
        return $result;

    }

	
	/**
     * 考勤出勤课件列表 及统计
     */
    public function courseAllAction(){
        $courseModel = new CoursewareModel();
        $params['status'] = 1;
        $params['crid'] = $this->crid;
        $params['order'] = ' c.submitat DESC,c.cwid desc';
        $params['live'] = 1;

        if($this->folderid > 0){
            $params['folderids'] = $this->folderid;
        }

        if($this->begindate){
            $params['truedatelinefrom'] = $this->begindate;
        }
        if($this->enddate){
            $params['truedatelineto'] = $this->enddate;
        }

        //名字搜索
        if(!empty($this->name)){
            $params['name'] = $this->name;
        }
		
		$params['limit'] = 10000;
		
		//课件列表
        $cwlist = $courseModel->getNewCourseList($params);
		//班级列表
		$classmodel = new ClassesModel();
		$classlist = $classmodel->getList(array('crid'=>$this->crid,'roomType'=>'edu','order'=>'classname asc'));
		
		$paramAtt['classids'] = array_column($classlist,'classid');
		$paramAtt['folderids'] = array_unique(array_column($cwlist,'folderid'));
		$paramAtt['cwids'] = array_column($cwlist,'cwid');
		$paramAtt['crid'] = $this->crid;
		
		$attendanceModel = new AttendancesModel();
		//统计结果
		$attendancelist = $attendanceModel->getAttendanceAll($paramAtt);
		$attendancelist['classes'] = $classlist;
		$attendancelist['cwlist'] = $cwlist;
		return $attendancelist;
        // return array(
            // 'total' =>  $total,
            // 'list'  =>  $courseList,
        // );
    }

    /**
     * 班级下的课件出勤统计
     */
    public function classCountAction() {
        $classesModel = new ClassesModel();
        $classitem = $classesModel->getDetail($this->classid);
        if (empty($classitem) || $this->uid > 0 && $classitem['headteacherid'] != $this->uid) {
            //教师非管理员非班主任
            return array();
        }
        $classstudentModel = new ClassstudentsModel();
        $studentids = $classstudentModel->getStudentidList($this->classid);
        if (empty($studentids)) {
            //班级下没有学生
            return array(

            );
        }
        //读取学生学习权限
        $permisionModel = new UserpermisionsModel();
        $permisions = $permisionModel->getServiceListForStudents($studentids, $this->crid);
        if (empty($permisions)) {
            //班级下的学生没有学习权限
            return array();
        }
        //读取课程列表
        $folderModel = new FolderModel();
        $fids = array_unique(array_column($permisions, 'folderid'));
        $folderlist = $folderModel->getFolderMenu($fids, $this->crid);
        unset($fids);
        if ($this->folderid > 0) {
            $folderid = $this->folderid;
            $permisions = array_filter($permisions, function($permision) use($folderid) {
               return $permision['folderid'] == $folderid;
            });
        }
        $folderids = $cwids = array();
        foreach ($permisions as $permision) {
            if (!empty($permision['cwid'])) {
                $cwids[$permision['cwid']] = $permision['cwid'];
                continue;
            }
            $folderids[$permision['folderid']] = $permision['folderid'];
        }
        //读取课件列表
        $roomCourseModel = new RoomCourseModel();
        $params = array('folderids' => $folderids, 'cwids' => $cwids, 'classid' => $this->classid);
        if ($this->s !== null && $this->s != '') {
            $params['s'] = $this->s;
        }
        if ($this->startstamp !== null && $this->startstamp > 0) {
            $params['start'] = $this->startstamp;
        }
        if ($this->endstamp !== null && $this->endstamp > 0) {
            $params['end'] = $this->endstamp;
        }
        $limit = array();
        if ($this->pagesize > 0) {
            $limit['page'] = $this->page;
            $limit['pagesize'] = $this->pagesize;
        }
        $count = $roomCourseModel->getCoursewareCount($params, $this->crid);
        if ($count == 0) {
            return array(
                'folderlist' => $folderlist
            );
        }
        $coursewares = $roomCourseModel->getCoursewareList($params, $this->crid, $limit);
        $cwids = array_keys($coursewares);
        $folderids = array_column($coursewares, 'folderid');
        //读取出勤表
        $attendanceModel = new AttendancesModel();
        $attendancelist = $attendanceModel->getCoursewareAttendanceList($cwids, $studentids, $this->crid);
        //读取课程学生统计
        $serviceCount = $permisionModel->getServiceStudentsCount($folderids, $studentids, $this->crid);
        unset($cwids, $folderids);
        array_walk($coursewares, function(&$courseware, $index, $args) {
            $courseware['studentCount'] = $args['serviceCount'][$courseware['folderid']]['c'];
            $courseware['signCount'] = min($courseware['studentCount'], isset($args['attendancelist'][$courseware['cwid']]) ? $args['attendancelist'][$courseware['cwid']] : 0);
            $courseware['foldername'] = $args['folders'][$courseware['folderid']];
        }, array(
            'folders' => $folderlist,
            'serviceCount' => $serviceCount,
            'attendancelist' => $attendancelist
        ));
        return array(
            'folderlist' => $folderlist,
            'list' => $coursewares,
            'classname' => $classitem['classname'],
            'count' => $count
        );
    }
}
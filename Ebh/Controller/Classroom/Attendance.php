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

        $result['classes'] = $classes;
        $classIds = array_column($classes,'classid');


        //获取学生数据开始
        $courseModel = new CoursewareModel();

        $course = $courseModel->getCourseByCwid($this->cwid);

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
}
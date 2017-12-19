<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ClassesController extends Controller{
    public $classesModel;
    public function init(){
        parent::init();
        $this->classesModel = new ClassesModel();
    }
    public function parameterRules(){
        return array(
            'isExistsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'classname'  =>  array('name'=>'classname','require'=>true),
                'classid'  =>  array('name'=>'classid','type'=>'int','default'=>0),
            ),
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'roomType' => array('name'=>'roomType', 'type'=>'string','default'=>'edu'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            ),
            'editAction'   =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
                'classname'  =>  array('name'=>'classname'),
                'grade'  =>  array('name'=>'grade','type' => 'int', 'default'=>0)
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'classname'  =>  array('name'=>'classname','require'=>true),
                'grade'  =>  array('name'=>'grade','type' => 'int', 'default'=>0)
            ),
            'detailAction'   =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
            ),
            'getSchoolClassCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
            'delAction' =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
            ),
            'getClassTeacherByClassidAction'   =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
            ),
            'setClassesTeacherAction'   =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
                'tids'  =>  array('name'=>'tids','default'=>array(),'type'=>'array'),
            ),
            'setHeadTeacherIdAction'   =>  array(
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
                'headteacherid'  =>  array('name'=>'headteacherid','require'=>true,'type'=>'int'),
            ),
            'addClassStudentAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'getClassesAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
            'getChangePlanAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
            ),
            'changeClassAction'   =>  array(
                'type'  =>  array('name'=>'type','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'sourceid'  =>  array('name'=>'sourceid','require'=>true,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','type'=>'int','default'=>0),
                'starttime'  =>  array('name'=>'starttime','type'=>'int','default'=>0),
                'endtime'  =>  array('name'=>'endtime','type'=>'int','default'=>0),
                'classids'  =>  array('name'=>'classids','type'=>'array','default'=>array()),
                'pid'  =>  array('name'=>'pid','type'=>'int','default'=>0),
            ),
            'getClassInfoByCridAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'array'),
            )

        );
    }

    /**
     * 班级是否在指定网校中
     */
    public function isExistsAction(){
        return $this->classesModel->exists($this->crid,$this->classname,$this->classid);
    }

    /**
     * 添加学生到班级中
     */
    public function addClassStudentAction(){
        $parameters = array();
        $parameters['classid'] = $this->classid;
        $parameters['uid'] = $this->uid;
        $parameters['crid'] = $this->crid;

        return $this->classesModel->addClassStudent($parameters);
    }

    /**
     * 返回班级列表
     */
    public function listAction(){
        $parameters = array();
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }
        $parameters['crid'] = $this->crid;
        $parameters['roomType'] = $this->roomType;
        $total = $this->classesModel->getCount($parameters);
        $pageClass  = new Page($total,$this->pagesize);
        if ($this->roomType == 'edu') {
            $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        }
        $list = $this->classesModel->getList($parameters);
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 添加班级
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        $parameters['classname'] = $this->classname;
        $parameters['grade'] = $this->grade;
        return $this->classesModel->addClasses($parameters);
    }

    /**
     * 修改班级信息
     */
    public function editAction(){
        $parameters = array();

        if($this->classname !== null && !empty($this->classname)){
            $parameters['classname'] = $this->classname;
        }
        $parameters['grade'] = $this->grade;

        return $this->classesModel->editClasses($this->classid,$parameters);


    }

    /**
     * 读取教研组详细信息
     */
    public function detailAction(){

        return $this->classesModel->getDetail($this->classid);
    }

    /**
     * 获取网校可用班级数
     * @return mixed
     */
    public function getSchoolClassCountAction(){
        return $this->classesModel->getSchoolClassCount($this->crid);
    }

    /**
     * 删除班级
     * @return int
     */
    public function delAction(){
        $parameters = array();
        $parameters['classid'] = $this->classid;


        $res =  $this->classesModel->del($parameters);
        return $res;
    }

    /**
     * 获取指定班级的教师
     */
    public function getClassTeacherByClassidAction(){
        return $this->classesModel->getClassTeacherByClassid($this->classid);
    }

    /**
     * 设置教研组教师
     */
    public function setClassesTeacherAction(){
        $parameters = array();
        $parameters['classid'] = $this->classid;
        $parameters['tids'] = $this->tids;



        return $this->classesModel->setTeachers($parameters);
    }

    /**
     * 设置班级班主任ID
     */
    public function setHeadTeacherIdAction(){
        $parameters = array();
        $parameters['classid'] = $this->classid;
        $parameters['headteacherid'] = $this->headteacherid;
        return $this->classesModel->setHeadTeacherId($parameters);
    }

    /**
     * 修改升班配置
     * @return bool
     */
    public function changeClassAction(){
        $parameters = array();
        //$parameters['type'] = $this->type;
        $parameters['crid'] = $this->crid;
        $parameters['sourceid'] = $this->sourceid;
        $parameters['uid'] = $this->uid;
        if($this->classid > 0){
            $parameters['classid'] = $this->classid;
        }

        if($this->starttime > 0){
            $parameters['starttime'] = $this->starttime;
        }

        if($this->endtime > 0){
            $parameters['endtime'] = $this->endtime;
        }

        if(count($this->classids) > 0){
            $parameters['classids'] = $this->classids;
        }

        if($this->pid  > 0){
            $parameters['pid'] = $this->pid;
        }
        $changeClassesModel = new ChangeClassModel();
        $errcode = 0;

        return $changeClassesModel->changeClass($parameters,$errcode);


    }
    /**
     * 读取指定网校所有班级 适用 升班
     * @return bool
     */
    public function getClassesAction(){
        $changeClassesModel = new ChangeClassModel();

        return $changeClassesModel->getClasses($this->crid);
    }

    /**
     * 获取升班设置
     * @return bool
     */
    public function getChangePlanAction(){

        $changeClassesModel = new ChangeClassModel();

        return $changeClassesModel->getChangePlan($this->uid, $this->crid, $this->classid);
    }

    public function getClassInfoByCridAction(){
        $classesModel  = new ClassesModel();
        return $classids = $classesModel->getClassInfoByCrid($this->crid,$this->uid);
    }

}
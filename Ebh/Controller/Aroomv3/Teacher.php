<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class TeacherController extends Controller{
    public $teacherModel;
    public function init(){
        parent::init();
        $this->teacherModel = new TeacherModel();
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'simple' => array('name'=>'simple', 'type'=>'int','default'=>0)
            ),
            'isExistsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'uidIsExistsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'addRoomTeacherAction'   =>  array(
                'tid'  =>  array('name'=>'tid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'status'  =>  array('name'=>'status','require'=>true,'type'=>'int'),
                'cdateline'  =>  array('name'=>'cdateline','require'=>true,'type'=>'int'),
                'role'  =>  array('name'=>'role','require'=>true,'type'=>'int'),
                'mobile'  =>  array('name'=>'mobile'),
            ),
            'addAction'   =>  array(
                'username'  =>  array('name'=>'username','require'=>true),
                'password'  =>  array('name'=>'password','require'=>true),
                'realname'  =>  array('name'=>'realname','require'=>true),
                'mobile'  =>  array('name'=>'mobile'),
                'sex'  =>  array('name'=>'sex','require'=>true,'type'=>'int'),
            ),
            'editAction'   =>  array(
                'tid'  =>  array('name'=>'tid','require'=>true,'type'=>'int'),
                'realname'  =>  array('name'=>'realname'),
                'sex'  =>  array('name'=>'sex','type'=>'int'),
                'mobile'  =>  array('name'=>'mobile'),
                'password'  =>  array('name'=>'password'),
            ),
            'groupsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            ),
            'addGroupsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'upid'  =>  array('name'=>'upid','type'=>'int','default'=>0),
                'displayorder'  =>  array('name'=>'displayorder','type'=>'int','default'=>0),
                'groupname'  =>  array('name'=>'groupname','require'=>true),
                'summary'  =>  array('name'=>'summary','default'=>''),
            ),
            'editGroupsAction'   =>  array(
                'groupid'  =>  array('name'=>'groupid','require'=>true,'type'=>'int'),
                'groupname'  =>  array('name'=>'groupname'),
                'summary'  =>  array('name'=>'summary'),
            ),
            'getGroupsDetailAction'   =>  array(
                'groupid'  =>  array('name'=>'groupid','require'=>true,'type'=>'int'),
            ),
            'delGroupsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'groupid'  =>  array('name'=>'groupid','require'=>true,'type'=>'int'),
            ),
            'setGroupsTeacherAction'   =>  array(
                'groupid'  =>  array('name'=>'groupid','require'=>true,'type'=>'int'),
                'tids'  =>  array('name'=>'tids','type'=>'array','default'=>array()),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
            'groupExistsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'groupname'  =>  array('name'=>'groupname','require'=>true),
                'groupid'  =>  array('name'=>'groupid','type'=>'int','default'=>0),
            ),
			'delAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'activateAction' => array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid' => array('name'=>'uid','require'=>true,'type'=>'int'),
                'status' => array('name'=>'status','require'=>true,'type'=>'int')
            ),
            'updateMobileAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'tid' => array(
                    'name' => 'tid',
                    'type' => 'int',
                    'require' => true
                ),
                'mobile' => array(
                    'name' => 'mobile',
                    'type' => 'string'
                )
            ),
			'detailAction' => array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid' => array('name'=>'uid','require'=>true,'type'=>'int')
            ),

        );
    }

    /**
     * 添加教师
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['username'] = $this->username;
        $parameters['password'] = $this->password;
        $parameters['realname'] = $this->realname;
        if (!empty($this->mobile)) {
            $parameters['mobile'] = $this->mobile;
        }
        $parameters['sex'] = $this->sex;
        $parameters['dateline'] = SYSTIME;
        return $this->teacherModel->addTeacher($parameters);
    }

    /**
     * 修改教师信息
     */
    public function editAction(){
        $parameters = array();
        if($this->realname !== null && !empty($this->realname)){
            $parameters['realname'] = $this->realname;
        }

        if($this->sex !== null){
            $parameters['sex'] = $this->sex;
        }

        if($this->mobile !== null){ // !empty($this->mobile)
        $parameters['mobile'] = $this->mobile;
        }

        if($this->password !== null && !empty($this->password)){
            $parameters['password'] = $this->password;
        }

        return $this->teacherModel->editTeacher($this->tid,$parameters);


    }

    /**
     * 读取网校教师列表
     * @return array
     */
    public function listAction(){
		$parameters = array();
		if(isset($this->q)){
            $parameters['q'] = $this->q;
        }
        $total = $this->teacherModel->getRoomTeacherCount($this->crid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        if ($this->simple == 0) {
            $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        }

        $list = $this->teacherModel->getRoomTeacherList($this->crid,$parameters);
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 教师是否在指定网校中
     */
    public function isExistsAction(){
        $roomTeacherModel = new RoomTeacherModel();
        return $roomTeacherModel->exists($this->crid,$this->uid);
    }

    /**
     * 教师是否在指定网校中
     */
    public function uidIsExistsAction(){
        $roomTeacherModel = new RoomTeacherModel();
        return $roomTeacherModel->uidIsExists($this->uid);
    }

    /**
     * 新增网校教师
     * @return mixed
     */
    public function addRoomTeacherAction(){
        $parameters['tid'] = $this->tid;
        $parameters['crid'] = $this->crid;
        $parameters['status'] = $this->status;
        $parameters['cdateline'] = $this->cdateline;
        $parameters['role'] = $this->role;
        $parameters['mobile'] = $this->mobile;
        $roomTeacherModel = new RoomTeacherModel();

        return $roomTeacherModel->add($parameters);
    }
	
	/**
     * 删除网校教师
     * 
     */
	public function delAction(){
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
		$roomTeacherModel = new RoomTeacherModel();
		return $roomTeacherModel->delRoomTeacher($param);
	}


    /**--------------------------教研组-----------------------------------------**/


    /**
     * 读取教研组列表
     * @return array
     */
    public function groupsAction(){
        $parameters = array();
        $tgroupsModel = new TgroupsModel();
        $parameters['crid'] = $this->crid;
        $total = $tgroupsModel->getCount($parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $tgroupsModel->getList($parameters);
        //读取教师和教研组关联联系

        $teachergroupsModel = new TeachergroupsModel();

        $teacherList = $teachergroupsModel->getList($parameters);
        $infoArr = array();
        foreach ($teacherList as $tgroup) {
            $key1 = 'groupid_tid_'.$tgroup['groupid'];
            $key2 = 'groupid_tname_'.$tgroup['groupid'];
            if(!array_key_exists($key1, $infoArr)){
                $infoArr[$key1] = array();
                $infoArr[$key1][]=$tgroup['tid'];
                $infoArr[$key2][]= !empty($tgroup['realname'])?$tgroup['realname']:$tgroup['username'];

            }else{
                $infoArr[$key1][]=$tgroup['tid'];
                $infoArr[$key2][]= !empty($tgroup['realname'])?$tgroup['realname']:$tgroup['username'];
            }
        }

        foreach ($list as $key=>$group){

            $group['teacher'] = array(
                'tid'   =>  $infoArr['groupid_tid_'.$group['groupid']],
                'tname' =>  $infoArr['groupid_tname_'.$group['groupid']] ? $infoArr['groupid_tname_'.$group['groupid']] : ''
            );
            $list[$key] = $group;
        }

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 读取教研组详细信息
     */
    public function getGroupsDetailAction(){

        $tgroupsModel = new TgroupsModel();

        return $tgroupsModel->getDetail($this->groupid);
    }

    /**
     * 添加教研组
     * @return int
     */
    public function addGroupsAction(){
        $parameters = array();
        $parameters['upid'] = $this->upid;
        $parameters['groupname'] = $this->groupname;
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['displayorder'] = $this->displayorder;
        $parameters['summary'] = $this->summary;

        $tgroupsModel = new TgroupsModel();

        return $tgroupsModel->add($parameters);
    }

    /**
     * 修改教研组信息
     * @return int
     */
    public function editGroupsAction(){
        $parameters = array();
        if($this->groupname !== null){
            $parameters['groupname'] = $this->groupname;
        }

        if($this->summary !== null){
            $parameters['summary'] = $this->summary;
        }

        $tgroupsModel = new TgroupsModel();

        return $tgroupsModel->edit($parameters,array('groupid'=>$this->groupid));

    }

    /**
     * 删除教研组信息
     * @return int
     */
    public function delGroupsAction(){
        $parameters = array();
        $parameters['groupid'] = $this->groupid;
        $tgroupsModel = new TgroupsModel();

        $res =  $tgroupsModel->del($parameters);
        if(!empty($res)){
            //删除分组下面关联的教师
            $teacherGroupsModel = new TeachergroupsModel();
            //参数组织
            $param = array(
                'groupid'=>$parameters['groupid'],
                'crid'=>$this->crid
            );
            $teacherGroupsModel->del($param);
        }
        return $res;
    }

    /**
     * 设置教研组教师
     */
    public function setGroupsTeacherAction(){
        $parameters = array();
        $parameters['groupid'] = $this->groupid;
        $parameters['tids'] = $this->tids;
        $parameters['crid'] = $this->crid;

        $teachergroupsModel = new TeachergroupsModel();

        return $teachergroupsModel->setTeachers($parameters);
    }

    /**
     * 教研组名称是否存在
     */
    public function groupExistsAction(){
        $teachergroupsModel = new TeachergroupsModel();
        return $teachergroupsModel->exists($this->crid,$this->groupname,$this->groupid);
    }

    /**
     * 禁用/解禁教师
     * @return mixed
     */
    public function activateAction() {
        return $this->teacherModel->activate($this->status, $this->uid, $this->crid);
    }

    /**
     * 修改教师电话
     */
    public function updateMobileAction() {
        $model = new RoomTeacherModel();
        return $model->updateMobile($this->mobile, $this->tid, $this->crid);
    }
	
	/**
     * 教师详情
     */
	public function detailAction(){
		$model = new RoomTeacherModel();
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
        return $model->getDetail($param);
	}
}
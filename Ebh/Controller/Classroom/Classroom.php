<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:08
 */
class ClassroomController extends Controller{

    public $classRoomModel;
    public function init(){
        parent::init();
        $this->classRoomModel = new ClassRoomModel();
    }
    public function parameterRules(){
        return array(
            'detailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1)
            ),
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1)
            ),
            'userExistAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'allAction' =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'q'  =>  array('name'=>'q','default'=>''),
                'lastcrid'  =>  array('name'=>'lastcrid','type'=>'int','default'=>0),
            )
        );
    }

    /**
     * 获取所有网校
     */
    public function allAction(){
        $classroomModel = new ClassRoomModel();
        if($this->uid > 0 && $this->lastcrid == 0) {//只有第一页时才读取自己的网校
            $roomlist = $classroomModel->getUserTvRoomList($this->uid,$this->q);
        }else{
            $roomlist = $classroomModel->getTvRoomList(array('q'=>$this->q,'lastcrid'=>$this->lastcrid,'limit'=>'limit 20'));
        }
        return $roomlist;
    }

    /**
     * 用户是否在网校中
     * @return bool
     */
    public function userExistAction(){
        $roomUserModel = new RoomUserModel();

        return $roomUserModel->isAlumni($this->crid,$this->uid);
    }
    /**
     * 获取指定用户的网校列表
     * @return array
     */
    public function listAction(){
        $userModel = new UserModel();

        $userInfo = $userModel->getUserByUid($this->uid);

        if(!$userInfo){
            return returnData('0','用户不存在');
        }

        if($userInfo['groupid'] == 6){
            //学生
            $classRooms = $this->classRoomModel->getStudentClassRoomListByUid($userInfo['uid']);
        }else{
            //老师
            $classRooms = $this->classRoomModel->getTeacherClassRoomListByUid($userInfo['uid']);
        }

        return returnData(1,'',$classRooms);
    }


    /**
     * 返回指定网校详细信息
     * @return array
     */
    public function detailAction(){
        $classRoomModel = new ClassRoomModel();

        $classroomInfo = $this->classRoomModel->getModel($this->crid);

        if(!$classroomInfo){
            return returnData(0,'指定网校不存在');
        }

        return returnData(1,'',$classroomInfo);
    }
}
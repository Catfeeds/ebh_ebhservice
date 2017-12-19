<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:31
 */
class CourseController extends Controller{

    public $courseModel;
    public function init(){
        parent::init();
        $this->courseModel = new CoursewareModel();
    }
    public function parameterRules(){
        return array(
            'hasStudyAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
                'pagesize'  =>  array('name'=>'pagesize','default'=>20),
            ),
            'detailAction'  =>  array(
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>false,'default'=>0),
                'crid'  =>  array('name'=>'crid','require'=>false,'default'=>0),
            ),
            'getCwidsAction' =>array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>0)
            ),
            'getAssignStimeAction'=>array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>0)
            ),
            'newCourseAction'=>array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            )
        );
    }


    public function newCourseAction(){
        $crid = $this->crid;
        $uid = $this->uid;
        $classroomModel = new ClassRoomModel();
        $roomInfo = $classroomModel->getModel($this->crid);
        //读取班级课程
        $courseModel = new CoursewareModel();
        $folderModel = new FolderModel();
        if($roomInfo['isschool'] != 7){
            //普通网校按班级读取课程
            $classStudent = new ClassstudentsModel();
            $classId = $classStudent->getClassIdByUid($uid,$roomInfo['crid']);

            if(!$classId){
                return array();
            }

            if(!$classId){
                $myfolderlist = array();
            }
            $parameters['classid'] = $classId;

            $myfolderlist = $folderModel->getClassStudentFolders($parameters);
        }else{
            $myperparam = array('uid'=>$uid,'crid'=>$crid,'filterdate'=>1);
            $myfolderlist = $folderModel->getUserPayFolderList($myperparam);
        }
        $myFolderIds = array();
        if(!empty($myfolderlist)){
            $myFolderIds = array_column($myfolderlist,'folderid');
        }


        $param = array();
        $param['folderids'] = implode(',',$myFolderIds);
        $newcwlist = array();
        $todaylist = array();
        $classid = runAction('Member/User/getStudentClassId',array('uid'=>$uid,'crid'=>$crid));
        if(!empty($param['folderids'])){
            $param['status'] = 1;
            $param['limit'] = 20;
            $param['order'] = 'c.truedateline asc';
            $param['truedatelineto'] = strtotime('today')+86400*7;//七天内
            $param['truedatelinefrom'] = strtotime('today');
            $param['power'] = 0;
            if (!empty($classid)) {
                $param['classids'] = $classid;
            }
            $cwlist = $courseModel->getNewCourseList($param);

            if(!empty($cwlist)){
                foreach ($cwlist as $cw){
                    $dayis = date('Y-m-d',$cw['truedateline']);
                    if($dayis == date('Y-m-d')){
                        $dayis = 'z今天';
                    }elseif($dayis == date('Y-m-d',SYSTIME+86400)){
                        $dayis = 'y明天';
                    }elseif($dayis == date('Y-m-d',SYSTIME-86400)){
                        $dayis = 'x昨天';
                    }
                    $newcwlist[$dayis][] = $cw;
                }

                if(!empty($newcwlist['z今天'])){
                    //今天的单独处理下
                    $totaycourses = $newcwlist['z今天'];
                    $todaylist = $this->sortTodayCourse($totaycourses);
                    unset( $newcwlist['z今天']);
                    //array_merge
                    $newcwlist['z今天'] = array_merge_recursive($todaylist['staring'],$todaylist['coming'],$todaylist['expired']);
                }
                //正在上课->即将开课->已结束（今天）->明天->昨天->[日期]->[日期]...排序
                krsort($newcwlist);
            }
        }
        return $newcwlist;

    }
    /**
     * 今天的课程 排序处理
     * 按照 正在上课->即将开课->已结束
     */
    private function sortTodayCourse($courselist){
        $todaylist = array('staring'=>array(),'coming'=>array(),'expired'=>array());
        if(empty($courselist))
            return false;
        foreach ($courselist as $course){
            $starttime = $course['truedateline'];//开始时间
            $cwlenth = $course['cwlength'];//课件时长
            $nowtime = SYSTIME;//当前时间
            if($nowtime <= $starttime){
                //即将开始
                $course['todaysort'] = 'coming';
                $todaylist['coming'][] = $course;
            }elseif(!empty($cwlenth) && ($nowtime>=$starttime) && (($starttime+$cwlenth) >= $nowtime) && (empty($course['endat']) || $course['endat']>=$nowtime)){
                //正在上课
                $course['todaysort'] = 'staring';
                $todaylist['staring'][] = $course;
            }elseif($nowtime > ($starttime+$cwlenth) || (!empty($course['endat']) && $nowtime>$course['endat'])){
                //已结束
                $course['todaysort'] = 'expired';
                $todaylist['expired'][] = $course;
            }
        }

        return $todaylist;
    }
    /**
     * 读取用户已学课件列表
     * @return array
     */
    public function hasStudyAction(){
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['normal'] = 1;
        $playlogMoldel = new PlayLogModel();

        $total = $playlogMoldel->getCourseCountByUid($parameters);
        $pageClass  = new Page($total);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $playlogMoldel->getCourseListByUid($parameters);

        return returnData(1,'',array('total'=>$total,'list'=>$list));
    }


    /**
     * 读取指定课程的课件列表
     * @return array
     */
    public function listAction(){
        $parameters['crid'] = $this->crid;
        $parameters['folderid'] = $this->folderid;
        $total = $this->courseModel->getListCountByFolderId($parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->courseModel->getListByFolderId($parameters);

        return returnData(1,'',array('total'=>$total,'list'=>$list));

    }

    /**
     * 获取指定课件详情
     */
    public function detailAction(){
        $course = $this->courseModel->getCourseByCwid($this->cwid);
        if(!$course){
            return returnData(0,'课件信息不存在');
        }

        $notice = $this->courseModel->getNotice($this->cwid);
        $course['notice'] = $notice ? $notice : '';
        /**
         * 如果传了CRID 和 uid 获取用户的课件信息
         */
        $playlogMoldel = new PlayLogModel();
        if($this->uid > 0 && $this->crid > 0){
            $course['note'] = '';
            $parameters['crid'] = $this->crid;
            $parameters['uid'] = $this->uid;
            $parameters['cwid'] = $this->cwid;
            $noteModel = new NoteModel();
            $note = $noteModel->getNote($parameters);
            if($note){
                $course['note'] = $note['ftext'];
            }


            $studyTime = $playlogMoldel->getCwStudyListByUser(array(
                'cwid'  =>  $this->cwid,
                'uids'  =>  $this->uid
            ));
            if($studyTime){
                $course['has_done'] = $studyTime[$this->uid]['ltime'] / $course['cwlength'] * 100;
                $course['has_done'] = $course['has_done'] > 100 ? 100 : $course['has_done'];
            }else{
                $course['has_done'] = 0;
            }
        }




        //获取当前课件学习人数

        $studyCount = $playlogMoldel->getStudyCountByCwid($this->cwid);
        $course['study_count'] = $studyCount;

        //获取课程详情
        $folderModel = new FolderModel();
        $folder = $folderModel->getFolderById($course['folderid']);

        $course['folder_info'] = array(
            'foldername'    =>  $folder['foldername'],
            'summary'   =>  $folder['summary']
        );
        return returnData(1,'',$course);

    }

    /**
     * 获取用户开通的课件ID
     * @return array
     */
    public function getCwidsAction(){
        $userPermissionModel = new UserpermisionsModel();
        $cwlist = $userPermissionModel->getUserPayCwList(array('uid'=>$this->uid,'crid'=>$this->crid,'filterdate'=>1));
        $cwids = array_column($cwlist,'cwid');
        return $cwids;
    }
    
    
    /*
     * 获取word等课件翻页需要等待的时间,主要是针对国土厅网校
     */
    public function getAssignStimeAction(){
        //word等課件每頁等待時間
        $stime = 0;
        $cwid = $this->cwid;
        $crid = $this->crid;
        $course = $this->courseModel->getCourseByCwid($this->cwid);
        $arr = explode('.',$course['cwurl']);
        $type = strtolower($arr[count($arr)-1]);
        if(!$course){
            return returnData(0,'课件信息不存在');
        }
        $sysModel = new SystemSettingModel();
        $setting = $sysModel->getModel($crid);
        if(in_array($type,array('doc','docx'))!==false){
            $jsonobj = json_decode($setting['creditrule']);
            if(!empty($jsonobj) && !empty( $jsonobj->notvideo)){
                if($jsonobj->notvideo->on==1){
                    $needtime = $jsonobj->notvideo->needtime;
                }
            }
           
            //从课件设置中读取
            $delaytime = !empty($course['delaytime']) ? intval($course['delaytime']) :  0;
            $stime = !empty($delaytime) ? $delaytime : (!empty($needtime) ? intval($needtime) : 0 );
        }

        return returnData(1,'成功获取翻页等待时间',array('stime'=>$stime));
    }
 
}
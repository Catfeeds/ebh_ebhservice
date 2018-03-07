<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:34
 */
class FolderController extends Controller{

    public $folderModel;
    public function init(){
        parent::init();
        $this->folderModel = new FolderModel();
    }
    public function parameterRules(){
        return array(
				'listAction'   =>  array(
					'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
					'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
					'type'  =>  array('name'=>'type','require'=>false,'type'=>'int','default'=>1),//1 获取所有课程 0获取我的课程
					'q'  =>  array('name'=>'q','require'=>false,'default'=>''),//课程查询关键字
					'pid'  =>  array('name'=>'pid','require'=>false,'type'=>'int','default'=>-1),//服务包
					'sid'  =>  array('name'=>'sid','require'=>false,'type'=>'int','default'=>-1),//服务包分类
				),
				'permissionAction'   =>  array(
					'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
					'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
					'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
				),
                'canpayAction'   =>  array(
                    'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                    'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
                ),
				'getselectedourseAction' =>array(
					'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
					'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>0),
					'itemid' =>  array('name'=>'itemid','require'=>true,'type'=>'string'),
				),
				'getFoldersAction' =>array(
					'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
					'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>0)
				),
				'getFolderByIdAction' =>array(
					'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
					'crid'  =>  array('name'=>'crid','type'=>'int','default' => 0),
				),
				'getFolderDirectoriesAction' =>array(
	                'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
	                'crid'  =>  array('name'=>'crid','type'=>'int','default' => 0),
	                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
	                'q'  =>  array('name'=>'q','default'=>''),
	                'page'  =>  array('name'=>'page','type'=>'int','default'=>0)
            	),
				'hotAction' => array(
					'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>0),
					'crid' => array('name' => 'crid', 'require' => TRUE, 'type' => 'int'),
                    'q'  =>  array('name'=>'q','require'=>false,'default'=>''),//课程查询关键字
                    'pid'  =>  array('name'=>'pid','require'=>false,'type'=>'int','default'=>-1),//服务包
                    'sid'  =>  array('name'=>'sid','require'=>false,'type'=>'int','default'=>-1),//服务包分类
					'num' => array('name' => 'num', 'require' => TRUE, 'type' => 'int'),
                    'onlylist' => array('name' => 'onlylist',  'type' => 'int','default'=>0),
				),
                'studyinfoAction' => array(
                    'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                    'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                    'folderids' => array('name' => 'folderids', 'require' => TRUE, 'type' => 'array'),
                ),
                'getFolderStudentAction' => array(
                    'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                    'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
                    'cwid'  =>  array('name'=>'cwid','type'=>'int','default'=>0),
                ),
        );
    }

    
    /**
     * 获取课程的学生
     * 分成网校获取拥有课程权限的学生
     * 非分成网校获取该课程所在班级学生
     */
    public function getFolderStudentAction(){
        $classroomMoldel = new ClassRoomModel();
        $roominfo = $classroomMoldel->getModel($this->crid);
        $classId = '';
        if($this->cwid > 0){
            $roomcourseModel = new RoomCourseModel();

            $roomcourse = $roomcourseModel->getClassidByCwid($this->cwid,$this->crid);
            $classId = $roomcourse['classids'];

        }
        $userpermisionMoldel = new UserpermisionsModel();
        $userList = $userpermisionMoldel->getUserList(array('crid'=>$this->crid,'folderid'=>$this->folderid,'classid'=>$classId));
        return $userList;

    }

    /**
     * 获取批量获取指定课程的学习信息
     * sumscore 学生在该课程中获得的学分
     * percent 课程学习进度
     */
    public function studyinfoAction(){

        $folderModel = new FolderModel();


        $coursewarelist = $folderModel->getCWByFolderid(array('folderid'=>implode(',',$this->folderids),'limit'=>10000));
        $foldercwlist = array();
        foreach($coursewarelist as $cw){
            $foldercwlist[$cw['folderid']][] = $cw;
        }
        $scorelist = runAction('Classroom/Score/folderScore',array('crid'=>$this->crid,'uid'=>$this->uid,'folderids'=>implode(',',$this->folderids)));


        foreach ($foldercwlist as $folderid => $cwList){
            $cwids = array_column($cwList,'cwid');
            $cwprogress = $this->_getProgress($this->uid,$cwids);
            $percent = intval(array_sum($cwprogress)/count($cwprogress));
            $scorelist[$folderid]['percent'] = $percent;
        }

        //结果整理
        $result = array();
        foreach ($this->folderids as $folderid){
            $result[$folderid]['sumscore'] = 0;
            $result[$folderid]['percent'] = 0;
            if(isset($scorelist[$folderid]) && isset($scorelist[$folderid]['sumscore'])){
                $result[$folderid]['sumscore'] = $scorelist[$folderid]['sumscore'];
            }
            if(isset($scorelist[$folderid]) && isset($scorelist[$folderid]['percent'])){
                $result[$folderid]['percent'] = $scorelist[$folderid]['percent'];
            }

        }
        return $result;
    }

    /**
     * 批量获取课件学习进度
     * @param $uid
     * @param $cwids
     * @return array
     */
    private function _getProgress($uid,$cwids){
        $loglist = runAction('Study/Log/list',array('uid'=>$uid,'cwids'=>implode(',',$cwids)));
        $list = array();
        foreach ($loglist as $cwid => $log) {
            $totalltime = !empty($log['totalltime'] ) ? $log['totalltime'] : 0;
            $percent = empty($log['ctime']) ? 0 : $log['ltime'] / $log['ctime'];
            if ($percent > 0.9)
                $percent = 1;
            $percent = floor($percent * 100);
            $list[$cwid] = $percent;
        }
        return $list;
    }

    /**
     * 热门课程
     */
    public function hotAction() {
        $userModel = new UserModel();

        $userInfo = $userModel->getUserByUid($this->uid);
        //$folderList =  $this->folderModel->hotList($this->crid);

        $parameters['crid'] = $this->crid;
        $parameters['pagesize'] = 1000;
        $parameters['order'] = ' f.viewnum desc,f.displayorder asc,f.folderid';
        $parameters['q'] = $this->q;
        $parameters['pid'] = $this->pid;
        $parameters['sid'] = $this->sid;
        $parameters['nosubfolder'] = 1;
        $parameters['pstatus'] = 1;
        $parameters['ishide'] = 0;
        $list = $this->folderModel->getFolderLists($parameters);


        if(!empty($list)){
            array_walk($list, function(&$v, $k) {
                $viewnum = Ebh()->cache->getRedis()->hget('folder'.'viewnum', $v['folderid']);
                if (!empty($viewnum)) {
                    $v['viewnum'] = $viewnum;
                }
            });

            $viewnumArr = array_column($list, 'viewnum');
            array_multisort($viewnumArr, SORT_NUMERIC, SORT_DESC, $list);

            if (count($list) > $this->num && $this->num > 0) {
                $list = array_slice($list, 0, $this->num);
            }
        }


        if($this->onlylist == 0){
            $folderUtil = new FolderUtil();

            $list = $folderUtil->init($list,$userInfo['uid'],$this->crid)->injectPermission(false)->getResult();
        }



        return returnData(1,'',$list);


    }
    /**
     * 是否拥有权限
     */
    public function permissionAction(){
        $userpermisionsModel = new UserpermisionsModel();
        $result = $userpermisionsModel->check($this->crid,$this->uid,$this->folderid);
        return $result;
    }

    /**
     * 查看指定课程是否可以购买
     * @return bool
     */
    public function canpayAction(){
        $payitemModel = new PayitemModel();
        $result = $payitemModel->getItemsByFolderIds($this->folderid,$this->crid);

        if(!$result){
            return false;
        }
        $res['itemid'] = $result[0]['itemid'];

        if($result[0]['cannotpay'] == 1){
            $res['canpay'] = false;
        }else{
            $res['canpay'] = true;
        }


        return $res;
    }
    /**
     * 读取网校课程
     * @return array
     *
     */
    public function listAction(){
        $userModel = new UserModel();

        $userInfo = $userModel->getUserByUid($this->uid);
        $classroomModel = new ClassRoomModel();
        $roomInfo = $classroomModel->getModel($this->crid);
        if(!$userInfo){
            return returnData('0','用户不存在');
        }
        $parameters['q'] = $this->q;
        $parameters['pid'] = $this->pid;
        $parameters['sid'] = $this->sid;
        $parameters['nosubfolder'] = 1;
        if($this->type == 0){

            //获取我的课程
            if($userInfo['groupid'] == 5){
                //如果是老师 读取老师课程
                $parameters['crid'] = $this->crid;
                $parameters['tid'] = $userInfo['uid'];

                $list = $this->folderModel->getTeacherFolders($parameters);

            }else{
                //学生读取学生课程
                if($roomInfo['isschool'] != 7){
                    //普通网校按班级读取课程
                    $classStudent = new ClassstudentsModel();
                    $classId = $classStudent->getClassIdByUid($userInfo['uid'],$roomInfo['crid']);

                    if(!$classId){
                        return returnData(1,'',array());
                    }
                    $parameters['classid'] = $classId;

                    $list = $this->folderModel->getClassStudentFolders($parameters);
                }else{

                    //分成网校按开通读取课程
                    /*$parameters['uid'] = $userInfo['uid'];
                    $list = $this->folderModel->getStudentFolders($parameters);*/

                    //获取用户已开通的课程
                    $myperparam = array('uid'=>$userInfo['uid'],'crid'=>$this->crid,'filterdate'=>1);
                    $myfolderlist = $this->folderModel->getUserPayFolderList($myperparam);
                    $fid_in = array();
                    foreach ($myfolderlist as $fkey => $folder) {
                        if(!in_array($folder['folderid'],$fid_in)){
                            $fid_in[] = $folder['folderid'];
                        }

                    }
                    if(empty($fid_in)){
                        $list =  array();
                    }else{
                        $parameters = array(
                            'folderid'=>implode(',',$fid_in),
                            'pagesize'=>1000,
                            'power'=>0,
                            'q' =>  $this->q,
                            'pid'   =>  $this->pid,
                            'sid'   =>  $this->sid
                        );
                        $list = $this->folderModel->getFolders($parameters);

                    }

                }
            }
        }else{

            if($roomInfo['isschool'] == 7){
                //通过服务包获取
                $payitemModel = new PayitemModel();
                $folderlist = $payitemModel->getItemList(array('pagesize'=>1000,'crid'=>$this->crid));

                $fid_in = array();
                foreach ($folderlist as $fkey => $folder) {
                    $fid_in[] = $folder['folderid'];
                }


                if(empty($fid_in)){
                    $list =  array();
                }else{

                    $parameters = array(
                        'folderid'=>implode(',',$fid_in),
                        'pagesize'=>1000,
                        'power'=>0,
                        'q' =>  $this->q,
                        'pid'   =>  $this->pid,
                        'sid'   =>  $this->sid
                    );

                    $list = $this->folderModel->getFolders($parameters);
                }
            }else{
                $parameters['crid'] = $this->crid;
                $parameters['pagesize'] = 1000;
                $list = $this->folderModel->getFolders($parameters);
            }

        }


        /**
         * 开始对课程注入权限
         */
        $folderUtil = new FolderUtil();

        $list = $folderUtil->init($list,$userInfo['uid'],$this->crid)->injectPermission()->getResult();


        /**
         * 企业选课信息
         * 如果网校类型是7 并且 是学生 读取自己的课程时 增加企业选课的课程
         */
        if($roomInfo['isschool'] == 7 && $userInfo['groupid'] == 6 && $this->type == 0){
            $schsourceModel = new SchsourceModel();
            //var_dump($selectedItems);exit;
            $selectedItems = $this->folderModel->getUserPaySchSourceFolderList(array('uid'=>$this->uid,'crid'=>$roomInfo['crid']));
            $schsourceList = array();
            if(!empty($selectedItems)){
                foreach ($selectedItems as $item){
                    $item['permission'] = 1;
                    $schsourceList[$item['sourceid']]['schsources'] = array(
                        'sourceid'  =>  $item['sourceid'],
                        'name'  =>  $item['name']
                    );
                    $schsourceList[$item['sourceid']]['list'][] = $item;
                }
            }

            $list['myschsources'] = array_values($schsourceList);
        }

        return returnData(1,'',$list);

    }
    
    
    /**
     * 获取网校自选课程
     */
    public function getselectedourseAction(){
        $crid = $this->crid;
        $itemid = $this->itemid;
        $uid = $this->uid;
        $check = true;//是否验证权限
        $itemidarr = explode(',', $itemid);
        //log_message(var_export($itemidarr,true));
        //验证数据是否合法
        if(empty($crid) || empty($crid) ||!is_array($itemidarr)){
            returnData(0,'参数错误',array());
            exit;
        }
        $itemidarr = array_unique($itemidarr);
        //验证用户和网校信息
        $userModel = new UserModel();
        $classroomModel = new ClassRoomModel();
        $roomInfo = $classroomModel->getModel($this->crid);
        
        if(empty($roomInfo)){
            returnData(0,'参数错误',array());
            exit;
        }
        //获取课程列表
        $payitemModel = new PayitemModel();

        $param = array(/*'crid'=>$crid,*/'itemidlist'=>implode(',', $itemidarr));
        $count = $payitemModel->getItemListFolderCount($param);
        if($count <= 0){
            returnData(0,'暂无数据',array());
            exit;
        }
        $itemlist = $payitemModel->getItemFolderList($param);
        if (!empty($itemlist)) {
            array_walk($itemlist, function(&$item, $itemid, $crid) {
                if ($item['crid'] != $crid) {
                    $item['cannotpay'] = 0;
                }
            }, $crid);
        }
        
        //验证用户是否有权限
        if(!empty($uid)){
            $userInfo = $userModel->getUserByUid($this->uid);
            if(($userInfo['groupid'] == 5) &&  ($roomInfo['uid'] == $userInfo['uid'])){
                //该网校管理员 负责网校装扮 不验证权限
                $check = false;
            }
        }

        if($check){
            //获取权限信息
            $permissionModel = new UserpermisionsModel();
            $sparam = array('itemids'=>implode(',', $itemidarr),'uid'=>$uid,'crid'=>$crid);
            $permisseionlist = $permissionModel->getPermissionByItemIds($sparam);
            foreach ($itemlist as &$item){
                if(!empty($permisseionlist[$item['itemid']])){
                    $item['haspower'] = true;
                }else{
                    $item['haspower'] = false;
                }
            }
        }
        
        $itemlist = array_coltokey($itemlist,'itemid');
        
        return returnData(1,'数据获取成功',$itemlist);
        
    }

    /**
     * 获取用户已开通课程
     */
    public function getFoldersAction(){
        $userpermissionModel = new UserpermisionsModel();
        $myfolderlist = $userpermissionModel->getUserPayFolderList(array('uid'=>$this->uid,'crid'=>$this->crid,'filterdate'=>1));
        $folderids = array();
        foreach($myfolderlist as $f){
            $folderids[]= $f['folderid'];
        }
        $classroomModel = new ClassRoomModel();
        $folderModel = new FolderModel();
        $roomInfo = $classroomModel->getModel($this->crid);

        if($roomInfo['isschool'] == 7){
            //全校免费课程
            $ruModel = new RoomUserModel();
            $userin = $ruModel->getroomuserdetail($this->crid,$this->uid);
            if(!empty($userin)){
                $schoolfreelist = $folderModel->getFolderlist(array('crid'=>$this->crid,'isschoolfree'=>1,'limit'=>1000));
                foreach($schoolfreelist as $f){
                    $folderids[]= $f['folderid'];
                }
            }
        }else{
            $classModel = new ClassesModel();
            $classCourseModel = new ClasscourseModel();
            if($roomInfo['domain'] == 'lcyhg'){//绿城育华 一个学生可以多个班级
                $needlist = TRUE;
                $myclass = $classModel->getClassByUid($this->crid,$this->uid,$needlist);
                $myclassidarr = array_column($myclass,'classid');

                $myclassid = implode($myclassidarr,',');
                $classfolders = $classCourseModel->getFolderidsByClassid($myclassid);
            }else{
                $myclass = $classModel->getClassByUid($this->crid,$this->uid);
                $myclassid = empty($myclass['classid']) ? 0 : $myclass['classid'];
                $classfolders = $classCourseModel->getFolderidsByClassid($myclassid);
            }

            if(!empty($classfolders)){//获取课程基础信息
                foreach ($classfolders as $fd){
                    $folderids[] = $fd['folderid'];
                }
            }

            //没有关联的，按老策略，老师的课程
            if(empty($folderids)){
                $queryarr = array();
                $queryarr['crid'] = $this->crid;
                if(!empty($myclassid))
                    $queryarr['classid'] = $myclassid;
                else{
                    // header('Location:'.geturl('myroom/college/allcourse'));
                    // exit;
                }
                if(!empty($queryarr['classid'])){
                    /*if(!empty($myclass['grade']))
                        $queryarr['grade'] = $myclass['grade'];*/
                    $queryarr['pagesize'] = 1000;
                    $queryarr['order'] = '  displayorder asc,folderid desc';
                    $folders = $folderModel->getClassFolder($queryarr);
                    if (!empty($folders)) {
                        foreach ($folders as $key => $value) {
                            $folderids[] = $value['folderid'];
                        }
                    }
                }
            }
        }


        return $folderids;

    }
    /**
     * 根据课程编号获取课程详情信息
     * @param int $folderid 课程编号
     * @param int $crid 教室编号
     * @return array 课程信息数组
     */
    public function getFolderByIdAction() {
        if(empty($this->crid)){
            $this->crid = 0;
        }
        return $this->folderModel->getFolderById($this->folderid,$this->crid);
    }
    
    /**
     *获取某课程下面课件详情
     */
    public function getFolderDirectoriesAction() {
        if(empty($this->crid)){
            $this->crid = 0;
        }
        return $this->folderModel->getFolderDirectories($this->crid, $this->folderid, $this->page, $this->pagesize,$this->q);
    }
}
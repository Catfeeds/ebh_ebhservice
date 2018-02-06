<?php

/**
 * @describe:学分folderid批量更新
 * @Author:tzq
 * @Class CreditLogTaskController
 * @CreateTime 2018/01/09
 */
class CreditLogTaskController extends Controller{

    public function init(){
        Ebh()->filter = 'Filter_Server';    //验证IP来源
        parent::init();
    }
    const folderids = '12867,12868,12869,12872,12873,12871';//国土课程id

    /**
     * @describe:更新folderid为0的数据
     * @Author:tzq
     * @Date:2018/01/09
     * @param void
     * @return void
     */
    public function doTaskAction(){
        //查询课件列表
        $roomCourseModel = new RoomCourseModel();
        $courseList      = $roomCourseModel->getCwList(['folderids' => self::folderids]);
        //课件列表

        if (!empty($courseList)) {
            $folderList = [];
            foreach ($courseList as $item){
                $folderid = $item['folderid'];
                if(isset($folderList[$folderid])){
                    array_push($folderList[$folderid],$item['cwid']);
                }else{
                    $folderList[$folderid] = [];
                    array_push($folderList[$folderid],$item['cwid']);
                }
            }
            //log_message('课件列表：'.json_encode($folderList));

            if (!empty($folderList)) {
                //执行更新
                $studyMolde = new StudycreditlogsModel();
                $ret        = $studyMolde->updateFolderid($folderList);
                if ($ret !== false) {
                    log_message('更新学分表folderid成功,受影响行数(' . $ret. ')');
                    return ['successNum'=>$ret];
                } else {
                    log_message('更新学分表folderid失败' . json_encode($ret));
                    return ['errorNum'=>'更新失败'];
                }
            } else {
                log_message('学分表无folderid需要更新');
                return ['success'=>'无需更新'];
            }
        }
    }


}
<?php

/**
 * @describe:获取课程学习记录汇总控制器
 * @User:gl
 * @Class FolderLogController
 * @Date 2017/12/22
 */
class FolderLogController extends Controller{

    private $studyLengthHashName = 'studyLength'; //存放用户在当前网校的课程学习记录
    private $totalHashName = 'totallog';    //存放用于课件的学习记录缓存
    private $folderLength = 'folderLength' ;//存放课程总时长

    public function init(){
        Ebh()->filter = 'Filter_Server';    //验证IP来源
        parent::init();
    }

    /**
     * @describe:请求参数验证规则
     * @return array
     */
    public function parameterRules(){
        return array(
            'getAction' => array(
                'crid' => array('name' => 'crid','type' => 'int','require' => true),
                'uid' => array('name' => 'uid','type' => 'int','require' => true),
                'folderids' => array('name' => 'folderids','type' => 'string','require' => true)
            )
        );
    }

    /**
     * @describe:获取课程时长和学习时长(可以是多个课程)
     * @param int $crid 网校id
     * @param int $uid  用户id
     * @param string $folderids 课程id多个逗号隔开 12567,45567
     * @return array
     */
    public function getAction() {
        $crid  = $this->crid;
        $uid  = $this->uid;
        $folderids = $this->folderids;

        // 验证传入的folderids字符串是否合法
        if (!preg_match('/^((\d,?)*)\d$/U', $folderids)) {
            return FALSE;
        }
        $redisKey  = $crid . '_' . $uid;//redis缓存key
        $redis = Ebh()->cache;//redis对象
        $folderidArr  = explode(',', $folderids);
        $totallogs  = array();//存课程学习记录汇总
        $updateFolder = array();//需要更新总时长的课程
        foreach ($folderidArr as $folderid) {
            $folder = $redis->hGet($this->folderLength, $folderid);
            //缓存中有效课程(课程总时长未过期)
            if (isset($folder['addtime']) && (SYSTIME - $folder['addtime']) < 600) {
                $totallogs[$folderid]['cwlength'] = $folder['cwlength'];
            } else {
                //已过期的课程总时长
                array_push($updateFolder, $folderid);
            }
        }
        //将需要更新总时长的课程重新赋值 存入redis
        if (!empty($updateFolder)) {
            $updateFolderList = $this->_getFolderTotalLength(implode(',', $updateFolder));
            if ($updateFolderList) {
                foreach ($updateFolderList as $folderid => $item) {
                    $totallogs[$folderid]['cwlength'] = isset($item['cwlength']) ? $item['cwlength'] : 0;
                    //缓存课程总时长信息
                    $redis->hSet($this->folderLength,$folderid,array('cwlength'=>$totallogs[$folderid]['cwlength'],'addtime'=>SYSTIME));
                }
            }
        }
        //log_message('课程总时长'.json_encode($totallogs));
        //获取课程学习时长
        $folderStudyList = $redis->hGet($this->studyLengthHashName,$redisKey);
        if (empty($folderStudyList)) {
            //不存在学习时长 则从数据库取出 并存入缓存
            $couseModel = new CoursewareModel();
            $courseList = $couseModel->getCourseList($crid, $uid, $folderids);//获取课程的课件列表
            if (!empty($courseList)) {
                $courseArr = array_keys($courseList);
                $updateCourseArr = array();//需要更新的课件
                //取缓存中的课件
                foreach ($courseArr as $cwid) {
                    $key   = $cwid . '_' . $uid;
                    $couse = $redis->hGet($this->totalHashName, $key);
                    if (!empty($couse)) {
                        //存在缓存的将它插入汇总数组中
                        $folderid = $couse['folderid'];
                        $totallogs[$folderid]['ltime'] = isset($totallogs[$folderid]['ltime']) ? $totallogs[$folderid]['ltime'] : 0;
                        $totallogs[$folderid]['ltime'] += $couse['ltime'];
                        $totallogs[$folderid]['totalltime'] = isset($totallogs[$folderid]['totalltime']) ? $totallogs[$folderid]['totalltime'] : 0;
                        $totallogs[$folderid]['totalltime'] += $couse['totalltime'];

                    } else {
                        //不存在的课件存入数组中
                        array_push($updateCourseArr, $cwid);
                    }

                }
                //如果有课件不在缓存中统一去数据库查询
                if (!empty($updateCourseArr)) {
                    $playlogModel     = new PlayLogModel();
                    // 普通网校
                    $updateCourseList1 = $playlogModel->getCourseltime($crid, $uid, implode(',', $updateCourseArr));
                    // 国土
                    $updateCourseList2 = $playlogModel->getCourseTotalltime($crid, $uid, implode(',', $updateCourseArr));
                    //将不存在缓存中的学习记录插入汇总数组中
                    foreach ($updateCourseArr as $cwid) {
                        if(array_key_exists($cwid,$updateCourseList1)){
                            $folderid = $updateCourseList1[$cwid]['folderid'];
                            $totallogs[$folderid]['ltime'] = isset($totallogs[$folderid]['ltime']) ? $totallogs[$folderid]['ltime'] : 0;
                            $totallogs[$folderid]['ltime'] += (isset($updateCourseList1[$cwid]['ltime']) ? $updateCourseList1[$cwid]['ltime'] : 0);
                            $totallogs[$folderid]['totalltime'] = isset($totallogs[$folderid]['totalltime']) ? $totallogs[$folderid]['totalltime'] : 0;
                            $totallogs[$folderid]['totalltime'] += (isset($updateCourseList2[$cwid]['totalltime']) ? $updateCourseList2[$cwid]['totalltime'] : 0);
                        }
                    }
                }
            }
            //log_message('要缓存的数据'.json_encode($totallogs,JSON_UNESCAPED_UNICODE));
            $redis->hSet($this->studyLengthHashName, $redisKey, $totallogs); //将数据缓存

        } else {
            //存在学习记录缓存，将新的课程总时长组装到学习记录里
            $updateFolderArr = [];
            foreach ($totallogs as $folderid => $item) {
                if(isset($folderStudyList[$folderid]['ltime'])){
                    $totallogs[$folderid]['ltime']      = $folderStudyList[$folderid]['ltime'];//普通网校
                    $totallogs[$folderid]['totalltime'] = $folderStudyList[$folderid]['totalltime'];//国土学习记录

                }else{
                    array_push($updateFolderArr,$folderid);
                    //不存在学习时长 则从数据库取出 并存入缓存

                }

            }
            if(!empty($updateFolderArr)){
                $folderids  = implode(',',$updateFolderArr);
                $couseModel = new CoursewareModel();
                $courseList = $couseModel->getCourseList($crid, $uid, $folderids);//获取课程的课件列表
                if (!empty($courseList)) {
                    $courseArr = array_keys($courseList);
                    $updateCourseArr = array();//需要更新的课件
                    //取缓存中的课件
                    foreach ($courseArr as $cwid) {
                        $key   = $cwid . '_' . $uid;
                        $couse = $redis->hGet($this->totalHashName, $key);
                        if (!empty($couse)) {
                            //存在缓存的将它插入汇总数组中
                            $folderid = $couse['folderid'];
                            $totallogs[$folderid]['ltime'] = isset($totallogs[$folderid]['ltime']) ? $totallogs[$folderid]['ltime'] : 0;
                            $totallogs[$folderid]['ltime'] += $couse['ltime'];
                            $totallogs[$folderid]['totalltime'] = isset($totallogs[$folderid]['totalltime']) ? $totallogs[$folderid]['totalltime'] : 0;
                            $totallogs[$folderid]['totalltime'] += $couse['totalltime'];

                        } else {
                            //不存在的课件存入数组中
                            array_push($updateCourseArr, $cwid);
                        }

                    }
                    //如果有课件不在缓存中统一去数据库查询
                    if (!empty($updateCourseArr)) {
                        $playlogModel     = new PlayLogModel();
                        // 普通网校
                        $updateCourseList1 = $playlogModel->getCourseltime($crid, $uid, implode(',', $updateCourseArr));
                        // 国土
                        $updateCourseList2 = $playlogModel->getCourseTotalltime($crid, $uid, implode(',', $updateCourseArr));
                        //将不存在缓存中的学习记录插入汇总数组中
                        foreach ($updateCourseArr as $cwid) {
                            if(array_key_exists($cwid,$updateCourseList1)){
                                $folderid = $updateCourseList1[$cwid]['folderid'];
                                $totallogs[$folderid]['ltime'] = isset($totallogs[$folderid]['ltime']) ? $totallogs[$folderid]['ltime'] : 0;
                                $totallogs[$folderid]['ltime'] += (isset($updateCourseList1[$cwid]['ltime']) ? $updateCourseList1[$cwid]['ltime'] : 0);
                                $totallogs[$folderid]['totalltime'] = isset($totallogs[$folderid]['totalltime']) ? $totallogs[$folderid]['totalltime'] : 0;
                                $totallogs[$folderid]['totalltime'] += (isset($updateCourseList2[$cwid]['totalltime']) ? $updateCourseList2[$cwid]['totalltime'] : 0);
                            }
                        }
                    }
                }
                //log_message('要缓存的数据'.json_encode($totallogs,JSON_UNESCAPED_UNICODE));
                $redis->hSet($this->studyLengthHashName, $redisKey, $totallogs); //将数据缓存

            }
        }
        //log_message('返回的数据'.json_encode($totallogs));
        return $totallogs;

    }

    /**
     * 获取课程的总时长(可以是多个课程)
     * @param $folderids 1263,1258
     * @return array array('folderid1'=>array('folderid'=>1234,'cwlength'=>4567),
     *                     'folderid2'=>array('folderid'=>1234,'cwlength'=>4567)
     *                         )
     */
    private function _getFolderTotalLength($folderids){
        $folderModel = new CoursewareModel();
        $FolderTotalLength  = $folderModel->getLengthByFolderid($folderids);
        return $FolderTotalLength;

    }



}
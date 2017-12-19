<?php

/**
 * LogController 学习中心相关
 * Author: tzq
 * Email: 290847305@qq.com
 * time: 2017/10/25
 */
class CenterController extends Controller
{


    public function init(){
        parent::init();//初始化
    }

    /***
     * 参数验证
     * @return array
     */
    public function parameterRules(){
        return array(
            'getProgressAndRateAction' => array(
                'uid'=>array('name'=>'uid', 'type'=>'int', 'require'=>true),
                'crid'=>array('name'=>'crid','type'=>'int','require'=>true)

            ),

        );
    }

    /****
     * 获取学习进度和课程比例
     * @param int $uid 用户uid
     * @return array
     */
    public function getProgressAndRateAction(){
        $courseModel = new CourseModel();
        return $courseModel->getProgressAndRate($this->uid,$this->crid);
   }


}
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
            'getRoomTeacherAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int')
            )
        );
    }

    /**
     * 读取网校教师列表
     * @return array
     */
    public function getRoomTeacherAction(){
        $list = $this->teacherModel->getRoomTeacherList($this->crid);
        return array(
            'list'  =>  $list
        );
    }
}
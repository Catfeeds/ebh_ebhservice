<?php
/**
 * 班级老师
 * Author: ycq
 */
class ClassTeacherController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //获取教师任课班级列表
            'getClassesForTeacherAction' => array(
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 获取教师任课班级列表
     * @return mixed
     */
    public function getClassesForTeacherAction() {
        $model = new ClassTeacherModel();
        return $model->getClassesForTeacher($this->uid, $this->crid);
    }
}
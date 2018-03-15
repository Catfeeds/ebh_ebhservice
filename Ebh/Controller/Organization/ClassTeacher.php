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
            ),
            //获取教师担任班主任的班级列表
            'getClassesForHomeroomAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ,                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                's' => array(
                    'name' => 's',
                    'type' => 'string'
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 0
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

    /**
     * 获取教师担任班主任的班级列表
     */
    public function getClassesForHomeroomAction() {
        $classesModel = new ClassesModel();
        $params = array();
        if ($this->s !== null) {
            $params['classname'] = $this->s;
            $params['username'] = $this->s;
            $params['realname'] = $this->s;
        }
        $count = $classesModel->getClassCountForHomeroom($this->uid, $this->crid, $params);
        if ($count == 0) {
            return array(
                'count' => 0
            );
        }
        $limit = array();
        if ($this->pagesize > 0) {
            $limit['page'] = $this->page;
            $limit['pagesize'] = $this->pagesize;
        }
        $ret = $classesModel->getClassesForHomeroom($this->uid, $this->crid, $params, true, $limit);
        return array(
            'count' => $count,
            'list' => $ret
        );
    }
}
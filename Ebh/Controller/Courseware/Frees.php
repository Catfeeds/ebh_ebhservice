<?php
/**
 * 免费试听课件
 * Author: ycq
 */
class FreesController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //免费试听课件列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'withAdmin' => array(
                    'name' => 'withAdmin',
                    'type' => 'int',
                    'default' => 0
                ),
                'cwids' => array(
                    'name' => 'cwids',
                    'type' => 'array'
                )
            )
        );
    }

    /**
     * 免费试听课件列表
     */
    public function indexAction(){
        $model = new DesignCoursewareModel();
        $cwids = array();
        if ($this->cwids !== null) {
            $cwids = array_map('intval', $this->cwids);
            $cwids = array_filter($cwids, function($cwid) {
               return $cwid > 0;
            });
            $cwids = array_unique($cwids);
        }
        $classroomModel = new ClassRoomModel();
        $classroom = $classroomModel->getModel($this->crid);
        if (empty($classroom)) {
            return array();
        }
        $roomtype = $classroom['property'] == 3 && $classroom['isschool'] == 7 ? 'com' : 'edu';
        $designModel = new DesignModel();
        $design = $designModel->getDesignByCrid($this->crid, $roomtype, 0);
        $ret = array();
        if (!empty($design)) {
            $ret = $model->getFreeCoursewareList($design['did'], $this->crid, $cwids);
        }
        $design = $designModel->getDesignByCrid($this->crid, $roomtype, 1);
        if (!empty($design)) {
            $coursewares = $model->getFreeCoursewareList($design['did'], $this->crid, $cwids);
            if (!empty($coursewares)) {
                $ret = $ret + $coursewares;
            }
        }
        if (empty($ret)) {
            return array();
        }
        if ($this->withAdmin == 1) {
            $crids = array_column($ret, 'crid');
            $classroomModel = new ClassRoomModel();
            $administrators = $classroomModel->getAdministrator($crids);
            array_walk($ret, function(&$courseware, $cwid, $administrators) {
                if (isset($administrators[$courseware['crid']])) {
                    $courseware['administrator'] = $administrators[$courseware['crid']];
                }
            }, $administrators);
        }
        return $ret;
    }
}
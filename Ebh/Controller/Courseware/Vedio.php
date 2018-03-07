<?php
/**
 * 视频课件
 * Author: ycq
 */
class VedioController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //视频课件列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'default' => 0
                ),
                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'int',
                    'default' => 0
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                )
            ),
            //设置主页视频部件课件
            'setComponentVedioAction' => array(
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                ),
                'cwids' => array(
                    'name' => 'cwids',
                    'type' => 'array',
                    'default' => array()
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //获取视频课件
            'getVedioAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'cwid' => array(
                    'name' => 'cwid',
                    'type' => 'int',
                    'require' => true
                ),
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //设置装扮免费试听视频课件、装扮主页视频课件、装扮背景视频
            'setHomeVedioAction' => array(
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                ),
                'auditions' => array(
                    'name' => 'auditions',
                    'type' => 'array',
                    'default' => array()
                ),
                'vedioids' => array(
                    'name' => 'vedioids',
                    'type' => 'array',
                    'default' => array()
                ),
                'backvedio' => array(
                    'name' => 'backvedio',
                    'type' => 'int',
                    'default' => 0
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
     * 视频课件列表
     */
    public function indexAction(){
        $model = new CoursewareModel();
        $params = array();
        if ($this->folderid > 0) {
            $params['folderid'] = $this->folderid;
        }
        $count = $model->getVedioCount($this->crid, $params);
        $limit = null;
        if ($this->page === null && $this->pagesize !== null) {
            $limit = $this->pagesize;
        } else if ($this->page !== null && $this->pagesize !== null) {
            $limit = array(
                'page' => $this->page,
                'pagesize' => $this->pagesize
            );
        }
        $list = $count > 0 ? $model->getVedioList($this->crid, $params, $limit) : array();
        return array(
            'count' => $count,
            'list' => $list
        );
    }

    /**
     * 设置主页视频部件课件
     * @return int
     */
    public function setComponentVedioAction() {
        $model = new DesignCoursewareModel();
        return $model->setDesignCoursewares($this->cwids, $this->crid, $this->did, DesignCoursewareModel::VEDIO);
    }

    /**
     * 获取视频
     * @return mixed
     */
    public function getVedioAction() {
        $model = new DesignCoursewareModel();
        $vedio = $model->getVedio($this->cwid, $this->did, $this->crid);
        if (!empty($vedio) && empty($vedio['isfree'])) {
            //课件未设置免费试听，读取课件所属网校的管理员帐号
            $classroomModel = new ClassRoomModel();
            $administrator = $classroomModel->getAdministrator($vedio['crid']);
            if (!empty($administrator)) {
                $vedio['administrator'] = $administrator;
            }
        }
        return $vedio;
    }

    /**
     * 设置装扮免费试听视频课件、装扮主页视频课件、装扮背景视频
     * @return int
     */
    public function setHomeVedioAction() {
        $auditions = array_map('intval', $this->auditions);
        $vedioids = array_map('intval', $this->vedioids);
        $model = new DesignCoursewareModel();
        $backvedios = $this->backvedio > 0 ? array($this->backvedio) : array();
        $model->setDesignCoursewares($auditions, $this->crid, $this->did, DesignCoursewareModel::FREE);
        $model->setDesignCoursewares($vedioids, $this->crid, $this->did, DesignCoursewareModel::VEDIO);
        $model->setDesignCoursewares($backvedios, $this->crid, $this->did, DesignCoursewareModel::BACK);
        return 1;
    }
}
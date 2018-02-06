<?php

/**
 * 课程服务
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/8/17
 * Time: 13:52
 */
class BundleController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //课程包列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',
                    'default' => 0
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 0
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 0
                ),
                'display' => array(
                    'name' => 'display',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //课程包详情
            'detailAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //编辑课程包
            'editAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'default' => 0
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string',
                    'require' => true
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string',
                    'require' => true
                ),
                'cover' => array(
                    'name' => 'cover',
                    'type' => 'string',
                    'require' => false
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',
                    'require' => true
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'default' => 0
                ),
                'speaker' => array(
                    'name' => 'speaker',
                    'type' => 'string',
                    'require' => true
                ),
                'bprice' => array(
                    'name' => 'bprice',
                    'type' => 'float',
                    'require' => true
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'default' => 0
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'detail' => array(
                    'name' => 'detail',
                    'type' => 'string'
                ),
                'itemids' => array(
                    'name' => 'itemids',
                    'type' => 'array',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'display' => array(
                    'name' => 'display',
                    'type' => 'int'
                ),
                'displayorder' => array(
                    'name' => 'displayorder',
                    'type' => 'int'
                ),
				'cannotpay' => array(
                    'name' => 'cannotpay',
                    'type' => 'boolean'
				),
				'limitnum' => array(
                    'name' => 'limitnum',
                    'type' => 'int',
					'default'=>0
                ),
				'islimit' => array(
                    'name' => 'islimit',
                    'type' => 'int',
					'default'=>0
                )
            ),
            //删除课程包
            'removeAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //设置课程包教师
            'setTeachersAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'tids' => array(
                    'name' => 'tids',
                    'type' => 'array'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //课程包老师
            'teachersAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'range' => array(
                    'name' => 'range',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //课程包课程列表
            'bundleCoursesAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'simple' => array(
                    'name' => 'simple',
                    'type' => 'boolen',
                    'require' => false
                )
            ),
            //课程包教师详情
            'teacherInfosAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //教师课程包列表
            'teacherBundleListAction' => array(
                'tid' => array(
                    'name' => 'tid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //设置课程包属性
            'setAttibuteAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'display' => array(
                    'name' => 'display',
                    'type' => 'int'
                ),
                'displayorder' => array(
                    'name' => 'displayorder',
                    'type' => 'int'
                )
            ),
            //设置课程包前台显示
            'setVisibilityAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'adds' => array(
                    'name' => 'adds',
                    'type' => 'array'
                ),
                'dels' => array(
                    'name' => 'dels',
                    'type' => 'array'
                )
            )
        );
    }

    /**
     * 课程包列表
     */
    public function indexAction() {
        $model = new BundleModel();
        $filterParams = array(
            'pid' => $this->pid,
            'k' => $this->k,
            'display' => $this->display
        );
        if ($this->sid !== null) {
            $filterParams['sid'] = $this->sid;
        }
        $count = $model->bundleCount($this->crid, $filterParams);
        if ($count == 0) {
            return array(
                'count' => 0,
                'list' => array()
            );
        }
        $limits = 0;
        if ($this->page > 0 || $this->pagesize > 0) {
            $limits = array(
                'page' => $this->page,
                'pagesize' => $this->pagesize
            );
        }
        $bundles = $model->bundleList($this->crid, $filterParams, $limits);
        $bids = array_column($bundles, 'bid');
        //课程数据统计信息
        $bundleCourses = $model->courseList($bids);
        if ($this->uid > 0) {
            $folderids = array_column($bundleCourses, 'folderid');
            $userPermisionModel = new UserpermisionsModel();
            $folderids = $userPermisionModel->checkPermission($folderids, $this->uid, $this->crid);
        }
        if (!empty($folderids)) {
            foreach ($bundleCourses as $k => $course) {
                $bundleCourses[$k]['hasPower'] = in_array($course['folderid'], $folderids);
            }
            unset($folderids);
        }
        foreach ($bundleCourses as $course) {
            if (!empty($course['hasPower']) && empty($bundles[$course['bid']]['folderid'])) {
                $bundles[$course['bid']]['folderid'] = $course['folderid'];
                $bundles[$course['bid']]['showmode'] = $course['showmode'];
            }
            $bundles[$course['bid']]['folderids'][$course['folderid']] = $course['viewnum'];
            if (!isset($bundles[$course['bid']]['viewnum'])) {
                $bundles[$course['bid']]['viewnum'] = $course['viewnum'];
                $bundles[$course['bid']]['coursewarenum'] = $course['coursewarenum'];
                $bundles[$course['bid']]['hasPower'] = !empty($course['hasPower']);
                $bundles[$course['bid']]['coursenum'] = 1;
                continue;
            }
            $bundles[$course['bid']]['viewnum'] += $course['viewnum'];
            $bundles[$course['bid']]['coursewarenum'] += $course['coursewarenum'];
            $bundles[$course['bid']]['hasPower'] = $bundles[$course['bid']]['hasPower'] && !empty($course['hasPower']);
            $bundles[$course['bid']]['coursenum']++;
        }

        return array(
            'count' => $count,
            'list' => array_values($bundles)
        );
    }

    /**
     * 课程包详情
     */
    public function detailAction() {
        $model = new BundleModel();
        $detail = $model->bundleDetail($this->bid);
        if (empty($detail)) {
            return false;
        }
        //课程信息
        $detail['courses'] = $model->getCourseList($this->bid, false);
        //课程权限
        $folderids = array();
        if ($this->uid > 0) {
            $permistionModel = new UserpermisionsModel();
            $folderids = array_column($detail['courses'], 'folderid');
            $folderids = array_unique($folderids);
            $folderids = $permistionModel->checkPermission($folderids, $this->uid, $detail['crid']);
        }

        $detail['hasPower'] = true;
        foreach ($detail['courses'] as $k => $course) {
            $detail['courses'][$k]['hasPower'] = in_array($course['folderid'], $folderids);
            $detail['hasPower'] = $detail['hasPower'] && $detail['courses'][$k]['hasPower'];
        }

        return $detail;
    }

    /**
     * 编辑课程包
     * @return array|bool
     */
    public function editAction() {
        $itemids = array_map('intval', $this->itemids);
        $itemids = array_filter($itemids, function($itemid) {
            return $itemid > 0;
        });
        if (empty($itemids)) {
            return false;
        }
        //验证课程服务项的有效性
        $payItemModel = new PayitemModel();
        $validItemids = $payItemModel->checkIds($itemids, $this->crid);
        if (empty($validItemids)) {
            return false;
        }
        $itemids = array_intersect($itemids, $validItemids);
        $params = array(
            'name' => $this->name,
            'remark' => $this->remark,
            'pid' => $this->pid,
            'sid' => $this->sid,
            'speaker' => $this->speaker,
            'bprice' => $this->bprice,
            'detail' => $this->detail,
            'uid' => $this->uid,
            'itemids' => $itemids,
            'dateline' => SYSTIME
        );
		//限制人数范围1-9999
		$params['limitnum'] = $this->limitnum;
		$params['islimit'] = $this->islimit;
		if($params['limitnum'] > 9999){
			$params['limitnum'] = 9999;
		} elseif($params['limitnum'] < 1 && $params['islimit'] == 1){
			$params['limitnum'] = 1;
		}
        if ($this->cover !== null) {
            $params['cover'] = $this->cover;
        }
        if ($this->detail !== null) {
            $params['detail'] = $this->detail;
        }
        if ($this->display !== null) {
            $params['display'] = intval($this->display) > 0 ? 1 : 0;
        }
        if ($this->displayorder !== null) {
            $params['displayorder'] = intval($this->displayorder);
        }
        if ($this->cannotpay !== null) {
            $params['cannotpay'] = intval($this->cannotpay);
        }
        $model = new BundleModel();
        if ($this->bid > 0) {
            //更新课程包
            $ret = $model->edit($this->bid, $this->crid, $params);
            return $ret;
        }
        //添加课程包
        return $model->add($this->crid, $params);
    }

    /**
     * 删除课程包
     */
    public function removeAction() {
        $model = new BundleModel();
        return $model->remove($this->bid);
    }

    /**
     * 设置课程包教师
     */
    public function setTeachersAction() {
        $roomTeacherModel = new RoomTeacherModel();
        $tids = $roomTeacherModel->checkTeacherids($this->tids, $this->crid);
        if (empty($tids)) {
            return false;
        }
        $model = new BundleModel();
        $ret = $model->editTeachers($this->bid, $tids);
        return $ret;
    }

    /**
     * 课程包教师列表
     */
    public function teachersAction() {
        $model = new BundleModel();
        $tids = $model->teacheridList($this->bid);
        $teacherModel = new TeacherModel();
        if (empty($this->range)) {
            $tids = array_flip($tids);
            $teachers = $teacherModel->getRoomTeacherList($this->crid, array(), true);
            $bundleTeachers = array_intersect_key($teachers, $tids);
            $teachers = array_diff_key($teachers, $bundleTeachers);
            return array(
                'bundleTeachers' => array_values($bundleTeachers),
                'teachers' => array_values($teachers)
            );
        }
        $teachers = $teacherModel->getRoomTeacherList($this->crid, array('uid' => $tids));
        return array('bundleTeachers' => $teachers);
    }

    /**
     * 课程包课程列表
     */
    public function bundleCoursesAction() {
        $model = new BundleModel();
        $courses = $model->getCourseList($this->bid, $this->simple, false);
        return $courses;
    }

    /**
     * 课程包老师统计信息
     * @return mixed
     */
    public function teacherInfosAction() {
        $model = new BundleModel();
        $tids = $model->teacheridList($this->bid);
        if (empty($tids)) {
            return array();
        }
        $teacherModel = new TeacherModel();
        $teachers = $teacherModel->getRoomTeacherList($this->crid, array('uid' => $tids), true);
        $teacherInfos = $model->teacherInfoList($tids, $this->crid);
        array_walk($teachers, function(&$teacher, $uid, $infos) {
            if (isset($infos['courseware'][$uid])) {
                $teacher['courseware_count'] = $infos['courseware'][$uid]['c'];
                $teacher['courseware_length'] = round($infos['courseware'][$uid]['cwlength'] / 60);
            } else {
                $teacher['courseware_count'] = 0;
                $teacher['courseware_length'] = 0;
            }
            if (isset($infos['answer'][$uid])) {
                $teacher['answer_count'] = $infos['answer'][$uid]['c'];
            } else {
                $teacher['answer_count'] = 0;
            }
            if (isset($infos['review'][$uid])) {
                $teacher['review_count'] = $infos['review'][$uid]['c'];
            } else {
                $teacher['review_count'] = 0;
            }
            if (isset($infos['exam'][$uid])) {
                $teacher['exam_count'] = $infos['exam'][$uid]['c'];
            } else {
                $teacher['exam_count'] = 0;
            }
        }, $teacherInfos);
        return $teachers;
    }

    /**
     * 教师课程包列表
     * @return mixed
     */
    public function teacherBundleListAction() {
        $model = new BundleModel();
        $bundles = $model->teacherBundleList($this->tid, $this->crid);
        if (empty($bundles)) {
            return array();
        }
        $bids = array_keys($bundles);
        $courses = $model->courseList($bids);
        $folderids = array();
        if ($this->uid > 0) {
            $permistionModel = new UserpermisionsModel();
            $folderids = array_column($courses, 'folderid');
            $folderids = array_unique($folderids);
            $folderids = $permistionModel->checkPermission($folderids, $this->uid, $this->crid);
        }
        foreach ($courses as $course) {
            if (!isset($bundles[$course['bid']]['viewnum'])) {
                $bundles[$course['bid']]['viewnum'] = 0;
                $bundles[$course['bid']]['coursewarenum'] = 0;
                $bundles[$course['bid']]['hasPower'] = true;
            }
            $bundles[$course['bid']]['viewnum'] += $course['viewnum'];
            $bundles[$course['bid']]['coursewarenum'] += $course['coursewarenum'];
            $bundles[$course['bid']]['cids'][] = $course['folderid'];
            $bundles[$course['bid']]['hasPower'] = $bundles[$course['bid']]['hasPower'] && in_array($course['folderid'], $folderids);
        }
        return $bundles;
    }

    /**
     * 设置课程包属性
     * @return mixed
     */
    public function setAttibuteAction() {
        $model = new BundleModel();
        $params = array();
        if ($this->display !== null) {
            $params['display'] = $this->display;
        }
        if ($this->displayorder !== null) {
            $params['displayorder'] = $this->displayorder;
        }
        if (empty($params)) {
            return false;
        }
        return $model->setAttribute($params, $this->bid, $this->crid);
    }

    /**
     * 设置课程包前台显示
     */
    public function setVisibilityAction() {
        if ($this->adds === null && $this->dels === null) {
            return false;
        }
        $adds = $dels = array();
        if (!empty($this->adds)) {
            $adds = array_map('intval', $this->adds);
            $adds = array_filter($adds, function($addid) {
                return $addid > 0;
            });
        }
        if (!empty($this->dels)) {
            $dels = array_map('intval', $this->dels);
            $dels = array_filter($dels, function($delid) {
               return $delid > 0;
            });
        }
        if (empty($adds) && empty($dels)) {
            return false;
        }
        $model = new BundleModel();
        return $model->setVisibility($adds, $dels, $this->crid);
    }
}
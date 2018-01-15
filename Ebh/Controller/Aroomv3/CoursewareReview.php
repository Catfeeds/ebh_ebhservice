<?php

/**
 * 课程评论接口
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/24
 * Time: 13:22
 */
class CoursewareReviewController extends Controller {
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //课程评论列表
            'coursewareReviewListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    //'require' => true
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'early' => array(
                    'name' => 'early',
                    'type' => 'int'
                ),
                'latest' => array(
                    'name' => 'latest',
                    'type' => 'int'
                ),
                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'int'
                ),
                'shield'   => array(
                    'name' => 'shield',
                    'type' => 'int'
                ),
                'audit' => array(
                    'name' => 'audit',
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'default' => 'edu'
                ),
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //课程评论统计
            'coursewareReviewCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    //'require' => true
                ),
                'early' => array(
                    'name' => 'early',
                    'type' => 'int'
                ),
                'latest' => array(
                    'name' => 'latest',
                    'type' => 'int'
                ),
                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'int'
                ),
                'shield'   => array(
                    'name' => 'shield',
                    'type' => 'int'
                ),
                'audit' => array(
                    'name' => 'audit',
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'default' => 'edu'
                ),
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //屏蔽评论
            'setShieldAction' => array(
                'logid' => array(
                    'name' => 'logid',
                    'require' => true,
                    'type' => 'int'
                ),
                'shield' => array(
                    'name' => 'shield',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //屏蔽评论
            'setAuditAction' => array(
                'logid' => array(
                    'name' => 'logid',
                    'require' => true,
                    'type' => 'int'
                ),
                'audit' => array(
                    'name' => 'audit',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //获取来源网校
            'getSchsourceAction'  =>  array(
                'crid'  =>  array('name'=>'crid','type'=>'int','require'=>true)
            ),
            //获取网校名
            'getNameAction'  =>  array(
                'crid'  =>  array('name'=>'crid','type'=>'int'),
                'sourcecrid'  =>  array('name'=>'sourcecrid','type'=>'int')
            )
        );
    }

    /**
     * 课程评论统计
     * @return int
     */
    public function coursewareReviewCountAction() {
        if ($this->uid > 0) {
            //非网校管理员用户，读取权限范围
            $teacherroleModel = new TeacherRoleModel();
            $role = $teacherroleModel->getTeacherRole($this->uid, $this->crid);
            if (is_numeric($role) && $role != 2) {
                //非系统管理员角色
                return 0;
            }
            if (!empty($role['limitscope'])) {
                //自定义的权限受限管理员角色
                $classTeacherModel = new ClassTeacherModel();
                if ($this->roomtype == 'com') {
                    $teacherDepts = $classTeacherModel->getDeptsForTeacher($this->uid, $this->crid);
                    if (empty($teacherDepts)) {
                        return 0;
                    }
                    $parents = array();
                    while ($parent = array_shift($teacherDepts)) {
                        $parents[] = $parent;
                        $teacherDepts = array_filter($teacherDepts, function($dept) use($parent) {
                            return $dept['rgt'] > $parent['rgt'] || $dept['lft'] < $parent['lft'];
                        });
                    }
                    $classes = $classTeacherModel->getDeptsForTeacherWithPath($this->crid, $parents);
                } else {
                    $classes = $classTeacherModel->getClassesForTeacher($this->uid, $this->crid);
                }
                if (empty($classes)) {
                    return 0;
                }
            }
        }
        $model = new ReviewModel();
        $filters = array();
        if ($this->early !== NULL) {
            $filters['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filters['latest'] = $this->latest;
        }
        if ($this->folderid !== NULL) {
            $filters['folderid'] = $this->folderid;
        }
        if ($this->shield !== NULL && $this->shield !== '') {
            $filters['shield'] = $this->shield;
        }
        if ($this->audit !== NULL && $this->audit !== '') {
            $filters['audit'] = $this->audit;
        }
        if (!empty($classes)) {
            $filters['classids'] = array_column($classes, 'classid');
            if ($this->classid > 0) {
                if (in_array($this->classid, $filters['classids'])) {
                    $filters['classids'] = array($this->classid);
                } else {
                    return 0;
                }
            }
            $filters['roomtype'] = $this->roomtype;
            unset($classes);
        } else if ($this->classid > 0) {
            $filters['roomtype'] = $this->roomtype;
            $filters['classids'] = array($this->classid);
        }
        return $model->getCourseReviewCount($this->crid, $filters);
    }

    /**
     * 课程评论列表
     * @return mixed
     */
    public function coursewareReviewListAction() {
        $filters = array();
        if ($this->uid > 0) {
            //非网校管理员用户，读取权限范围
            $teacherroleModel = new TeacherRoleModel();
            $role = $teacherroleModel->getTeacherRole($this->uid, $this->crid);
            if (is_numeric($role) && $role != 2) {
                //非系统管理员角色
                return array();
            }
            if (!empty($role['limitscope'])) {
                //自定义的权限受限管理员角色
                $classTeacherModel = new ClassTeacherModel();
                if ($this->roomtype == 'com') {
                    $teacherDepts = $classTeacherModel->getDeptsForTeacher($this->uid, $this->crid);
                    if (empty($teacherDepts)) {
                        return array();
                    }
                    $parents = array();
                    while ($parent = array_shift($teacherDepts)) {
                        $parents[] = $parent;
                        $teacherDepts = array_filter($teacherDepts, function($dept) use($parent) {
                            return $dept['rgt'] > $parent['rgt'] || $dept['lft'] < $parent['lft'];
                        });
                    }
                    $classes = $classTeacherModel->getDeptsForTeacherWithPath($this->crid, $parents);
                } else {
                    $classes = $classTeacherModel->getClassesForTeacher($this->uid, $this->crid);
                }
                if (empty($classes)) {
                    return array();
                }
            }

        }
        $model = new ReviewModel();
        if ($this->early !== NULL) {
            $filters['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filters['latest'] = $this->latest;
        }
        if ($this->folderid !== NULL) {
            $filters['folderid'] = $this->folderid;
        }
        if ($this->shield !== NULL && $this->shield !== '') {
            $filters['shield'] = $this->shield;
        }
        if ($this->audit !== NULL && $this->audit !== '') {
            $filters['audit'] = $this->audit;
        }
        if (!empty($classes)) {
            $filters['classids'] = array_column($classes, 'classid');
            if ($this->classid > 0) {
                if (in_array($this->classid, $filters['classids'])) {
                    $filters['classids'] = array($this->classid);
                } else {
                    return array();
                }
            }
            $filters['roomtype'] = $this->roomtype;
            unset($classes);
        } else if ($this->classid > 0) {
            $filters['roomtype'] = $this->roomtype;
            $filters['classids'] = array($this->classid);
        }
        $limits = array();
        if ($this->pagesize !== NULL) {
            $limits['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limits['page'] = $this->pagenum;
        }

        $ret = $model->getCourseReviewList($this->crid, $filters, $limits);

        if (!empty($ret)) {
            //注入课件信息、课件发布者信息、评论者信息
            $cwidArr = array_column($ret, 'toid');
            $coursewareModel = new CoursewareModel();
            $coursewareArr = $coursewareModel->getSimpleInfoByIds($cwidArr, true);
            $authorIdArr = array_column($coursewareArr, 'uid');
            $uidArr = array_column($ret, 'uid');
            $uidArr = array_merge($uidArr, $authorIdArr);
            $uidArr = array_unique($uidArr);
            $userModel = new UserModel();
            $userArr = $userModel->getUserByUids(implode(',', $uidArr));
            array_walk($ret, function(&$v, $k, $info) {
                //$v['dateline'] = date('Y-m-d H:i', $v['dateline']);
                if (isset($info['coursewareArr'][$v['toid']])) {
                    $v['courseware'] = $info['coursewareArr'][$v['toid']];
                    //$v['courseware']['dateline'] = date('Y-m-d H:i', $v['courseware']['dateline']);
                    if (isset($info['userArr'][$v['courseware']['uid']])) {
                        $v['author'] = $info['userArr'][$v['courseware']['uid']];
                    }
                }
                if (isset($info['userArr'][$v['uid']])) {
                    $v['user'] = $info['userArr'][$v['uid']];
                }
            }, array(
                'userArr' => $userArr,
                'coursewareArr' => $coursewareArr
            ));
        }

        return $ret;
    }

    /**
     * 屏蔽评论
     */
    public function setShieldAction() {
        $model = new ReviewModel();
        //$shield = $this->shield == 0 ? 0 : 1;
        return $model->setShield($this->logid, $this->shield);
    }
    /**
     * 更新国土审核状态
     */
    public function setAuditAction() {
        $model = new ReviewModel();
        return $model->setAudit($this->logid, $this->audit);
    }

    /**
     * [获取来源网校列表]
     * @return [array]
     */
    public function getSchsourceAction(){
        $model = new ReviewModel();
        return $model->getSchsource($this->crid);
    }

    public function getNameAction(){
        $model = new ReviewModel();
        return $model->getName($this->crid,$this->sourcecrid);
    }
}
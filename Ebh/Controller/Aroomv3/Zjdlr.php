<?php

/**
 * 国土接口
 * Created by PhpStorm.
 * User: ycq
 * Date: 2018/1/27
 * Time: 14:28
 */
class ZjdlrController extends Controller
{
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules()
    {
        return array(
            //问题列表
            'askQuestionListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
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
                'shield' => array(
                    'name' => 'shield',
                    'type' => 'int'
                ),
                'folderid' => array(
                    'name' => 'folderid',
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
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'default' => 0
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'default' => 'edu'
                )
            ),
            //问题统计
            'askQuestionCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'early' => array(
                    'name' => 'early',
                    'type' => 'int'
                ),
                'latest' => array(
                    'name' => 'latest',
                    'type' => 'int'
                ),
                'shield' => array(
                    'name' => 'shield',
                    'type' => 'int'
                ),
                'folderid' => array(
                    'name' => 'folderid',
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
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'default' => 0
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'default' => 'edu'
                )
            )
        );
    }

    /**
     * 问题列表
     * @return mixed
     */
    public function askQuestionListAction() {
        $model = new AskQuestionModel();
        $filters = array();
        if ($this->early !== NULL) {
            $filters['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filters['latest'] = $this->latest;
        }
        if ($this->shield !== NULL) {
            $filters['shield'] = $this->shield;
        }
        if ($this->folderid !== NULL) {
            $filters['folderid'] = $this->folderid;
        }
        if (!empty($this->k)) {
            $filters['k'] = $this->k;
        }
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
        $limits = array();
        if ($this->pagesize !== NULL) {
            $limits['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limits['page'] = $this->pagenum;
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
        $ret = $model->getList($this->crid, $filters, $limits);
        if (!empty($ret)) {
            //注入用户信息，课程名称
            $userModel = new UserModel();
            $uidArr = array_column($ret, 'uid');
            $uidArr = array_unique($uidArr);
            $userArr = $userModel->getUserByUids(implode(',', $uidArr));
            $folderidArr = array_column($ret, 'folderid');
            $folderidArr = array_unique($folderidArr);
            $folderModel = new FolderModel();
            $folderArr = $folderModel->getfolderbyids(implode(',', $folderidArr), true);
            array_walk($ret, function(&$v, $k, $otherInfo) {
                if (isset($otherInfo['users'][$v['uid']])) {
                    $v['user'] = $otherInfo['users'][$v['uid']];
                }
                if (isset($otherInfo['folders'][$v['folderid']])) {
                    $v['foldername'] = $otherInfo['folders'][$v['folderid']]['foldername'];
                }
                //$v['dateline'] = date('Y-m-d H:i', $v['dateline']);
                $imageName = $v['imagename'];
                $imageSrc = $v['imagesrc'];
                unset($v['imagename'], $v['imagesrc']);
                if (!empty($imageSrc)) {
                    $imageSrcArr = explode(',', $imageSrc);
                    $imageSrcArr = array_filter($imageSrcArr, function($src) {
                        return !empty($src);
                    });
                    $imageNameArr = explode(',', $imageName);
                    $images = array();
                    foreach ($imageSrcArr as $k => $srcItem) {
                        $images[] = array(
                            'src' => $srcItem,
                            'name' => isset($imageNameArr[$k]) ? $imageNameArr[$k] : ''
                        );
                    }
                    $v['images'] = $images;
                }
            }, array(
                'users' => $userArr,
                'folders' => $folderArr
            ));
        }

        return $ret;
    }

    /**
     * 问题统计
     */
    public function askQuestionCountAction() {
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
        $model = new AskQuestionModel();
        $filters = array();
        if ($this->early !== NULL) {
            $filters['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filters['latest'] = $this->latest;
        }
        if ($this->shield !== NULL) {
            $filters['shield'] = $this->shield;
        }
        if ($this->folderid !== NULL) {
            $filters['folderid'] = $this->folderid;
        }
        if (!empty($this->k)) {
            $filters['k'] = $this->k;
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
        return $model->getCount($this->crid, $filters);
    }
}
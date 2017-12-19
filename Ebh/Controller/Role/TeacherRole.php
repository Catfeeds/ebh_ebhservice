<?php

/**
 * 教师角色
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/7/25
 * Time: 13:52
 */
class TeacherRoleController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //主页，角色列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'simple' => array(
                    'name' => 'simple',
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
                )
            ),
            //编辑角色
            'editAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'default' => 0
                ),
                'rolename' => array(
                    'name' => 'rolename',
                    'type' => 'string',
                    'require' => true
                ),
                'category' => array(
                    'name' => 'category',
                    'type' => 'int',
                    'require' => true
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string',
                    'default' => ''
                ),
                'permissions' => array(
                    'name' => 'permissions',
                    'type' => 'array',
                    'default' => array()
                )
            ),
            //角色详情
            'detailAction' => array(
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //删除角色
            'removeAction' => array(
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //角色下用户列表
            'roleUsersAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'rid' => array(
                    'name' => 'rid',
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
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //删除角色用户
            'setUserAction' => array(
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'default' => 1
                ),
                'tids' => array(
                    'name' => 'tids',
                    'type' => 'array',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //教师选择面板
            'teacherListPanelAction' => array(
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'q' => array(
                    'name' => 'q',
                    'type' => 'string'
                )
            ),
            //菜单列表
            'getMenuListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'int',
                    'require' => true
                ),
                'rid' => array(
                    'name' => 'rid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //获取教师角色ID
            'getTeacherRoleAction' => array(
                'tid' => array(
                    'name' => 'tid',
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
     * 角色列表
     */
    public function indexAction() {
        $model = new TeacherRoleModel();
        $ret = $model->getList($this->crid, $this->simple > 0, $this->page, $this->pagesize);
        if ($this->simple > 0 || empty($ret)) {
            return $ret;
        }
        if ($this->page > 0) {
            $count = $model->getCount($this->crid);
            return array(
                'count' => $count,
                'list' => $ret
            );
        }
        return array(
            'count' => count($ret),
            'list' => $ret
        );;
    }

    /**
     * 编辑角色
     */
    public function editAction() {
        $model = new TeacherRoleModel();
        if ($this->rid > 0) {
            return $model->update($this->crid, array(
                'rid' => $this->rid,
                'rolename' => $this->rolename,
                'category' => intval($this->category),
                'remark' => $this->remark,
                'permissions' => $this->permissions
            ));
        } else {
            $newid = $model->add($this->crid, array(
                'rolename' => $this->rolename,
                'category' => intval($this->category),
                'remark' => $this->remark,
                'permissions' => $this->permissions
            ));
            if ($newid > 0) {
                return $newid;
            }
            return false;
        }
    }

    /**
     * 角色详情
     * @return mixed
     */
    public function detailAction() {
        $model = new TeacherRoleModel();
        $ret = $model->getDetail($this->rid, $this->crid);
        return $ret;
    }

    /**
     * 删除角色
     * @return bool
     */
    public function removeAction() {
        $model = new TeacherRoleModel();
        return $model->remove($this->rid, $this->crid);
    }

    /**
     * 角色下用户列表
     */
    public function roleUsersAction() {
        $model = new TeacherRoleModel();
        $role = $model->getDetail($this->rid, $this->crid);
        if (empty($role)) {
            return array();
        }
        $ret = $model->getUserList($this->rid, $this->crid, $this->k, $this->page, $this->pagesize);

        if (!empty($ret) && $role['category'] == 2) {
            //管理员后台角色，注入用户操作日志

        }
        if ($this->page > 0) {
            $count = $model->getUserCount($this->rid, $this->crid, $this->k);
            return array(
                'role' => $role,
                'count' => $count,
                'list' => $ret
            );
        }
        return array(
            'role' => $role,
            'count' => count($ret),
            'list' => $ret
        );
    }

    /**
     * 设置角色用户
     * @return mixed
     */
    public function setUserAction() {
        $model = new TeacherRoleModel();
        return $model->setUser($this->rid, $this->tids, $this->crid);
    }

    /**
     * 教师选择面板数据，教师只能设置一个角色
     * @return array
     */
    public function teacherListPanelAction() {
        $teacherModel = new TeacherModel();
        $model = new TeacherRoleModel();
        $params = array();
        if (!empty($this->q)) {
            $params['q'] = $this->q;
        }
        $params['role'] = array(1, 3, $this->rid);
        $teachers = $teacherModel->getRoomTeacherList($this->crid, $params);
        $roleUsers = $model->getUserList($this->rid, $this->crid, '', 0, 0, true);
        return array(
            'teachers' => $teachers,
            'roleUsers' => $roleUsers
        );
    }

    /**
     * 网校管理员端菜单
     * @return array|int
     */
    public function getMenuListAction() {
        $params = array(
            'crid' => $this->crid,
            'roomtype' => $this->roomtype
        );
Ebh()->log->info($this->roomtype);
        $model = new MenuModel();
        $ret = $model->getMenuList($params);
        //过滤隐藏的菜单,角色管理菜单
        array_walk($ret, function(&$item) {
            if (empty($item['children'])) {
                return;
            }
            $children = array_filter($item['children'], function($child) {
               return !empty($child['status']) || $child['url'] != 'rolemanage';
            });
            $item['children'] = array_values($children);
        });
        $ret = array_filter($ret, function($item) {
            return !empty($item['status']) || $item['url'] != 'rolemanage';
        });
        $ret = array_values($ret);
        if ($this->rid > 0 && !empty($ret)) {
            $teacherRoleModel = new TeacherRoleModel();
            $permissions = $teacherRoleModel->getPermissions($this->rid, $this->crid);
            $permissions = json_decode($permissions, true);
            if (empty($permissions) || !is_array($permissions)) {
                return $ret;
            }
            array_walk($ret, function(&$item, $k, $permissions) {
                if (in_array($item['mid'], $permissions)) {
                    $item['status'] = 1;
                } else {
                    $item['status'] = 0;
                }
                if (empty($item['children'])) {
                    return;
                }
                array_walk($item['children'], function(&$item, $k, $permissions) {
                    if (in_array($item['mid'], $permissions)) {
                        $item['status'] = 1;
                    }else {
                        $item['status'] = 0;
                    }
                }, $permissions);
            }, $permissions);
        }
        return $ret;
    }

    /**
     * 获取教师角色ID
     */
    public function getTeacherRoleAction() {
        $model = new TeacherRoleModel();
        return $model->getTeacherRole($this->tid, $this->crid);
    }
}

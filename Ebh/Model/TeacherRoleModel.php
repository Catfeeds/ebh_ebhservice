<?php
/**
 * TeacherRoleModel 教师角色
 * Author: ycq
 */
class TeacherRoleModel{
    public function __construct() { }

    /**
     * 获取教师角色列表
     * @param $crid 网校ID
     * @param bool $simple 是否返回简单值(roleid,rolename)
     * @param int $page 页码，为０不分页
     * @param int $pagesize,分页大小
     * @return int
     */
	public function getList($crid, $simple = false, $page = 0, $pagesize = 0) {
        $crid = intval($crid);
        $page = intval($page);
        $pagesize = intval($pagesize);
        if ($page > 0) {
            $pagesize = max(1, $pagesize);
            $offset = ($page - 1) * $pagesize;
        }

        if ($simple) {
            $fields = array('rid', 'rolename');
        } else {
            $fields = array('rid', 'rolename', 'category', 'remark', 'permissions', 'limitscope');
        }
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_teacher_roles` WHERE `crid`='.$crid.' AND `category`>0 ORDER BY `rid` DESC';
        if ($page > 0) {
            $sql .= ' LIMIT '.$offset.','.$pagesize;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 角色数统计
     * @param $crid
     * @return int
     */
    public function getCount($crid) {
	    $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_teacher_roles` WHERE `crid`='.intval($crid).' AND `category`>0';
	    $ret = Ebh()->db->query($sql)->row_array();
	    if (!empty($ret)) {
	        return intval($ret['c']);
        }
        return 0;
    }

    /**
     * 添加教师角色
     * @param $crid 网校ID
     * @param $params 字段参数
     * @return bool
     */
    public function add($crid, $params) {
        if (empty($params['rolename']) || empty($params['category'])) {
            return false;
        }
        $sets = array(
            'crid' => intval($crid),
            'category' => intval($params['category']),
            'rolename' => trim($params['rolename']),
            'remark' => isset($params['remark']) ? trim($params['remark']) : '',
            'limitscope' => isset($params['limitscope']) ? intval($params['limitscope']) : 0,
            'permissions' => '',
            'dateline' => SYSTIME
        );
        if (isset($params['permissions'])) {
            if (is_array($params['permissions'])) {
                $permissions = array_map('intval', $params['permissions']);
                $permissions = array_filter($permissions, function($per) {
                    return $per > 0;
                });
                $sets['permissions'] = json_encode($permissions);
            } else {
                $sets['permissions'] = trim($params['permissions']);
            }
        }
        return Ebh()->db->insert('ebh_teacher_roles', $sets);
    }

    /**
     * 更新教师角色
     * @param $crid 网校ID
     * @param $params 字段参数
     * @return bool
     */
    public function update($crid, $params) {
        if (empty($params['rid'])) {
            return false;
        }
        $rid = intval($params['rid']);
        $sets = array('crid' => intval($crid));
        if (isset($params['rolename'])) {
            $sets['rolename'] = trim($params['rolename']);
            if (empty($sets['rolename'])) {
                return false;
            }
        }
        if (isset($params['category'])) {
            $sets['category'] = intval($params['category']);
        }
        if (isset($params['remark'])) {
            $sets['remark'] = trim($params['remark']);
        }
        if (isset($params['limitscope'])) {
            $sets['limitscope'] = intval($params['limitscope']);
        }
        if (isset($params['permissions'])) {
            if (is_array($params['permissions'])) {
                $permissions = array_map('intval', $params['permissions']);
                $permissions = array_filter($permissions, function($per) {
                    return $per > 0;
                });
                $sets['permissions'] = json_encode($permissions);
            } else {
                $sets['permissions'] = trim($params['permissions']);
            }
        }
        return Ebh()->db->update('ebh_teacher_roles', $sets, '`rid`='.$rid);
    }

    /**
     * 角色详情
     * @param $rid 角色ID
     * @param $crid 网校ID
     * @return mixed
     */
    public function getDetail($rid, $crid) {
        $sql = 'SELECT `rid`,`rolename`,`category`,`remark`,`permissions` FROM `ebh_teacher_roles` WHERE `rid`='.intval($rid).' AND `crid`='.intval($crid);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 角色权限ID集
     * @param $rid 角色ID
     * @param $crid 网校ID
     * @return mixed
     */
    public function getPermissions($rid, $crid) {
        $sql = 'SELECT `permissions` FROM `ebh_teacher_roles` WHERE `rid`='.intval($rid).' AND `crid`='.intval($crid);
        $permissions = Ebh()->db->query($sql)->row_array();
        if (!empty($permissions)) {
            return $permissions['permissions'];
        }
        return '';
    }

    /**
     * 删除角色
     * @param $rid 角色ID
     * @param $crid 网校ID
     * @return bool
     */
    public function remove($rid, $crid) {
        $rid = intval($rid);
        $crid = intval($crid);
        Ebh()->db->begin_trans();
        Ebh()->db->delete('ebh_teacher_roles', array('rid' => $rid, 'crid' => $crid));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->update('ebh_roomteachers', array('role' => 1), array('crid' => $crid, 'role'=> $rid));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return true;
    }

    /**
     * 角色下用户列表
     * @param $rid 角色ID
     * @param $crid 网校ID
     * @param $k 查询关键字
     * @param int $page 分页页码
     * @param int $pagesize 分页页大小
     * @param bool $setKey 是否以用户ID为键
     * @return mixed
     */
    public function getUserList($rid, $crid, $k, $page = 0, $pagesize = 0, $setKey = false) {
        $crid = intval($crid);
        $rid = intval($rid);
        $page = intval($page);
        $pagesize = intval($pagesize);
        if ($page > 0) {
            $pagesize = max(1, $pagesize);
            $offset = ($page - 1) * $pagesize;
        }
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status` IN (1,2)',
            '`c`.`status`=1',
            '`d`.`rid`='.$rid
        );
        if ($k != '') {
            $k = Ebh()->db->escape('%'.$k.'%');
            $wheres[] = '(`c`.`realname` LIKE '.$k.' OR `c`.`username` LIKE '.$k.')';
        }
        $sql = 'SELECT `c`.`uid`,`c`.`realname`,`c`.`username`,`c`.`sex`,`c`.`face`,`c`.`groupid`,`c`.`lastlogintime`,`c`.`lastloginip`,IF(`c`.`mobile`=\'\',`a`.`mobile`,`c`.`mobile`) AS `mobile`,`a`.`status` 
                FROM `ebh_roomteachers` `a` 
                JOIN `ebh_teachers` `b` ON `b`.`teacherid`=`a`.`tid` 
                JOIN `ebh_users` `c` ON `c`.`uid`=`a`.`tid` 
                JOIN `ebh_teacher_roles` `d` ON `d`.`rid`=`a`.`role` AND `d`.`crid`=`a`.`crid` 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`tid` DESC';
        if ($page > 0) {
            $sql .= ' LIMIT '.$offset.','.$pagesize;
        }
        return Ebh()->db->query($sql)->list_array($setKey ? 'uid' : '');
    }

    /**
     * 角色用户统计
     * @param $rid 角色ID
     * @param $crid 网校ID
     * @param $k 查询关键字
     * @return int
     */
    public function getUserCount($rid, $crid, $k) {
        $crid = intval($crid);
        $rid = intval($rid);
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status` IN(1,2)',
            '`c`.`status`=1',
            '`d`.`rid`='.$rid
        );
        if ($k != '') {
            $k = Ebh()->db->escape('%'.$k.'%');
            $wheres[] = '(`c`.`realname` LIKE '.$k.' OR `c`.`username` LIKE '.$k.')';
        }
        $sql = 'SELECT COUNT(1) AS `c` 
                FROM `ebh_roomteachers` `a` 
                JOIN `ebh_teachers` `b` ON `b`.`teacherid`=`a`.`tid` 
                JOIN `ebh_users` `c` ON `c`.`uid`=`a`.`tid` 
                JOIN `ebh_teacher_roles` `d` ON `d`.`rid`=`a`.`role` AND `d`.`crid`=`a`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return intval($ret['c']);
        }
        return 0;
    }

    /**
     * 设置角色用户
     * @param $rid 角色ID
     * @param $tids 教师ID
     * @param $crid 网校ID
     * @return bool
     */
    public function setUser($rid, $tids, $crid) {
        if (is_array($tids)) {
            $tids = array_map('intval', $tids);
        } else {
            $tids = array(intval($tids));
        }
        $tids = array_filter($tids, function($tid) {
           return $tid > 3 || $tid == 1;
        });
        $rid = intval($rid);
        $crid = intval($crid);
        if ($crid < 1 || empty($tids)) {
            return false;
        }
        if ($rid == 1) {
            return Ebh()->db->update('ebh_roomteachers', array('role' => $rid), '`crid`='.$crid.' AND `tid` IN('.implode(',', $tids).')');
        }
        $otids = Ebh()->db->query('SELECT `tid` FROM `ebh_roomteachers` WHERE `role`='.$rid.' AND `crid`='.$crid)->list_field();
        $otids = array_map('intval', $otids);
        //删除的教师ID集
        $deleteids = array_diff($otids, $tids);
        //新增的教师ID集
        $tids = array_diff($tids, $otids);
        $rows = 0;
        if (!empty($deleteids)) {
            $rows = Ebh()->db->update('ebh_roomteachers', array('role' => 1), '`crid`='.$crid.' AND `tid` IN('.implode(',', $deleteids).')');
        }
        if (!empty($tids)) {
            $rows += Ebh()->db->update('ebh_roomteachers', array('role' => $rid), '`crid`='.$crid.' AND `tid` IN('.implode(',', $tids).')');
        }
        return $rows;
    }

    /**
     * 查询教师已分配的角色
     * @param $tid 教师ID
     * @param $crid 网校ID
     * @return int
     */
    public function getTeacherRole($tid, $crid) {
        $tid = intval($tid);
        $crid = intval($crid);
        $sql = 'SELECT `a`.`role`,`b`.`rolename`,`a`.`status`,IFNULL(`b`.`rid`,0) AS `rid`,`b`.`permissions`,`b`.`category`,`b`.`limitscope` FROM `ebh_roomteachers` `a` LEFT JOIN `ebh_teacher_roles` `b` ON `b`.`rid`=`a`.`role` AND `b`.`crid`=`a`.`crid` WHERE `a`.`tid`='.$tid.' AND `a`.`crid`='.$crid;
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        if (empty($ret['rid'])) {
            $role = intval($ret['role']);
            if (in_array($role, array(1, 2, 3))) {
                return $role;
            }
            return 1;
        }
        if (intval($ret['status']) != 1) {
            return 1;
        }
        return $ret;
    }
}

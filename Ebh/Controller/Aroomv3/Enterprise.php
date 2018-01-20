<?php

/**
 * 企业网校
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/4/25
 * Time: 13:52
 */
class EnterpriseController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //部门列表树
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'crname' => array(
                    'name' => 'crname',
                    'require' => true,
                    'type' => 'string'
                ),
                'show_teachers' => array(
                    'name' => 'show_teachers',
                    'require' => false,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //添加部门
            'addDeptmentAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                //上级部门ID
                'superiorid' => array(
                    'name' => 'superiorid',
                    'require' => true,
                    'type' => 'int'
                ),
                //部门名称
                'deptname' => array(
                    'name' => 'deptname',
                    'require' => true,
                    'type' => 'string'
                ),
                //排序号
                'displayorder' => array(
                    'name' => 'displayorder',
                    'require' => false,
                    'type' => 'int'
                ),
                //部门编号
                'code' => array(
                    'name' => 'code',
                    'type' => 'string',
                    'default' => ''
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            //导入部门
            'importDeptmentsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                //上级部门ID
                'superiorid' => array(
                    'name' => 'superiorid',
                    'require' => true,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'deptments' => array(
                    'name' => 'deptments',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //修改部门
            'updateDeptmentAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                //部门ID
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
                //部门名称
                'deptname' => array(
                    'name' => 'deptname',
                    'require' => true,
                    'type' => 'string'
                ),
                //排序号
                'displayorder' => array(
                    'name' => 'displayorder',
                    'require' => false,
                    'type' => 'int'
                ),
                //部门编号
                'code' => array(
                    'name' => 'code',
                    'require' => false,
                    'type' => 'string'
                )
            ),
            //删除部门
            'removeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'require' => true
                )
            ),
			//讲师的部门
            'teacherDeptsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
			//部门课程
            'deptCourseAction' => array(
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
				'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
            //判断是否底层部门
            'isLastDeptAction' => array(
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
			//部门信息
			'detailAction' =>array(
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
				'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
			//部门课程
			'classCourseCountAction' =>array(
                'classids' => array(
                    'name' => 'classids',
                    'require' => true,
                    'type' => 'string'
                ),
            ),
			//子部门
			'subDepartmentAction' =>array(
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
				'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
			//批量修改员工部门
            'batchChangeDeptAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                //部门ID
                'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
                //学员ID
                'staffid' => array(
                    'name' => 'staffid',
                    'require' => true,
                    'type' => 'array'
                )
            ),
			//子部门的员工
			'subDeptUsersAction' =>array(
                'classids' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'string'
                ),
				'crid'=> array(
					'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
				),
				'q'=> array(
					'name' => 'q',
                    'default' => '',
                    'type' => 'string'
				)
				
            ),
			'deptUserCountAction'=>array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
				'classids' => array(
                    'name' => 'classids',
                    'require' => true,
                    'type' => 'string'
                ),
			),
			//部门绑定人数
			'bindCountAction'=>array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
				'classids' => array(
                    'name' => 'classids',
                    'require' => true,
                    'type' => 'string'
                ),
				'bind'=>array(
					'name' => 'bind',
                    'require' => true,
                    'type' => 'int'
				)
			),
			//绑定的员工
			'bindUserAction'=>array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
				'classid' => array(
                    'name' => 'classid',
                    'require' => true,
                    'type' => 'int'
                ),
				'bind'=>array(
					'name' => 'bind',
                    'default' => 0,
                    'type' => 'int'
				),
				'pagesize'=>array(
					'name' => 'pagesize',
                    'default' => 0,
                    'type' => 'int'
				),
				'page'=>array(
					'name' => 'page',
                    'default' => 0,
                    'type' => 'int'
				),
			),
			'teacherCourseAction' =>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
			),
			'deptTeacherListAction' =>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
			),
            'getDeptListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'deptName' => array(
                    'name' => 'deptName',
                    'type' => 'string',
                    'default' => ''
                ),
                'number' => array(
                    'name' => 'number',
                    'type' => 'int',
                    'default' => 0
                )
            ),
            'verifyAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'deptName' => array(
                    'name' => 'deptName',
                    'type' => 'string',
                    'require' => true
                ),
                'code' => array(
                    'name' => 'code',
                    'type' => 'string',
                    'require' => true
                )
            ),
            'importDeptmentsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                //上级部门ID
                'superiorId' => array(
                    'name' => 'superiorId',
                    'require' => true,
                    'type' => 'int'
                ),
                //批量部门数据
                'deptments' => array(
                    'name' => 'deptments',
                    'require' => true,
                    'type' => 'string'
                )
            )
        );
    }

    public function indexAction() {
        $showTeachers = !empty($this->show_teachers);
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
                $depts = $classTeacherModel->getDeptsForTeacherWithPath($this->crid, $parents);
                unset($parents);
                if (empty($depts)) {
                    return array();
                }
            }
        }
        $model = new ClassesModel();
        if (empty($depts)) {
            $depts = $model->getDeptmentTree($this->crid, true);
        }
        if (empty($depts)) {
            //部门数据为空，生成顶级部门
            $rootid = $model->initDeptment($this->crid, $this->crname);
            return array(
                'classid' => intval($rootid),
                'classname' => $this->crname,
                'path' => '/'.$this->crname,
                'category' => 1,
                'superior' => 0,
                'stunum' => 0,
                'code' => '-',
                'lft' => 1,
                'rgt' => 2
            );
        }
        $roots = array_filter($depts, function($dept) {
            return !empty($dept['category']);
        });
        if (!empty($role['limitscope']) && empty($roots)) {
            $depts[-1] = array(
                'classid' => -1,
                'classname' => $this->crname,
                'path' => '/'.$this->crname,
                'category' => 1,
                'superior' => 0,
                'stunum' => 0,
                'code' => '-',
                'displayorder' => 0,
                'lft' => 1,
                'rgt' => 2
            );
        }
        unset($roots);
        $reset = false;
        foreach ($depts as $dept) {
            if (empty($dept['category']) && !isset($depts[$dept['superior']])) {
                $reset = true;
                break;
            }
        }
        if (!$reset && empty($role['limitscope'])) {
            $lfts = array_column($depts, 'lft');
            $rgts = array_column($depts, 'rgt');
            $lfts = array_merge($lfts, $lfts);
            $rgt = count($depts) * 2;
            $lfts = array_flip($lfts);
            $rgts = array_fill(1, $rgt, 0);
            $rgts = array_intersect_key($rgts, $lfts);
            $rgtCount = count($rgts);
            if ($rgtCount != $rgt || $rgtCount != count($lfts)) {
                $reset = true;
            }
            unset($rgts, $lfts);
        }
        if ($reset) {
            //重置部门结构
            $depts = $this->resetDept($this->crid, $depts, $this->crname, $model);
        }
        if ($showTeachers) {
            //注入讲师数据
            $classids = array_column($depts, 'classid');
            $teachers = $model->getTeachers($classids, $this->uid);
        }
        if (!empty($teachers)) {
            array_walk($depts, function(&$deptment, $k, $teachers) {
                foreach ($teachers as $teacher) {
                    if (!empty($teacher['username']) && $teacher['classid'] == $deptment['classid']) {
                        $deptment['teacher'][] = $teacher;
                    }
                }
            }, $teachers);
        }
        return $depts;
    }

    /**
     * 添加部门
     */
    public function addDeptmentAction() {
        $model = new ClassesModel();
        $stunum = 0;
        $ret = $model->addDeptment(
            $this->superiorid,
            $this->deptname,
            $this->code,
            $this->crid,
            intval($this->displayorder),
            $stunum
        );
        if (!empty($ret) && $this->uid > 0) {
            //将添加教师加入班级
            $classTeacherModel = new ClassTeacherModel();
            $classTeacherModel->addTeacher($this->uid, $ret);
        }
        return array(
            'newid' => $ret,
            'stunum' => $stunum
        );
    }

    /**
     * 修改部门
     */
    public function updateDeptmentAction() {
        $model = new ClassesModel();
        $params = array(
            'classname' => $this->deptname,
            'displayorder' => intval($this->displayorder)
        );
        $code = trim($this->code);
        if ($code != '') {
            $params['code'] = $code;
        }
        return $model->updateDeptment(
            $this->classid,
            $this->crid,
            $params
        );
    }

    /**
     * 删除部门，当部门下无人员时级联删除
     * @return bool|mixed
     */
    public function removeAction() {
        $model = new ClassesModel();
        return $model->removeDeptment($this->classid, $this->crid);
    }

	/**
     * 讲师所在的部门
     */
	public function teacherDeptsAction(){
		$model = new ClassesModel();
        return $model->getTeacherDepts(
            $this->crid,
            $this->uid
        );
	}
	
	/**
     * 部门课程
     */
	public function deptCourseAction(){
		$model = new ClassesModel();
        return $model->getDeptCourse(
            $this->classid,
			$this->crid
        );
	}

    /**
     * 判断是否底层部门
     * @return mixed
     */
	public function isLastDeptAction() {
        $model = new ClassesModel();
        return $model->isLastDept($this->classid);
    }

	/**
     * 部门信息
     */
	public function detailAction(){
		$model = new ClassesModel();
        return $model->getDept(
            $this->classid,
			$this->crid
        );
	}
	
	/*
	 *班级课程数量
	*/
	public function classCourseCountAction(){
		$model = new ClasscourseModel();
        return $model->getFolderidCountByClassid(
            $this->classids
        );
	}
	
	/*
	 *子部门
	 */
	public function subDepartmentAction(){
		$model = new ClassesModel();
        return $model->getSubDepartment(
            array('classid'=>$this->classid,'crid'=>$this->crid)
        );
	}
	/**
     * 批量修改员工部门
     * @return bool
     */
	public function batchChangeDeptAction() {
        $model = new ClassesModel();
        return $model->batchChangeDept($this->classid, $this->staffid, $this->crid);
    }

	/*
	 *子部门的员工
	*/
	public function subDeptUsersAction(){
		$model = new ClassesModel();
        return $model->getSubDeptUsers(
            array('classids'=>$this->classids,'crid'=>$this->crid,'nolimit'=>1,'q'=>$this->q)
        );
	}
	
	/*
	 *多个部门学生数量
	*/
	public function deptUserCountAction(){
		$model = new ClassesModel();
        return $model->getDeptUserCount(
            array('classids'=>$this->classids,'crid'=>$this->crid)
        );
	}
	
	/*
	 *多个部门绑定数量
	*/
	public function bindCountAction(){
		$model = new ClassesModel();
        $uidlist = $model->getBindStudent(array('crid'=>$this->crid));
		if(empty($uidlist) && $this->bind==1){
			return array();
		}
		$uids = array_keys($uidlist);
		$uids = implode(',',$uids);
		$countlist = $model->getDeptUserCount(array('classids'=>$this->classids,'crid'=>$this->crid,'uids'=>$uids,'bind'=>$this->bind));
		return $countlist;
	}
	
	/*
	教师有权限的部门
	*/
	public function teacherCourseAction(){
		$model = new ClassesModel();
		$param['uid'] = $this->uid;
		$param['crid'] = $this->crid;
		return $model->getTeacherCourse($param);
	}
	
	/*
	 * 企业微信绑定的员工
	*/
	public function bindUserAction(){
		$model = new ClassesModel();
		if(empty($this->bind)){//全部员工
			$userlist = $model->getSubDeptUsers(
				array('classids'=>$this->classid,'crid'=>$this->crid,'pagesize'=>$this->pagesize,'page'=>$this->page)
			);
			if(!empty($userlist)){
				$uids = array_column($userlist,'uid');
				$uids = implode(',',$uids);
				$bindlist = $model->getBindStudent(array('crid'=>$this->crid,'uids'=>$uids));
				
				foreach($userlist as &$user){
					$uid = $user['uid'];
					$user['bind'] = empty($bindlist[$uid])?0:1;
				}
			}
			$usercount = $model->getDeptUserCount(array('classids'=>$this->classid,'crid'=>$this->crid));
			$usercount = array_values($usercount);
			$usercount = empty($usercount)?0:$usercount[0]['count'];
		} else {
			
			$bindlist = $model->getBindStudent(array('crid'=>$this->crid));
			if($this->bind == 1 && empty($bindlist)){
				return array('userlist'=>array(),'usercount'=>0);
			}
			$uids = array_keys($bindlist);
			$uids = implode(',',$uids);
			$userlist = $model->getSubDeptUsers(
				array('classids'=>$this->classid,'crid'=>$this->crid,'pagesize'=>$this->pagesize,'page'=>$this->page,'uids'=>$uids,'bind'=>$this->bind)
			);
			$usercount = $model->getDeptUserCount(array('classids'=>$this->classid,'crid'=>$this->crid,'uids'=>$uids,'bind'=>$this->bind));
			$usercount = array_values($usercount);
			$usercount = empty($usercount)?0:$usercount[0]['count'];
		}
		
		return array('userlist'=>$userlist,'usercount'=>$usercount);
	}
	
	/*
	部门有权限的教师列表
	*/
	public function deptTeacherListAction(){
		$model = new ClassesModel();
		$param['crid'] = $this->crid;
		$param['classid'] = $this->classid;
		$teacherlist = $model->deptTeacherList($param);
		return $teacherlist;
	}

    /**
     * 部门帮助菜单
     * @return array
     */
    public function getDeptListAction() {
        $deptModel = new ClassesModel();
	    return $deptModel->getDeptList($this->crid, trim($this->deptName), $this->number > 0 ? $this->number : null);
    }

    /**
     * 验证部门是否有效(部门名称与编号是否有效对应)
     * @return bool
     */
    public function verifyAction() {
        $deptModel = new ClassesModel();
        return $deptModel->verify($this->deptName, $this->code, $this->crid);
    }

    /**
     * 导入部门
     */
    public function importDeptmentsAction() {
        $lockid = 'lock-dept-'.$this->crid;
        $lock = Ebh()->cache->get($lockid);
        if ($lock !== null) {
            return array(
                'errno' => 100,
                'msg' => '部门导入功能被锁，请稍候再试'
            );
        }
        $depts = json_decode($this->deptments, true);
        if (empty($depts) || !is_array($depts)) {
            if (empty($depts)) {
                return array(
                    'errno' => 1,
                    'msg' => '部门数据不能为空'
                );
            }
        }
        $depts = array_filter($depts, function($dept) {
            return !empty($dept['deptname']) && isset($dept['code']);
        });
        if (empty($depts)) {
            return array(
                'errno' => 1,
                'msg' => '部门数据不能为空'
            );
        }
        Ebh()->cache->set($lockid, SYSTIME);
        $model = new ClassesModel();
        $ret = $model->importDeptments($this->crid, $this->superiorId, $depts);
        Ebh()->cache->delete($lockid);
        if ($ret === true) {
            return array(
                'errno' => 0
            );
        }
        if ($ret === false) {
            return array(
                'errno' => 2,
                'msg' => '上级部门不存在，导入失败'
            );
        }
        return array(
            'errno' => 3,
            'msg' => $ret
        );
    }

    /**
     * 重置部门
     * @param int $crid 网校ID
     * @param array $depts 部门
     * @param string $crname 网校名
     * @param object $model 数据库Model
     * @returns bool
     */
    private function resetDept($crid, $depts, $crname, $model) {
        $root = null;
        $init = false;
        $categorys = $superiors = $classids = array();
        foreach ($depts as $dept) {
            $categorys[] = $dept['category'];
            $superiors[] = $dept['superior'];
            $classids[] = $dept['classid'];
        }
        array_multisort($categorys, SORT_DESC, SORT_NUMERIC,
            $superiors, SORT_ASC, SORT_NUMERIC,
            $classids, SORT_ASC, SORT_NUMERIC, $depts);
        $classids = array_column($depts, 'classid');
        $depts = array_combine($classids, $depts);
        unset($categorys, $superiors, $classids);
        //找到顶级部门
        $roots = array_filter($depts, function($dept) {
            return !empty($dept['category']);
        });
        if (!empty($roots)) {
            $deptCount = count($depts);
            if ($deptCount > 1) {
                $weights = array_map(function($rootItem) use($crname) {
                    return $rootItem['classname'] == $crname ? 1 : 0;
                }, $roots);
                array_multisort($weights, SORT_DESC, SORT_NUMERIC, $roots);
            }
            $rootIndex = key($roots);
            $root = $roots[$rootIndex];
            $root['category'] = 1;
            $root['superior'] = 0;
            $root['lft'] = 1;
            $root['rgt'] = count($depts) * 2;
            $root['path'] = '/'.$crname;
            $root['classname'] = $crname;
            unset($depts[$rootIndex]);
        }
        if ($root === null) {
            //无顶级部门，新建
            $root = array(
                'classname' => $crname,
                'category' => 1,
                'superior' => 0,
                'lft' => 1,
                'rgt' => count($depts) * 2,
                'code' => 0,
                'path' => '/'.$crname
            );
            $rootid = $model->addRoot($root['classname'], $root['rgt'], $crid);
            if ($rootid !== false) {
                $root['classid'] = $rootid;
            }
            $init = true;
        }
        //上级索引
        $superiors = array_keys($depts);
        $superiors = array_flip($superiors);
        array_walk($depts, function(&$dept, $classid, $args) {
            //调整上级部门关联错误的部门到顶级部门下
            if ($dept['superior'] == 0 || !isset($args['superiors'][$dept['superior']])) {
                $dept['superior'] = $args['superiorid'];
            }
            $dept['category'] = 0;
        }, array(
            'superiorid' => $root['classid'],
            'superiors' => $superiors
        ));

        unset($superiors);
        $indexs = array();
        //将部门分层
        foreach ($depts as $k => $dept) {
            if ($dept['superior'] == $root['classid']) {
                $indexs[0][$k] = $dept['superior'];
                continue;
            }
            $indexs[1][$k] = $dept['superior'];
        }

        $keys = array_keys($indexs);
        array_multisort($keys, SORT_ASC, SORT_NUMERIC, $indexs);
        unset($keys);
        $grade = count($indexs);
        $loops = $grade > 1;

        while($loops) {
            $index = $grade - 1;
            $parent = $index - 1;
            $next = $grade;
            foreach ($indexs[$index] as $k => $item) {
                if (!isset($indexs[$parent][$item])) {
                    $indexs[$next][$k] = $item;
                    unset($indexs[$index][$k]);
                }
            }
            if (empty($indexs[$next])) {
                $loops = false;
            } else {
                $grade++;
            }
        }
        //将部门按级别嵌套
        while ($index = array_pop($indexs)) {
            foreach ($index as $key => $value) {
                $depts[$value]['children'][$key] = $depts[$key];
                $depts[$value]['children'][$key]['path'] = '';
                $depts[$value]['children'][$key]['lft'] = 0;
                $depts[$value]['children'][$key]['rgt'] = 0;
                unset($depts[$key]);
            }
        }

        $depts = reset($depts);
        $depts = $depts['children'];
        if (!empty($depts)) {
            $classids = array_keys($depts);
            array_multisort($classids, SORT_ASC, SORT_NUMERIC, $depts);
        }
        //设置部门路径参数
        $this->treePath($depts, $root['path'], 2);
        //将数据转为一维数组
        $depts = $this->singleDimensional($depts);
        if (!$init) {
            array_unshift($depts, $root);
        }
        if ($root['classid'] != -1) {
            $model->resetDeptment($depts, $crid);
        }
        return $depts;
    }

    /**
     * 递归设置部门路径参数
     * @param $depts 部门
     * @param $rootPath 根路径
     * @param int $lft 路径左值
     * @return int 返回路径右值
     */
    private function treePath(&$depts, $rootPath, $lft = 2) {
        foreach ($depts as &$dept) {
            $dept['lft'] = $lft++;
            $dept['path'] = $rootPath.'/'.$dept['classname'];
            if (empty($dept['children'])) {
                $dept['rgt'] = $lft++;
            } else {
                $displayorders = array_column($dept['children'], 'displayorder');
                $classids = array_keys($dept['children']);
                array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
                    $classids, SORT_DESC, SORT_NUMERIC, $dept['children']);
                $lft = $this->treePath($dept['children'], $rootPath.'/'.$dept['classname'], $lft);
                $dept['rgt'] = $lft++;
            }
        }
        return $lft;
    }

    /**
     * 递归将部门数据转为一维数组
     * @param $depts 部门
     * @return array
     */
    private function singleDimensional($depts) {
        if (empty($depts)) {
            return array();
        }
        $group = array();
        foreach ($depts as $dept) {
            if (empty($dept['children'])) {
                $group[] = $dept;
            } else {
                $subGroup = $this->singleDimensional($dept['children']);
                unset($dept['children']);
                $group[] = $dept;
                $group = array_merge($group, $subGroup);
            }
        }
        return $group;
    }
}
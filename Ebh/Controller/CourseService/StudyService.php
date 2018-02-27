<?php

/**
 * 学习相关服务接口
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/12/14
 * Time: 10:52
 */
class StudyServiceController extends Controller {
    const ORDER_ID_DESC = 0;
    const ORDER_VIEWNUM_DESC = 1;
    const ORDER_PRICE_DESC = 2;
    const ORDER_PRICE_ASC = 3;
    const ORDER_SRANK_ASC = 4;
    const ORDER_PRANK_ASC = 5;
    const ORDER_GRANK_ASC = 6;
    private $model;
    private $redis;
    //学习权限表
    private $userpermissions = array();
    //是否本校学生
    private $isStudent = false;
    //课程包报名数列表
    private $bundleReportCounts = array();
    //课程报名数列表
    private $itemReportCounts = array();
    public function __construct() {
        parent::init();
        $this->model = new StudyServiceModel();
        $this->redis = Ebh()->cache;
        if (isset($this->uid) && $this->uid > 0) {
            //读取用户开通权限
            $userpermissionModel = new UserpermisionsModel();
            $roomUserModel = new RoomUserModel();
            $this->userpermissions = $userpermissionModel->getService($this->uid);
            $this->isStudent = $roomUserModel->isAlumni($this->crid, $this->uid);
        }
    }
    public function parameterRules() {
        return array(
            //课程列表接口：课程包、本校课程、企业选课课程
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0,
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
                ),
                'ordertype' => array(
                    'name' => 'ordertype',
                    'type' => 'int',
                    'default' => 0
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',
                    'default' => 0,
                    'min' => 0
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'min' => 0
                ),
                'isfree' => array(
                    'name' => 'isfree',
                    'type' => 'boolean',
                    'default' => false
                ),
                'q' => array(
                    'name' => 'q',
                    'type' => 'string'
                ),
                'filterbuyed' => array(
                    'name' => 'filterbuyed',
                    'type' => 'boolean',
                    'default' => false
                ),
                'column' => array(//列数：用于主页课程列表
                    'name' => 'column',
                    'type' => 'int',
                    'default' => 0
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //课程包列表
            'bundleListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //学生课程列表+单课件列表
            'studyListAction' => array(
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'retain' => array(
                    'name' => 'retain',
                    'type' => 'boolean',
                    'default' => false
                ),
                'otherGroupType' => array(
                    'name' => 'otherGroupType',//企业选课分组方式：０-按来源网校分组，默认方式，1-按package分组
                    'type' => 'int',
                    'default' => 0,
                    'min' => 0,
                    'max' => 1
                )
            ),
            //获取网校课程服务分类列表
            'courseCategoryListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int'
                )
            ),
            //获取分类下的课程服务
            'serviceListBySortAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 0
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //自选课程列表，列表按package分组
            'manualServiceListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //单课列表
            'fineListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
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
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 0
                ),
                'ordertype' => array(
                    'name' => 'ordertype',
                    'type' => 'int',
                    'default' => 0
                ),
                'isfree' => array(
                    'name' => 'isfree',
                    'type' => 'boolean',
                    'default' => false
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //课程包详情
            'bundleAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int'
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //打包课程详情
            'sortAction' => array(
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'require' => true,
                    'min' => 1
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'requrie' => true,
                    'min' => 1
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //课程详情
            'courseInfoAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'itemid' => array(
                    'name' => 'itemid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int'
                ),
                'isinterior' => array(
                    'name' => 'isinterior',
                    'type' => 'boolean',
                    'default' => false
                )
            ),
            //教师所教课程
            'getTeacherCourseListAction' => array(
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
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 0
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
                ),
                'isinterior' => array(
            'name' => 'isinterior',
            'type' => 'boolean',
            'default' => false
        )
            )
        );
    }

    /**
     * 课程列表接口：课程包、本校课程、企业选课课程，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=464
     */
    public function indexAction() {
        $list = $this->getService($this->crid);
        if ($this->filterbuyed && $this->uid > 0) {
            //过滤已报名的服务项
            $list = array_filter($list, function($item) {
                return empty($item['haspower']);
            });
        }
        if (empty($list)) {
            return array(
                'pid' => 0,
                'count' => 0
            );
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        /**
         * 排序方式：0-时间[ID]降序，1-热度[浏览数]降序，2-价格降序，3-价格升序
         */
        $orderType = $this->ordertype;
        //同步浏览数、读取权限
        $subOrder = $this->ordertype;
        if (!in_array($subOrder, array(1, 2, 3))) {
            if ($this->sid !== null && $this->pid > 0) {
                $subOrder = 4;
            } else if ($this->pid > 0) {
                $subOrder = 5;
            } else {
                $subOrder = 6;
            }
        }
        //读取来源网校、服务包、服务包分类
        $classrooms = $packages = $sorts = array();
        $classroomOrderArgs = array();//网校排序参数
        $packageOrderArgs = array();//package排序参数
        $sortOrderArgs = array();//sort排序参数
        $itemOrderArgs = array();//项排序
        //定位的PID
        $localPid = 0;
        //定位的SID
        $localSid = null;
        //定位的Package是否包含未分类的项
        $hasOther = 0;
        //第一个package的排序号
        $packageDisplayorder = null;
        $isfree = $this->isfree;
        $isStudent = $this->isStudent;
        $this->bundleReportCounts = $this->model->reportCount($this->crid, array(), $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        $this->itemReportCounts = $this->model->reportCount($this->crid, array(), $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        foreach ($list as $index => $item) {
            if (isset($item['crid']) && !isset($classrooms[$item['crid']])) {
                $classrooms[$item['crid']] = array(
                    'crid' => $item['crid'],
                    'crname' => $item['crname'],
                    'subcount' => 0
                );
                $classroomOrderArgs[0][] = $item['displayorder'];
                $classroomOrderArgs[1][] = $item['crid'];
            }
            if (!isset($packages[$item['pid']])) {
                if (isset($item['crid'])) {
                    //企业选课package
                    $pcrid = $item['crid'];
                    $packageOrderArgs[0][] = $pcrid;
                    $packageOrderArgs[1][] = $item['pdisplayorder'];
                } else if (isset($item['pcrid']) && $item['pcrid'] != $this->crid) {
                    //本校引用别的网校的package
                    $pcrid = $item['pcrid'];
                    $packageOrderArgs[0][] = 0;
                    $packageOrderArgs[1][] = 0;
                } else {
                    //本校的package
                    $pcrid = 0;
                    $packageOrderArgs[0][] = -1;
                    $packageOrderArgs[1][] = $item['pdisplayorder'];
                    if ($packageDisplayorder === null) {
                        $packageDisplayorder = intval($item['pdisplayorder']);
                    }
                }
                $packages[$item['pid']] = array(
                    'pid' => $item['pid'],
                    'pname' => $item['pname'],
                    'subcount' => 0,
                    'crid' => $pcrid
                );
                $packageOrderArgs[2][] = $item['pid'];
                if (isset($item['crid'])) {
                    $classrooms[$item['crid']]['subcount']++;
                }
                if ($this->pid == $item['pid']) {
                    $localPid = $this->pid;
                    $packages[$item['pid']]['cur'] = 1;
                }
            }
            if ($item['sid'] > 0 && !isset($sorts[$item['sid']])) {
                $sorts[$item['sid']] = array(
                    'sid' => $item['sid'],
                    'sname' => $item['sname'],
                    'pid' => $item['pid'],
                    'crid' => $pcrid
                );
                $sortOrderArgs[0][] = $item['sdisplayorder'];
                $sortOrderArgs[1][] = $item['sid'];
                $packages[$item['pid']]['subcount']++;
                if ($localPid > 0 && $this->sid !== null && $this->sid == $item['sid']) {
                    $localSid = $this->sid;
                    $sorts[$item['sid']]['cur'] = 1;
                }
            } else if ($item['sid'] == 0 && !isset($packages[$item['pid']]['hasother'])) {
                $packages[$item['pid']]['hasother'] = 1;
                $packages[$item['pid']]['subcount']++;
                if ($this->pid == $item['pid']) {
                    $hasOther = 1;
                }
                if ($localPid > 0 && $this->sid !== null && $this->sid == $item['sid']) {
                    $localSid = $this->sid;
                }
            }
            //本校免费课程对本校学生免费
            if ($isStudent && !empty($item['isschoolfree'])) {
                $list[$index]['price'] = 0;
            }
            if (empty($item['items'])) {
                if (!empty($item['islimit']) && isset($this->itemReportCounts[$payitem['itemid']]) && $this->itemReportCounts[$item['itemid']]['c'] >= $item['limitnum']) {
                    //本校课程限制报名并且报名人数达到限制数，禁止课程报名
                    $list[$index]['cannotpay'] = 1;
                }
                $viewnum = $this->redis->hget('folderviewnum', $item['folderid'], false);
                if (!empty($viewnum)) {
                    $list[$index]['viewnum'] = $viewnum;
                }
                $item['haspower'] = isset($this->userpermissions[$item['folderid']]) ? 1 : 0;
                if (empty($item['crid']) && !empty($item['isschoolfree']) && $isStudent) {
                    //本校免费课程价格置0
                    $item['price'] = 0;
                }
            }
            //处理课程包，打包课程
            if (!empty($item['items'])) {
                $this->tagged($list[$index], $subOrder);
            }
            //网校内部课程
            if ($this->isinterior && empty($item['crid']) && $this->uid > 0 && !$isStudent) {
                $list[$index]['cannotpay'] = 1;
            }
            if (!empty($item['bid'])) {
                $itemOrderArgs[0][] = 0;
                if ($orderType == self::ORDER_VIEWNUM_DESC) {
                    $itemOrderArgs[1][] = $list[$index]['viewnum'];
                } else if ($orderType == self::ORDER_PRICE_ASC || $orderType == self::ORDER_PRICE_DESC) {
                    $itemOrderArgs[1][] = $item['price'];
                } else {
                    $itemOrderArgs[1][] = $item['bdisplayorder'];
                }
                $itemOrderArgs[2][] = $item['bid'];
                unset($list[$index]['bdisplayorder'], $list[$index]['pdisplayorder'], $list[$index]['sdisplayorder']);
                continue;
            }
            if (!empty($item['showbysort'])) {
                $itemOrderArgs[0][] = 1;
                if ($orderType == self::ORDER_VIEWNUM_DESC) {
                    $itemOrderArgs[1][] = $list[$index]['viewnum'];
                } else if ($orderType == self::ORDER_PRICE_ASC || $orderType == self::ORDER_PRICE_DESC) {
                    $itemOrderArgs[1][] = $list[$index]['price'];
                } else {
                    $itemOrderArgs[1][] = $item['sdisplayorder'];
                }
                $itemOrderArgs[2][] = $item['sid'];
                unset($list[$index]['pdisplayorder'], $list[$index]['sdisplayorder']);
                continue;
            }
            $itemOrderArgs[0][] = empty($item['crid']) ? 2 : 3;
            if ($orderType == self::ORDER_VIEWNUM_DESC) {
                $itemOrderArgs[1][] = $item['viewnum'];
            } else if ($orderType == self::ORDER_PRICE_DESC || $orderType == self::ORDER_PRICE_ASC) {
                $itemOrderArgs[1][] = $item['price'];
            } else if ($this->pid > 0 && $this->sid !== null) {
                $itemOrderArgs[1][] = $item['srank'];
            } else if ($this->pid > 0) {
                $itemOrderArgs[1][] = $item['prank'];
            } else {
                $itemOrderArgs[1][] = $item['grank'];
            }
            $itemOrderArgs[2][] = $item['itemid'];
            unset($list[$index]['displayorder'], $list[$index]['pdisplayorder'], $list[$index]['sdisplayorder'], $list[$index]['pcrid'], $list[$index]['grank'], $list[$index]['prank'], $list[$index]['srank']);
        }
        //排序classrooms
        if (!empty($classrooms)) {
            array_multisort($classroomOrderArgs[0], SORT_ASC, SORT_NUMERIC,
                $classroomOrderArgs[1], SORT_DESC, SORT_NUMERIC, $classrooms);
        }
        //排序packages：本校的package,本校item引用的package,企业选课的package
        array_multisort($packageOrderArgs[0], SORT_ASC, SORT_NUMERIC,
            $packageOrderArgs[1], SORT_ASC, SORT_NUMERIC,
            $packageOrderArgs[2], SORT_DESC, SORT_NUMERIC, $packages);
        //排序sorts
        if (!empty($sorts)) {
            array_multisort($sortOrderArgs[0], SORT_ASC, SORT_NUMERIC,
                $sortOrderArgs[1], SORT_DESC, SORT_NUMERIC, $sorts);
        }
        unset($classroomOrderArgs, $packageOrderArgs, $sortOrderArgs);
        $sort = in_array($orderType, array(1, 2)) ? SORT_DESC : SORT_ASC;
        array_multisort($itemOrderArgs[0], SORT_ASC, SORT_NUMERIC,
            $itemOrderArgs[1], $sort, SORT_NUMERIC,
            $itemOrderArgs[2], SORT_DESC, SORT_NUMERIC, $list);
        unset($itemOrderArgs);
        if (in_array($this->column, array(3, 4)) && $packageDisplayorder > 0) {
            $firstPk = key($packages);
            $localPid = $this->pid = $packages[$firstPk]['pid'];
            $packages[$firstPk]['cur'] = 1;
        }
        $q = trim($this->q);
        $pid = $this->pid;
        $sid = $this->sid;
        if ($pid > 0) {
            $sorts = array_filter($sorts, function($sort) use($pid) {
                return $sort['pid'] == $pid;
            });
        }
        //根据条件过滤服务项
        if ($q != '' || !empty($isfree) || $pid > 0) {
            $list = array_filter($list, function($item) use($isfree, $q, $pid, $sid) {
                if ($pid > 0 && $pid != $item['pid']) {
                    return false;
                }
                if ($sid !== null && $sid != $item['sid']) {
                    return false;
                }
                if (!empty($isfree) && $item['price'] > 0) {
                    return false;
                }
                if ($q == '') {
                    return true;
                }
                return stripos($item['foldername'], $q) !== false;
            });
            $list = array_values($list);
        }
        $count = count($list);
        //分页
        if ($this->pagesize > 0) {
            $page = max(1, $this->page);
            $offset = ($page - 1) * $this->pagesize;
            $list = array_slice($list, $offset, $this->pagesize);
        }
        $list = $this->group($list, $packages, $this->column, $this->pid);
        $ret = array(
            'classrooms' => $classrooms,
            'packages' => $packages,
            'sorts' => $sorts,
            'count' => $count,
            'items' => $list,
            'pid' => $localPid,
            'hasother' => $hasOther
        );
        if ($localSid !== null) {
            $ret['sid'] = $localSid;
        }
        return $ret;
    }

    /**
     * 课程包列表，用于Plate模板"课程包模块"
     * 接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=459
     */
    public function bundleListAction() {
        $list = $this->getService($this->crid);
        if (empty($list)) {
            return array();
        }
        $list = array_filter($list, function($item) {
            return isset($item['bid']) && !empty($item['display']);
        });
        if (empty($list)) {
            return array();
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        $limitBundles = array_filter($list, function($bundle) {
           return !empty($bundle['islimit']);
        });
        $bids = !empty($limitBundles) ? array_column($limitBundles, 'bid') : array();
        unset($limitBundles);
        $this->bundleReportCounts = $this->model->reportCount($this->crid, $bids, $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        //本校内部服务，只对本校学生开放报名
        $disabled = $this->isinterior && $this->uid > 0 && !$this->isStudent;
        array_walk($list, function(&$item, $index, $disabled) {
            $this->tagged($item, 0, 0);
            if ($disabled) {
                $item['cannotpay'] = 1;
            }
        }, $disabled);
        //课程包排序
        $displayorders = $bids = array();
        foreach ($list as &$bundle) {
            $displayorders[] = $bundle['bdisplayorder'];
            $bids[] = $bundle['bid'];
            unset($bundle['bdisplayorder'], $bundle['display'], $bundle['pdisplayorder'], $bundle['sdisplayorder'], $bundle['located']);
        }
        array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
            $bids, SORT_DESC, SORT_NUMERIC, $list);
        return $list;
    }

    /**
     * 接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=460
     * 学生课程列表+单课件列表，课程分组按分组中的最近开通课程时间降序排序
     * 课程分本校课程与企业选课课程
     */
    public function studyListAction() {
        $userpermisionModel = new UserpermisionsModel();
        $ret['folders'] = $userpermisionModel->getService($this->uid, $this->crid, 0);
        $coursewares = $userpermisionModel->getService($this->uid, $this->crid, 1);
        $now = SYSTIME - 86400;
        if (!$this->retain) {
            //过滤过期课程、课件
            $ret['folders'] = array_filter($ret['folders'], function($folder) use($now) {
                return $folder['enddate'] > $now;
            });
        }
        $coursewares =  array_filter($coursewares, function($courseware) use($now) {
            return $courseware['enddate'] > $now;
        });
        if (!empty($ret['folders'])) {
            $list = $this->getService($this->crid);
            $itemids = array_column($ret['folders'], 'itemid');
            $itemids = array_flip($itemids);
            $sublist = array_filter($list, function($item) {
                return !empty($item['items']);
            });
            $list = array_filter($list, function($item) use($itemids) {
                return isset($item['itemid']) && isset($itemids[$item['itemid']]);
            });
            if (!empty($sublist)) {
                foreach ($sublist as $group) {
                    foreach ($group['items'] as $item) {
                        if (isset($itemids[$item['itemid']])) {
                            $list[$item['itemid']] = $item;
                        }
                    }
                }
                unset($sublist);
            }
            $itemids = array_column($list, 'itemid');
            $list = array_combine($itemids, $list);
            array_walk($ret['folders'], function(&$folder, $fid, $list) {
                $itemid = $folder['itemid'];
                $folder['cannotpay'] = isset($list[$itemid]['cannotpay']) ? $list[$itemid]['cannotpay'] : 0;
                if (!isset($list[$itemid])) {
                    $folder['itemid'] = 0;
                    $folder['pid'] = 0;
                    return;
                }
                $folder['price'] = $list[$itemid]['price'];
                $folder['pid'] = $list[$itemid]['pid'];
                $folder['pname'] = $list[$itemid]['pname'];
                if (isset($list[$itemid]['crid'])) {
                    $folder['sourcecrid'] = $list[$itemid]['crid'];
                    $folder['crname'] = $list[$itemid]['crname'];
                }
            }, $list);
            //按来源、开通时间排序
            $updatelines = $grouptypes = array();
            foreach ($ret['folders'] as $folder) {
                $updatelines[] = $folder['updateline'];
                if (empty($folder['itemid'])) {
                    $grouptypes[] = 2;
                } else if (isset($folder['sourcecrid'])) {
                    $grouptypes[] = 1;
                } else {
                    $grouptypes[] = 0;
                }
            }
            array_multisort($grouptypes, SORT_ASC, SORT_NUMERIC,
                $updatelines, SORT_DESC, SORT_NUMERIC, $ret['folders']);
            unset($updatelines, $grouptypes);
        }
        $ret['coursewarenum'] = count($coursewares);
        unset($coursewares);
        $folderGroup = array();
        unset($folder);
        foreach ($ret['folders'] as $folder) {
            if (!$this->otherGroupType && !empty($folder['sourcecrid'])) {
                //按来源网校分组企业选课课程
                $id = $folder['sourcecrid'];
                $key = 'c'.$folder['sourcecrid'];
                $groupName = $folder['crname'];
            } else if (!empty($folder['pid'])) {
                //按package分组企业选课课程
                $id = $folder['pid'];
                $key = 'p'.$folder['pid'];
                $groupName = $folder['pname'];
            } else {
                //数据缺失归到其他分组
                $id = 0;
                $key = 0;
                $groupName = '其他课程';
            }
            if (!isset($folderGroup[$key])) {
                $folderGroup[$key] = array(
                    'groupName' => $groupName,
                    'id' => $id,
                    'folderlist' => array()
                );
            }
            $itemid = $folder['itemid'];
            $price = 0;
            if ($itemid > 0) {
                $price = $this->isStudent && !empty($folder['isschoolfree']) ? 0 : $folder['price'];
            }
            $folderGroup[$key]['folderlist'][] = array(
                'folderid' => $folder['enddate'] > $now ? $folder['folderid'] : 0,
                'foldername' => $folder['foldername'],
                'img' => $folder['img'],
                'coursewarenum' => $folder['coursewarenum'],
                'itemid' => $itemid,
                'price' => $price,
                'showmode' => $folder['showmode'],
                'cannotpay' => $folder['cannotpay'],
                'credit' => $folder['credit']
            );
        }
        $ret['folders'] = array_values($folderGroup);
        return $ret;
    }

    /**
     * 获取网校课程服务分类列表,排序方式：本校分类分组、引用其他网校的分类分组(displayorder无效)、企业选课分类分组；
     * 分组中的排序：package['displayorder']升序，package['pid']降序
     * 接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=461
     * @return array
     */
    public function courseCategoryListAction() {
        $list = $this->getService($this->crid);
        if (empty($list)) {
            return array();
        }
        $ret = array();
        $located = false;
        $pid = intval($this->pid);
        foreach ($list as $item) {
            if ($this->pid > 0 && $item['pid'] != $pid) {
                continue;
            }
            if (!isset($ret[$item['pid']])) {
                if (!$located && !empty($item['located'])) {
                    $locate = 1;
                    $located = true;
                } else {
                    $locate = 0;
                }
                $ret[$item['pid']] = array(
                    'pid' => $item['pid'],
                    'pname' => $item['pname'],
                    'crid' => isset($item['crid']) ? $item['crid'] : 0,
                    'crname' => isset($item['crname']) ? $item['crname'] : '',
                    'located' => $locate,
                    'pcrid' => isset($item['pcrid']) && $item['pcrid'] != $this->crid ? $item['pcrid'] : 0,
                    'displayorder' => isset($item['displayorder']) ? $item['displayorder'] : 0,
                    'pdisplayorder' => $item['pdisplayorder'],
                    'sorts' => array()
                );
            }
            if (!isset($ret[$item['pid']]['sorts'][$item['sid']])) {
                $ret[$item['pid']]['sorts'][$item['sid']] = array(
                    'sid' => intval($item['sid']),
                    'sname' => empty($item['sname']) ? '其他课程' : $item['sname'],
                    'coursenum' => 0,
                    'sdisplayorder' => !empty($item['sid']) ? $item['sdisplayorder'] : 2147483647,
                    'pid' => $item['pid']
                );
            }
            $ret[$item['pid']]['sorts'][$item['sid']]['coursenum']++;
        }
        $owns = $crids = $displayorders = $pids = $pdisplayorders = array();
        foreach ($ret as $package) {
            if ($package['crid'] > 0) {
                $owns[] = 2;
            } else if ($package['pcrid'] > 0) {
                $owns[] = 1;
            } else {
                $owns[] = 0;
            }
            $crids[] = $package['crid'];
            $displayorders[] = $package['displayorder'];
            $pdisplayorders[] = $package['pdisplayorder'];
            $pids[] = $package['pid'];
        }
        array_multisort($owns, SORT_ASC, SORT_NUMERIC,
            $displayorders, SORT_ASC, SORT_NUMERIC,
            $crids, SORT_DESC, SORT_NUMERIC,
            $pdisplayorders, SORT_ASC, SORT_NUMERIC,
            $pids, SORT_DESC, SORT_NUMERIC, $ret);
        unset($owns, $crids, $displayorders, $pids, $pdisplayorders);
        array_walk($ret, function(&$package) {
            unset($package['displayorder'], $package['pcrid']);
            if (count($package['sorts']) == 1) {
                $package['sorts'] = array_values($package['sorts']);
                $package['coursenum'] = $package['sorts'][0]['coursenum'];
                unset($package['sorts'][0]['sdisplayorder']);
                return;
            }
            $sids = $sdisplayorders = array();
            $package['coursenum'] = 0;
            foreach ($package['sorts'] as $sid => $sort) {
                $package['coursenum'] += $sort['coursenum'];
                $sids[] = $sort['sid'];
                $sdisplayorders[] = $sort['sdisplayorder'];
                unset($package['sorts'][$sid]['sdisplayorder']);
            }
            array_multisort($sdisplayorders, SORT_ASC, SORT_NUMERIC,
                $sids, SORT_DESC, SORT_NUMERIC, $package['sorts']);
        });
        return $ret;
    }

    /**
     * 获取分类下的课程服务：课程包、本校课程、企业选课课程，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=462
     * @return array
     */
    public function serviceListBySortAction() {
        $list = $this->getService($this->crid);
        if (empty($list)) {
            return array();
        }
        $pid = $this->pid;
        $sid = $this->sid;
        $list = array_filter($list, function($item) use($pid, $sid) {
            return $item['pid'] == $pid && $item['sid'] ==  $sid;
        });
        if (empty($list)) {
            return array();
        }
        $this->bundleReportCounts = $this->model->reportCount($this->crid, array(), $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        $this->itemReportCounts = $this->model->reportCount($this->crid, array(), $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        //排序结果：课程包第一组，打包课程第二组，零售课程第三组
        //组内排序：排序号升序、ID降序
        $rankTypes = $ranks = $ids = array();
        foreach ($list as &$item) {
            $this->tagged($item, self::ORDER_SRANK_ASC);
            if (isset($item['bid'])) {
                //课程包
                $rankTypes[] = 0;
                $ranks[] = $item['bdisplayorder'];
                $ids[] = $item['bid'];
            } else if (!empty($item['showbysort'])) {
                //打包课程
                $rankTypes[] = 1;
                $ranks[] = $item['sdisplayorder'];
                $ids[] = $item['sid'];
            } else {
                //零售课程
                $viewnum = $this->redis->hget('folderviewnum', $item['folderid'], false);
                if (!empty($viewnum)) {
                    $item['viewnum'] = $viewnum;
                }
                $rankTypes[] = 2;
                $ranks[] = $item['srank'];
                $ids[] = $item['itemid'];
                if (isset($this->userpermissions[$item['folderid']])) {
                    $item['haspower'] = 1;
                }
                if ($this->isStudent && !empty($item['isschoolfree'])) {
                    $item['price'] = 0;
                }
                if (empty($item['islimit']) && isset($this->itemReportCounts[$item['itemid']]) && $this->itemReportCounts[$item['itemid']]['c'] > $item['limitnum']) {
                    //本校课程限制报名并且报名人数达到限制数，课程禁止报名
                    $item['cannotpay'] = 1;
                }
            }
            unset($item['bdisplayorder'], $item['sdisplayorder'], $item['pdisplayorder'], $item['displayorder'], $item['pcrid'], $item['fcrid'], $item['grank'], $item['prank'], $item['srank']);
            if ($this->isinterior && empty($item['crid']) && $this->uid > 0 && !$this->isStudent) {
                $item['cannotpay'] = 1;
            }
        }
        array_multisort($rankTypes, SORT_ASC, SORT_NUMERIC,
            $ranks, SORT_ASC, SORT_NUMERIC,
            $ids, SORT_DESC, SORT_NUMERIC, $list);
        return array_values($list);
    }

    /**
     * 自选课程列表,只取零售课程，列表按package分组，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=463
     * @return array
     */
    public function manualServiceListAction() {
        $list = $this->model->getManualCourseList($this->crid);
        if (empty($list)) {
            return array();
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        $limitItems = array_filter($list, function($item) {
            return !empty($item['islimit']);
        });
        if (!empty($limitItems)) {
            $itemids = array_column($limitItems, 'itemid');
            $this->itemReportCounts = $this->model->reportCount($this->crid, $itemids, $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        }
        array_walk($list, function(&$item) {
            $viewnum = $this->redis->hget('folderviewnum', $item['folderid'], false);
            if (!empty($viewnum)) {
                $item['viewnum'] = $viewnum;
            }
            if ($this->isStudent && !empty($item['isschoolfree'])) {
                //本校免费课程价格置0
                $item['price'] = 0;
            }
            if (!empty($this->userpermissions[$item['folderid']])) {
                $item['haspower'] = 1;
            }
            if (!empty($item['islimit']) && isset($this->itemReportCounts[$item['itemid']]) && $this->itemReportCounts[$item['itemid']]['c'] >= $item['limitnum']) {
                $item['cannotpay'] = 1;
            }
            //网校内部课程，只对本校学生开放报名
            if ($this->isinterior && $this->uid > 0 && !$this->isStudent && empty($item['own'])) {
                $item['cannotpay'] = 1;
            }
        });
        $packages = array();
        foreach ($list as $item) {
            $pid = $item['pid'];
            if (!isset($packages[$pid])) {
                $packages[$pid] = array(
                    'pid' => $pid,
                    'pname' => $item['pname'],
                    'items' => array()
                );
            }
            unset($item['pname']);
            $packages[$pid]['items'][] = $item;
        }
        return array_values($packages);
    }

    /**
     * 单课列表，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=465
     * @return array
     */
    public function fineListAction() {
        $cacheKey = 'plate-fineware-'.$this->crid;
        $list = $this->redis->get($cacheKey);
        $folderid = max(0, $this->folderid);
        if (empty($list)) {
            $list = $this->model->getFineList($this->crid);
            $folders = array();
            $ranks = $folderids = array();
            foreach ($list as &$courseware) {
                if (!isset($folders[$courseware['folderid']])) {
                    $folders[$courseware['folderid']] = array(
                        'folderid' => $courseware['folderid'],
                        'foldername' => $courseware['foldername']
                    );
                    $ranks[] = $courseware['grank'] === null ? 2147483647 : $courseware['grank'];
                    $folderids[] = $courseware['folderid'];
                }
                if (empty($courseware['truedateline'])) {
                    $courseware['truedateline'] = !empty($courseware['submitat']) ? $courseware['submitat'] : $courseware['dateline'];
                }
                $courseware['user'] = array(
                    'uid' => $courseware['uid'],
                    'username' => $courseware['username'],
                    'realname' => $courseware['realname'],
                    'groupid' => $courseware['groupid'],
                    'face' => $courseware['face'],
                    'sex' => $courseware['sex']
                );
                unset($courseware['submitat'], $courseware['uid'], $courseware['username'], $courseware['realname'], $courseware['groupid'], $courseware['face'], $courseware['sex'], $courseware['grank']);
            }
            array_multisort($ranks, SORT_ASC, SORT_NUMERIC,
                $folderids, SORT_DESC, SORT_NUMERIC, $folders);
            unset($folderids, $ranks);
            $list = array(
                'folderlist' => $folders,
                'coursewarelist' => $list
            );
            unset($folders);
            $this->redis->set($cacheKey, $list);
        }
        if (empty($list)) {
            return array(
                'count' => 0
            );
        }
        $isfree = $this->isfree;
        if ($folderid > 0 || $isfree) {
            $list['coursewarelist'] = array_filter($list['coursewarelist'], function($courseware) use($folderid, $isfree) {
                if ($folderid > 0 && $courseware['folderid'] != $folderid) {
                    return false;
                }
                if (!$isfree) {
                    return true;
                }
                return $courseware['cprice'] == 0;
            });
            $list['coursewarelist'] = array_values($list['coursewarelist']);
            if ($folderid > 0) {
                foreach ($list['folderlist'] as $index => $folder) {
                    if ($folder['folderid'] == $folderid) {
                        $list['folderlist'][$index]['cur'] = true;
                        $list['folderid'] = $folderid;
                        break;
                    }
                }
            }
        }
        $list['count'] = count($list['coursewarelist']);
        //学生开通权限
        $userpermissions = array();
        $coursewareUserpermissions = array();
        if ($this->uid > 0) {
            //读取用户开通权限
            $userpermissionModel = new UserpermisionsModel();
            $userpermissions = $this->userpermissions;
            $now = SYSTIME - 86400;
            $userpermissions = array_filter($userpermissions, function($userpermission) use($now) {
                return $userpermission['enddate'] > $now;
            });
            $coursewareUserpermissions = $userpermissionModel->getService($this->uid, 0, 1);
            $coursewareUserpermissions = array_filter($coursewareUserpermissions, function($userpermission) use($now) {
                return $userpermission['enddate'] > $now;
            });
        }
        /**
         * 排序方式：0-时间[ID]降序，1-热度[浏览数]降序，2-价格降序，3-价格升序
         */
        $orderType = $this->ordertype;
        $sort = in_array($orderType, array(1, 2)) ? SORT_DESC : SORT_ASC;
        $ranks = $ids = array();
        $disabled = $this->isinterior && $this->uid > 0 && !$this->isStudent;
        foreach ($list['coursewarelist'] as &$courseware) {
            $viewnum = $this->redis->hget('coursewareviewnum', $courseware['cwid'], false);
            if (!empty($viewnum)) {
                $courseware['viewnum'] = $viewnum;
            }
            if ($orderType == 1) {
                $ranks[] = $courseware['viewnum'];
            } else if ($orderType == 2 || $orderType == 3) {
                $ranks[] = $courseware['cprice'];
            } else {
                $ranks[] = $courseware['cdisplayorder'];
            }
            $ids[] = $courseware['cwid'];
            unset($courseware['cdisplayorder']);
            if ($disabled) {
                $courseware['cannotpay'] = 1;
            }
            if (isset($userpermissions[$courseware['folderid']])) {
                $courseware['haspower'] = 1;
                continue;
            }
            if (isset($coursewareUserpermissions[$courseware['cwid']])) {
                $courseware['haspower'] = 1;
            }
        }
        array_multisort($ranks, $sort, SORT_NUMERIC,
            $ids, SORT_DESC, SORT_NUMERIC, $list['coursewarelist']);
        unset($ranks, $ids);
        $page = max(1, $this->page);
        $pagesize = max(0, $this->pagesize);
        if ($pagesize > 0) {
            $offset = ($page - 1) * $pagesize;
            $list['coursewarelist'] = array_slice($list['coursewarelist'], $offset, $pagesize);
        }
        return $list;
    }

    /**
     * 课程包详情，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=467
     */
    public function bundleAction() {
        $bundle = $this->model->getBundleDetail($this->crid, $this->bid);
        if (empty($bundle)) {
            return false;
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        $bundle['items'] = $this->model->getBundleItemList($this->bid);
        if (empty($bundle['items'])) {
            return false;
        }
        if (!empty($bundle['islimit'])) {
            $this->bundleReportCounts = $this->model->reportCount($this->crid, $bundle['bid'], $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        }
        $this->tagged($bundle, 0, true);
        if ($this->isinterior && $this->uid > 0 && !$this->isStudent) {
            $bundle['cannotpay'] = 1;
        }
        return $bundle;
    }

    /**
     * 打包课程
     */
    public function sortAction() {
        $sort = $this->model->getSortDetail($this->sid);
        if (empty($sort)) {
            return false;
        }
        $sort['items'] = $this->model->getSchoolCourseList($this->crid, array('pid' => $sort['pid'], 'sid' => $sort['sid']));
        if (empty($sort['items'])) {
            return false;
        }
        $limitItems = array_filter($sort['items'], function($item) {
            return !empty($item['islimit']);
        });
        if (!empty($sort['imgurl'])) {
            $sort['cover'] = $sort['imgurl'];
        }
        unset($sort['imgurl']);
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        $itemids = array_column($limitItems, 'itemid');
        $this->itemReportCounts = $this->model->reportCount($this->crid, $itemids, $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        $this->tagged($sort, self::ORDER_SRANK_ASC,  true);
        if ($this->isinterior && $this->uid > 0 && !$this->isStudent) {
            $sort['cannotpay'] = 1;
        }
        return $sort;
    }

    /**
     * 课程详情
     */
    public function courseInfoAction() {
        if ($this->itemid < 1) {
            return false;
        }
        $courseinfo = $this->model->getCourseDetail($this->itemid, $this->crid);
        if (empty($courseinfo)) {
            return false;
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        if (isset($this->userpermissions[$courseinfo['folderid']])) {
            $courseinfo['haspower'] = 1;
        }
        if (empty($courseinfo['isschoolfree'])) {
            $courseinfo['price'] = 0;
        }
        if ($this->isinterior && $this->uid > 0 && !$this->isStudent) {
            //非网校用户禁止购买课程
            $courseinfo['cannotpay'] = 1;
        }
        return $courseinfo;
    }

    /**
     * 教师所教课程
     * @return array
     */
    public function getTeacherCourseListAction() {
        $count = $this->model->getCourseForTeacherCount($this->tid, $this->crid);
        if ($count == 0) {
            return array('count' => 0);
        }
        $limit = null;
        $page = max(1, $this->page);
        if ($this->pagesize > 0) {
            $limit['pagesize'] = $this->pagesize;
            $limit['page'] = $page;
        }
        $courses = $this->model->getCourseForTeacher($this->tid, $this->crid, $limit);
        if (empty($courses)) {
            return array();
        }
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        //本校内部服务，只对本校学生开放报名
        $disabled = $this->isinterior && $this->uid > 0 && !$this->isStudent ? 1 : 0;
        if ($this->isStudent || !$disabled || !empty($this->userpermissions)) {
            array_walk($courses, function(&$course, $index, $disabled) {
                $course['cannotpay'] = $disabled;
                if ($this->isStudent && !empty($course['isschoolfree'])) {
                    $course['iprice'] = 0;
                }
                if (isset($this->userpermissions[$course['folderid']])) {
                    $course['haspower'] = 1;
                }
            }, $disabled);
        }
        return array(
            'count' => $count,
            'list' => $courses
        );
    }

    /**
     * 全校课程服务列表
     * @param int $crid 网校ID
     * @param bool $useCache 使用缓存
     * @return array
     */
    private function getService($crid, $useCache = true) {
        //return json_decode('[{"bid":"1","name":"\u6691\u5047\u5305","summary":"\u6298\u78e8\u5b66\u751f\u7684\u8bfe\u7a0b","cover":"","pid":"372","sid":"0","speaker":"\u4e3b\u8bb2\u4e3b\u4efb","price":"321.32","bdisplayorder":"1503627649","display":"1","cannotpay":"0","limitnum":"1","islimit":"1","pname":"\u591c\u6708","pdisplayorder":"27","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"1","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"1","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"},{"bid":"1","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"1","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"}]},{"bid":"3","name":"\u514d\u8d39\u5305","summary":"\u6298\u78e8\u5b66\u751f\u7684\u8bfe\u7a0b","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/07\/24\/15008662298100_243_144.jpg","pid":"372","sid":"0","speaker":"\u514d\u8d39\u5305\u4e3b\u8bb2","price":"0.00","bdisplayorder":"1503627438","display":"1","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u591c\u6708","pdisplayorder":"27","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"3","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"},{"bid":"3","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"3","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"3","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"}]},{"bid":"5","name":"\u5c01\u59bb\u836b\u5b50","summary":"\u6298\u78e8\u5b66\u751f\u7684\u8bfe\u7a0b","cover":"","pid":"372","sid":"0","speaker":"\u5c01\u59bb","price":"0.00","bdisplayorder":"1503627437","display":"1","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u591c\u6708","pdisplayorder":"27","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"5","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"},{"bid":"5","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"5","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"5","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"}]},{"bid":"6","name":"\u5947\u5de7","summary":"\u6298\u78e8\u5b66\u751f\u7684\u8bfe\u7a0b","cover":"","pid":"372","sid":"0","speaker":"\u5947\u5de7","price":"220.00","bdisplayorder":"1503627647","display":"1","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u591c\u6708","pdisplayorder":"27","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"6","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"6","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"6","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"6","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"}]},{"bid":"7","name":"\u9876\u6234","summary":"\u6298\u78e8\u5b66\u751f\u7684\u8bfe\u7a0b","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/07\/24\/15008662298100_243_144.jpg","pid":"372","sid":"0","speaker":"\u9876\u6234","price":"0.01","bdisplayorder":"1503627429","display":"1","cannotpay":"1","limitnum":"0","islimit":"0","pname":"\u591c\u6708","pdisplayorder":"27","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"7","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"7","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"7","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"}]},{"bid":"8","name":"\u897f\u57df","summary":"\u897f\u57df\u897f\u57df\u897f\u57df\u897f\u57df\u897f\u57df","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/07\/24\/15008662298100_243_144.jpg","pid":"380","sid":"0","speaker":"\u897f\u57df","price":"20.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u9876\u6234","pdisplayorder":"12","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"8","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"},{"bid":"8","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"8","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"8","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"}]},{"bid":"9","name":"\u7530\u7537","summary":"\u7530\u7537\u7530\u7537\u7530\u7537\u7530\u7537","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/07\/24\/15008662298100_243_144.jpg","pid":"380","sid":"0","speaker":"\u7530\u7537","price":"13.00","bdisplayorder":"1503652417","display":"1","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u9876\u6234","pdisplayorder":"12","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"9","dateline":"0","itemid":"1077","iname":"xx","price":"2.00","summary":"\u662f\u53cd\u5012\u662f\u53cd\u5012\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u751f\u7684\u7684\u8bf4\u6cd5\u90fd\u662f\u5b9e\u5f97\u5206\u662f\u5426\u662f\u7684\u7b2c\u4e09\u65b9\u7684\u8bf4\u6cd5\u5b9e\u5f97\u5206\u5b9e\u5f97\u5206\u5b9e\u5f97\u5206","cannotpay":"0","imonth":"33","iday":"0","folderid":"4807","isschoolfree":"1","coursewarenum":"2","viewnum":"50","foldername":"xx","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1095","pname":"cesguga","grank":"161","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"1075","iname":"\u8bfa\u4e00\u6536","price":"0.00","summary":"1","cannotpay":"0","imonth":"55","iday":"0","folderid":"4819","isschoolfree":"1","coursewarenum":"3","viewnum":"0","foldername":"\u8bfa\u4e00\u6536","showmode":"0","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/26\/14616370583464.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1093","pname":"\u624b\u5de5\u8bfe\u670d\u52a1\u5305","grank":"163","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"1073","iname":"\u54c7\u54c8\u54c8","price":"2.00","summary":"123","cannotpay":"0","imonth":"44","iday":"0","folderid":"4817","isschoolfree":"1","coursewarenum":"8","viewnum":"400","foldername":"\u54c7\u54c8\u54c8","showmode":"0","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/15\/14606994837347.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1091","pname":"\u4f53\u80b2\u8bfe\u670d\u52a1\u5305","grank":"165","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"1063","iname":"\u52b3\u6280","price":"1000.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"3","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"183","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"966","iname":"\u4e09(\u4e0b)","price":"0.00","summary":"\u4e09(\u4e0b)\u4e09(\u4e0b)\u4e09(\u4e0b)\u4e09(\u4e0b)","cannotpay":"0","imonth":"5","iday":"0","folderid":"4595","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u4e09(\u4e0b)","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","speaker":"bug","pid":"1016","pname":"bug","grank":"175","prank":"5","srank":"5"},{"bid":"9","dateline":"0","itemid":"823","iname":"\u79d1\u5b66","price":"10.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"1","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"186","prank":"2","srank":"2"},{"bid":"9","dateline":"0","itemid":"822","iname":"\u7f8e\u5de5\u6346\u7ed1","price":"111.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","cannotpay":"0","imonth":"1","iday":"0","folderid":"3950","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"987","pname":"\u6346\u7ed1\u9500\u552e","grank":"184","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"},{"bid":"9","dateline":"0","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"}]},{"bid":"10","name":"121","summary":"12121","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","pid":"1140","sid":"1575","speaker":"1212","price":"220.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","located":"0","sname":"aaaaaaaaaaaaaaaaaaa","sdisplayorder":"0","items":[{"bid":"10","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"}]},{"bid":"11","name":"1121212","summary":"12131231","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","pid":"987","sid":"1530","speaker":"12312","price":"2210.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":"\u5305\u8981\u8981","sdisplayorder":"0","items":[{"bid":"11","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"},{"bid":"11","dateline":"0","itemid":"1116","iname":"\u53c8\u4e00\u6b21","price":"2.00","summary":"1111111111111","cannotpay":"0","imonth":"0","iday":"1","folderid":"4862","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u53c8\u4e00\u6b21","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1126","pname":"\u4e00\u6b21\u670d\u52a1\u5305","grank":"122","prank":"1","srank":"1"},{"bid":"11","dateline":"0","itemid":"1111","iname":"\u8bfe\u7a0b5","price":"5.00","summary":"\u8bfe\u7a0b5","cannotpay":"0","imonth":"0","iday":"1","folderid":"4856","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u8bfe\u7a0b5","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1123","pname":"duo","grank":"127","prank":"2","srank":"2"},{"bid":"11","dateline":"0","itemid":"1110","iname":"\u8bfe\u7a0b4","price":"4.00","summary":"\u8bfe\u7a0b4","cannotpay":"0","imonth":"0","iday":"1","folderid":"4855","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u8bfe\u7a0b4","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1123","pname":"duo","grank":"128","prank":"3","srank":"3"}]},{"bid":"12","name":"21332","summary":"2222","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","pid":"987","sid":"0","speaker":"222","price":"0.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"12","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"},{"bid":"12","dateline":"0","itemid":"1116","iname":"\u53c8\u4e00\u6b21","price":"2.00","summary":"1111111111111","cannotpay":"0","imonth":"0","iday":"1","folderid":"4862","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u53c8\u4e00\u6b21","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1126","pname":"\u4e00\u6b21\u670d\u52a1\u5305","grank":"122","prank":"1","srank":"1"}]},{"bid":"13","name":"qweqe","summary":"qwqeewqeqeq","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/08\/28\/1503899141783_243_144.jpg","pid":"987","sid":"1530","speaker":"qweqw","price":"1231.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":"\u5305\u8981\u8981","sdisplayorder":"0","items":[{"bid":"13","dateline":"0","itemid":"1121","iname":"\u70ed","price":"1.00","summary":"\u70ed","cannotpay":"0","imonth":"0","iday":"1","folderid":"4866","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1122","pname":"\u51b7\u5305","grank":"119","prank":"1","srank":"1"},{"bid":"13","dateline":"0","itemid":"1136","iname":"wuli\u8c26123","price":"5.00","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","cannotpay":"0","imonth":"0","iday":"2","folderid":"4877","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"wuli\u8c26123","showmode":"2","cover":"","speaker":"wuli\u8c26","pid":"1139","pname":"\u547c\u547chh","grank":"104","prank":"1","srank":"1"},{"bid":"13","dateline":"0","itemid":"1140","iname":"\u7f57\u8499","price":"0.01","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","cannotpay":"0","imonth":"0","iday":"2","folderid":"4881","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u7f57\u8499","showmode":"2","cover":"","speaker":"\u7f57","pid":"1144","pname":"\u8863\u670d","grank":"100","prank":"3","srank":"1"},{"bid":"13","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"}]},{"bid":"14","name":"\u6d4b\u8bd5\u7f16\u8f91","summary":"qwqeewqeqeqwqeqeqe","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/08\/28\/1503899141783_243_144.jpg","pid":"987","sid":"0","speaker":"qweqw","price":"1231.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"14","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"},{"bid":"14","dateline":"0","itemid":"1140","iname":"\u7f57\u8499","price":"0.01","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","cannotpay":"0","imonth":"0","iday":"2","folderid":"4881","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u7f57\u8499","showmode":"2","cover":"","speaker":"\u7f57","pid":"1144","pname":"\u8863\u670d","grank":"100","prank":"3","srank":"1"},{"bid":"14","dateline":"0","itemid":"1136","iname":"wuli\u8c26123","price":"5.00","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","cannotpay":"0","imonth":"0","iday":"2","folderid":"4877","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"wuli\u8c26123","showmode":"2","cover":"","speaker":"wuli\u8c26","pid":"1139","pname":"\u547c\u547chh","grank":"104","prank":"1","srank":"1"},{"bid":"14","dateline":"0","itemid":"1121","iname":"\u70ed","price":"1.00","summary":"\u70ed","cannotpay":"0","imonth":"0","iday":"1","folderid":"4866","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1122","pname":"\u51b7\u5305","grank":"119","prank":"1","srank":"1"}]},{"bid":"16","name":"qwe","summary":"qwqeewqeqeq","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/08\/28\/1503899141783_243_144.jpg","pid":"987","sid":"0","speaker":"qweqw","price":"2.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"16","dateline":"1516332988","itemid":"1140","iname":"\u7f57\u8499","price":"0.01","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","cannotpay":"0","imonth":"0","iday":"2","folderid":"4881","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u7f57\u8499","showmode":"2","cover":"","speaker":"\u7f57","pid":"1144","pname":"\u8863\u670d","grank":"100","prank":"3","srank":"1"},{"bid":"16","dateline":"1516332989","itemid":"1136","iname":"wuli\u8c26123","price":"5.00","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","cannotpay":"0","imonth":"0","iday":"2","folderid":"4877","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"wuli\u8c26123","showmode":"2","cover":"","speaker":"wuli\u8c26","pid":"1139","pname":"\u547c\u547chh","grank":"104","prank":"1","srank":"1"},{"bid":"16","dateline":"1516332990","itemid":"1121","iname":"\u70ed","price":"1.00","summary":"\u70ed","cannotpay":"0","imonth":"0","iday":"1","folderid":"4866","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1122","pname":"\u51b7\u5305","grank":"119","prank":"1","srank":"1"},{"bid":"16","dateline":"1516332991","itemid":"26488","iname":"\u5316\u5b66","price":"1.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3957","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u5316\u5b66","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/2.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"55","prank":"55","srank":"55"},{"bid":"16","dateline":"1516332992","itemid":"384","iname":"\u5730\u7406","price":"0.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21","cannotpay":"1","imonth":"22","iday":"0","folderid":"3958","isschoolfree":"0","coursewarenum":"4","viewnum":"0","foldername":"\u5730\u7406","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/19.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"380","pname":"\u9876\u6234","grank":"185","prank":"1","srank":"1"},{"bid":"16","dateline":"1516332993","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"16","dateline":"1516332994","itemid":"26489","iname":"\u751f\u7269","price":"24.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3961","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u751f\u7269","showmode":"0","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/12\/23\/14824589913046_th.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"54","prank":"54","srank":"54"}]},{"bid":"17","name":"qweww","summary":"qwqeewqeqeq","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/08\/28\/1503899141783_243_144.jpg","pid":"987","sid":"0","speaker":"qweqw","price":"1231.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"17","dateline":"0","itemid":"1142","iname":"\u841d\u535c\u72792","price":"1.00","summary":"1111111111111111111111","cannotpay":"1","imonth":"0","iday":"1","folderid":"4883","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u841d\u535c\u72792","showmode":"2","cover":"","speaker":"123","pid":"1144","pname":"\u8863\u670d","grank":"99","prank":"2","srank":"1"},{"bid":"17","dateline":"0","itemid":"1140","iname":"\u7f57\u8499","price":"0.01","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","cannotpay":"0","imonth":"0","iday":"2","folderid":"4881","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u7f57\u8499","showmode":"2","cover":"","speaker":"\u7f57","pid":"1144","pname":"\u8863\u670d","grank":"100","prank":"3","srank":"1"},{"bid":"17","dateline":"0","itemid":"1136","iname":"wuli\u8c26123","price":"5.00","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","cannotpay":"0","imonth":"0","iday":"2","folderid":"4877","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"wuli\u8c26123","showmode":"2","cover":"","speaker":"wuli\u8c26","pid":"1139","pname":"\u547c\u547chh","grank":"104","prank":"1","srank":"1"},{"bid":"17","dateline":"0","itemid":"1121","iname":"\u70ed","price":"1.00","summary":"\u70ed","cannotpay":"0","imonth":"0","iday":"1","folderid":"4866","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1122","pname":"\u51b7\u5305","grank":"119","prank":"1","srank":"1"}]},{"bid":"18","name":"xxxxx","summary":"qwqeewqeqeq","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/08\/28\/1503899141783_243_144.jpg","pid":"987","sid":"1429","speaker":"qweqw","price":"0.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","located":"0","sname":"\u6346\u7ed1","sdisplayorder":"0","items":[{"bid":"18","dateline":"0","itemid":"1140","iname":"\u7f57\u8499","price":"0.01","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","cannotpay":"0","imonth":"0","iday":"2","folderid":"4881","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u7f57\u8499","showmode":"2","cover":"","speaker":"\u7f57","pid":"1144","pname":"\u8863\u670d","grank":"100","prank":"3","srank":"1"},{"bid":"18","dateline":"0","itemid":"1136","iname":"wuli\u8c26123","price":"5.00","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","cannotpay":"0","imonth":"0","iday":"2","folderid":"4877","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"wuli\u8c26123","showmode":"2","cover":"","speaker":"wuli\u8c26","pid":"1139","pname":"\u547c\u547chh","grank":"104","prank":"1","srank":"1"},{"bid":"18","dateline":"0","itemid":"1121","iname":"\u70ed","price":"1.00","summary":"\u70ed","cannotpay":"0","imonth":"0","iday":"1","folderid":"4866","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","showmode":"0","cover":"","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"1122","pname":"\u51b7\u5305","grank":"119","prank":"1","srank":"1"}]},{"bid":"31","name":"dsfasd","summary":"fadsfadsf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","pid":"973","sid":"0","speaker":"dsfasdfadsf","price":"22.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u8d77\u4f0f","pdisplayorder":"7","located":"0","sname":null,"sdisplayorder":null,"items":[{"bid":"31","dateline":"1516332791","itemid":"26542","iname":"\u79df","price":"0.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"4886","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u79df","showmode":"0","cover":"","speaker":"","pid":"372","pname":"\u591c\u6708","grank":"1","prank":"1","srank":"1"},{"bid":"31","dateline":"1516332791","itemid":"26488","iname":"\u5316\u5b66","price":"1.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3957","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u5316\u5b66","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/2.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"55","prank":"55","srank":"55"},{"bid":"31","dateline":"1516332792","itemid":"384","iname":"\u5730\u7406","price":"0.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21","cannotpay":"1","imonth":"22","iday":"0","folderid":"3958","isschoolfree":"0","coursewarenum":"4","viewnum":"0","foldername":"\u5730\u7406","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/19.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"380","pname":"\u9876\u6234","grank":"185","prank":"1","srank":"1"},{"bid":"31","dateline":"1516332793","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"31","dateline":"1516332794","itemid":"26489","iname":"\u751f\u7269","price":"24.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3961","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u751f\u7269","showmode":"0","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/12\/23\/14824589913046_th.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"54","prank":"54","srank":"54"}]},{"bid":"32","name":"xxx","summary":"xxxxxxxxxxxxxxxxx","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","pid":"1126","sid":"1555","speaker":"dsfasfas","price":"0.00","bdisplayorder":"0","display":"0","cannotpay":"0","limitnum":"0","islimit":"0","pname":"\u4e00\u6b21\u670d\u52a1\u5305","pdisplayorder":"1","located":"0","sname":"\u4e00\u6b21\u5206\u7c7b","sdisplayorder":"0","items":[{"bid":"32","dateline":"1516333113","itemid":"959","iname":"\u516d(\u4e0a)","price":"1000.00","summary":"\u516d(\u4e0a)","cannotpay":"0","imonth":"5","iday":"0","folderid":"4588","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u516d(\u4e0a)","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","speaker":"bug","pid":"1016","pname":"bug","grank":"182","prank":"12","srank":"12"},{"bid":"32","dateline":"1516333114","itemid":"961","iname":"\u4e94(\u4e0a)","price":"0.00","summary":"\u4e94(\u4e0a)","cannotpay":"0","imonth":"5","iday":"0","folderid":"4590","isschoolfree":"0","coursewarenum":"0","viewnum":"0","foldername":"\u4e94(\u4e0a)","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","speaker":"bug","pid":"1016","pname":"bug","grank":"180","prank":"10","srank":"10"},{"bid":"32","dateline":"1516333115","itemid":"26489","iname":"\u751f\u7269","price":"24.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3961","isschoolfree":"0","coursewarenum":"2","viewnum":"0","foldername":"\u751f\u7269","showmode":"0","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/12\/23\/14824589913046_th.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"54","prank":"54","srank":"54"},{"bid":"32","dateline":"1516333116","itemid":"26488","iname":"\u5316\u5b66","price":"1.00","summary":"","cannotpay":"0","imonth":"12","iday":"0","folderid":"3957","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u5316\u5b66","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/2.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"372","pname":"\u591c\u6708","grank":"55","prank":"55","srank":"55"},{"bid":"32","dateline":"1516333117","itemid":"384","iname":"\u5730\u7406","price":"0.00","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21","cannotpay":"1","imonth":"22","iday":"0","folderid":"3958","isschoolfree":"0","coursewarenum":"4","viewnum":"0","foldername":"\u5730\u7406","showmode":"0","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/19.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"380","pname":"\u9876\u6234","grank":"185","prank":"1","srank":"1"},{"bid":"32","dateline":"1516333118","itemid":"378","iname":"\u79d1\u5b66","price":"48.00","summary":"\u79d1\u5b66\u79d1\u5b66","cannotpay":"0","imonth":"99","iday":"0","folderid":"3959","isschoolfree":"0","coursewarenum":"10","viewnum":"0","foldername":"\u79d1\u5b66","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"379","pname":"\u5947\u5de7","grank":"186","prank":"2","srank":"2"},{"bid":"32","dateline":"1516333119","itemid":"792","iname":"\u52b3\u6280","price":"0.00","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","cannotpay":"0","imonth":"1","iday":"0","folderid":"4434","isschoolfree":"0","coursewarenum":"1","viewnum":"0","foldername":"\u52b3\u6280","showmode":"2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","speaker":"\u4e3b\u8bb2\u8001\u5e08","pid":"973","pname":"\u8d77\u4f0f","grank":"183","prank":"1","srank":"1"}]},{"sid":"1530","pid":"987","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/07\/07\/14994202732697.png","summary":"&nbsp;ff","sdisplayorder":"0","sname":"\u5305\u8981\u8981","pname":"\u6346\u7ed1\u9500\u552e","located":"0","pdisplayorder":"2","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"987","sid":"1530","itemid":"1063","price":"1000.00","cannotpay":"0","folderid":"4434","iname":"\u52b3\u6280","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","view_mode":"0","islimit":"0","limitnum":"0","imonth":"3","iday":"0","foldername":"\u52b3\u6280","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":"\u5305\u8981\u8981","showbysort":"1","sdisplayorder":"0","grank":"183","prank":"1","srank":"1"},{"pid":"987","sid":"1530","itemid":"1064","price":"333.00","cannotpay":"0","folderid":"3959","iname":"\u79d1\u5b66","summary":"\u79d1\u5b66\u79d1\u5b66","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u79d1\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","viewnum":"0","coursewarenum":"10","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":"\u5305\u8981\u8981","showbysort":"1","sdisplayorder":"0","grank":"186","prank":"2","srank":"2"}]},{"sid":"1558","pid":"1122","cover":"","summary":"","sdisplayorder":"0","sname":"lll","pname":"\u51b7\u5305","located":"0","pdisplayorder":"36","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1122","sid":"1558","itemid":"1107","price":"2.00","cannotpay":"0","folderid":"4850","iname":"\u51bb\u5f97\u54c6\u55e6","summary":"\u51bb\u5f97\u54c6\u55e6\u51bb\u5f97\u54c6\u55e6\u51bb\u5f97\u54c6\u55e6\u51bb\u5f97\u54c6\u55e6\u51bb\u5f97\u54c6\u55e6","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u51bb\u5f97\u54c6\u55e6","cover":"","viewnum":"0","coursewarenum":"4","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u51b7\u5305","pdisplayorder":"36","pcrid":"10622","located":"0","sname":"lll","showbysort":"1","sdisplayorder":"0","grank":"131","prank":"2","srank":"2"},{"pid":"1122","sid":"1558","itemid":"1121","price":"1.00","cannotpay":"0","folderid":"4866","iname":"\u70ed","summary":"\u70ed","view_mode":"0","islimit":"1","limitnum":"1","imonth":"0","iday":"1","foldername":"\u70edsdfdsfsfsfdsfdsfdsf","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u51b7\u5305","pdisplayorder":"36","pcrid":"10622","located":"0","sname":"lll","showbysort":"1","sdisplayorder":"0","grank":"119","prank":"1","srank":"1"}]},{"sid":"1602","pid":"1123","cover":"","summary":"","sdisplayorder":"0","sname":"duoduodefenlei","pname":"duo","located":"0","pdisplayorder":"35","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1123","sid":"1602","itemid":"1110","price":"4.00","cannotpay":"0","folderid":"4855","iname":"\u8bfe\u7a0b4","summary":"\u8bfe\u7a0b4","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0b4","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"duo","pdisplayorder":"35","pcrid":"10622","located":"0","sname":"duoduodefenlei","showbysort":"1","sdisplayorder":"0","grank":"128","prank":"3","srank":"3"},{"pid":"1123","sid":"1602","itemid":"1111","price":"5.00","cannotpay":"0","folderid":"4856","iname":"\u8bfe\u7a0b5","summary":"\u8bfe\u7a0b5","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0b5","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"duo","pdisplayorder":"35","pcrid":"10622","located":"0","sname":"duoduodefenlei","showbysort":"1","sdisplayorder":"0","grank":"127","prank":"2","srank":"2"},{"pid":"1123","sid":"1602","itemid":"1112","price":"1.00","cannotpay":"0","folderid":"4852","iname":"\u8bfe\u7a0bx","summary":"12323","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0bx","cover":"","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"duo","pdisplayorder":"35","pcrid":"10622","located":"0","sname":"duoduodefenlei","showbysort":"1","sdisplayorder":"0","grank":"126","prank":"1","srank":"1"}]},{"sid":"1555","pid":"1126","cover":"","summary":"","sdisplayorder":"0","sname":"\u4e00\u6b21\u5206\u7c7b","pname":"\u4e00\u6b21\u670d\u52a1\u5305","located":"0","pdisplayorder":"1","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1126","sid":"1555","itemid":"1116","price":"2.00","cannotpay":"0","folderid":"4862","iname":"\u53c8\u4e00\u6b21","summary":"1111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u53c8\u4e00\u6b21","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u4e00\u6b21\u670d\u52a1\u5305","pdisplayorder":"1","pcrid":"10622","located":"0","sname":"\u4e00\u6b21\u5206\u7c7b","showbysort":"1","sdisplayorder":"0","grank":"122","prank":"1","srank":"1"}]},{"sid":"1557","pid":"1128","cover":"","summary":"","sdisplayorder":"0","sname":"\u8bad\u7c7b","pname":"\u8bad1","located":"0","pdisplayorder":"30","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1128","sid":"1557","itemid":"1117","price":"1.00","cannotpay":"0","folderid":"4859","iname":"\u57f9\u8bad","summary":"1111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u57f9\u8bad","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad1","pdisplayorder":"30","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"1","sdisplayorder":"0","grank":"124","prank":"4","srank":"3"},{"pid":"1128","sid":"1557","itemid":"1118","price":"2.00","cannotpay":"0","folderid":"4860","iname":"\u518d\u57f9\u8bad","summary":"111111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u518d\u57f9\u8bad","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad1","pdisplayorder":"30","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"1","sdisplayorder":"0","grank":"123","prank":"3","srank":"2"},{"pid":"1128","sid":"1557","itemid":"1120","price":"3.00","cannotpay":"1","folderid":"4865","iname":"\u518d\u518d\u8bad","summary":"111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u518d\u518d\u8bad","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad1","pdisplayorder":"30","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"1","sdisplayorder":"0","grank":"120","prank":"1","srank":"1"}]},{"sid":"1568","pid":"1139","cover":"","summary":"","sdisplayorder":"0","sname":"\u560e\u560e","pname":"\u547c\u547chh","located":"0","pdisplayorder":"94","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1139","sid":"1568","itemid":"1136","price":"5.00","cannotpay":"0","folderid":"4877","iname":"wuli\u8c26123","summary":"wuli\u8c26wuli\u8c26wuli\u8c26wuli\u8c26123","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"2","foldername":"wuli\u8c26123","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"wuli\u8c26","pname":"\u547c\u547chh","pdisplayorder":"94","pcrid":"10622","located":"0","sname":"\u560e\u560e","showbysort":"1","sdisplayorder":"0","grank":"104","prank":"1","srank":"1"}]},{"sid":"1598","pid":"1144","cover":"","summary":"","sdisplayorder":"0","sname":"\u4e0a\u8863","pname":"\u8863\u670d","located":"0","pdisplayorder":"8","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"1144","sid":"1598","itemid":"1140","price":"0.01","cannotpay":"0","folderid":"4881","iname":"\u7f57\u8499","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"2","foldername":"\u7f57\u8499","cover":"","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u7f57","pname":"\u8863\u670d","pdisplayorder":"8","pcrid":"10622","located":"0","sname":"\u4e0a\u8863","showbysort":"1","sdisplayorder":"0","grank":"100","prank":"3","srank":"1"}]},{"sid":"3756","pid":"2141","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/06\/05\/14966469362953.jpg","summary":"5A\u8bfe\u7a0b\u662f\u59dc\u8d5e\u535a\u58eb\u72ec\u521b\u7684\u4f01\u4e1a\u7ba1\u7406\u7cfb\u7edf\uff0c\u5305\u542b\u4ee5\u4e0b\u4e94\u5927\u5757\u5185\u5bb9\uff1a1A\uff1a\u4f01\u4e1a\u6587\u5316\u7cfb\u7edf\uff08Culture\u7cfb\u7edf\uff09\u4f01\u4e1a\u601d\u60f3\u7075\u9b42\u7cfb\u7edf\uff0c\u6253\u9020\u4f01\u4e1a\u6e90\u52a8\u529b\u30022A\uff1a\u5de5\u4f5c\u6d41\u7a0b\u7cfb\u7edf\uff08Process\u7cfb\u7edf\uff09\u4f01\u4e1a\u5de5\u4f5c\u7a0b\u5e8f\u7cfb\u7edf\uff0c\u6253\u9020\u4f01\u4e1a\u6838\u5fc3\u529b\u30023A\uff1a\u90e8\u95e8\u804c\u4f4d\u7cfb\u7edf\uff08Office\u7cfb\u7edf\uff09\u4f01\u4e1a\u6307\u6325\u63a7\u5236\u7cfb\u7edf\uff0c\u6253\u9020\u4f01\u4e1a\u6267\u884c\u529b\u30024A\uff1a\u7ba1\u7406\u5236\u5ea6\u7cfb\u7edf\uff08System\u7cfb\u7edf\uff09\u4f01\u4e1a\u5de5\u4f5c\u6807\u51c6\u7cfb\u7edf\uff0c\u6253\u9020\u4f01\u4e1a\u63a7\u5236\u529b\u30025A\uff1a\u85aa\u916c\u7ee9\u6548\u7cfb\u7edf\uff08Remuneration\u7cfb\u7edf\uff09","sdisplayorder":"0","sname":"5A\u8bfe\u7a0b\u5305","pname":"5A\u7ba1\u7406\u7cfb\u7edf","located":"0","pdisplayorder":"1","pcrid":"12859","cannotpay":0,"showbysort":1,"items":[{"pid":"2141","sid":"3756","itemid":"8630","price":"1000.00","cannotpay":"0","folderid":"4881","iname":"\u7f57\u8499","summary":"\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499\u7f57\u8499","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7f57\u8499","cover":"","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u7f57","pname":"5A\u7ba1\u7406\u7cfb\u7edf","pdisplayorder":"1","pcrid":"12859","located":"0","sname":"5A\u8bfe\u7a0b\u5305","showbysort":"1","sdisplayorder":"0","grank":"100","prank":"3","srank":"1"}]},{"sid":"3762","pid":"2142","cover":"","summary":"","sdisplayorder":"0","sname":"\u53ea\u6709\u6253\u5305","pname":"\u5751\u4eba\u7684\u5305","located":"1","pdisplayorder":"10","pcrid":"10622","cannotpay":1,"showbysort":1,"items":[{"pid":"2142","sid":"3762","itemid":"8633","price":"0.00","cannotpay":"1","folderid":"26664","iname":"aaaaa","summary":"aaaaaaaaaaaa","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"aaaaa","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"aaaa","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":"\u53ea\u6709\u6253\u5305","showbysort":"1","sdisplayorder":"0","grank":"90","prank":"9","srank":"2"},{"pid":"2142","sid":"3762","itemid":"8634","price":"110.00","cannotpay":"1","folderid":"26665","iname":"\u89c6\u9891\u5f00\u573a","summary":"\u89c6\u9891\u5f00\u573a","view_mode":"0","islimit":"0","limitnum":"0","imonth":"3","iday":"0","foldername":"\u89c6\u9891\u5f00\u573a","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bbbbb","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":"\u53ea\u6709\u6253\u5305","showbysort":"1","sdisplayorder":"0","grank":"89","prank":"8","srank":"1"}]},{"sid":"3765","pid":"2142","cover":"","summary":"","sdisplayorder":"2147483647","sname":"444444","pname":"\u5751\u4eba\u7684\u5305","located":"1","pdisplayorder":"10","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"2142","sid":"3765","itemid":"8635","price":"0.00","cannotpay":"0","folderid":"26666","iname":"xxxx","summary":"xxxxxxxxxxxxxxxxx","view_mode":"0","islimit":"0","limitnum":"0","imonth":"3","iday":"0","foldername":"xxxx","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dfasdfadsfasdf","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":"444444","showbysort":"1","sdisplayorder":"2147483647","grank":"88","prank":"7","srank":"2"},{"pid":"2142","sid":"3765","itemid":"8636","price":"0.00","cannotpay":"0","folderid":"26667","iname":"\u56fe\u7247\u5f00\u573a","summary":"\u56fe\u7247\u5f00\u573a","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"\u56fe\u7247\u5f00\u573a","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfadsfadsf","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":"444444","showbysort":"1","sdisplayorder":"2147483647","grank":"87","prank":"6","srank":"1"}]},{"sid":"4419","pid":"372","cover":"","summary":"&nbsp;gfgsfdgsgsgdsfadsfadfasdfadsfa","sdisplayorder":"0","sname":"\u6253\u5305\u8bfe\u7a0b","pname":"\u591c\u6708","located":"0","pdisplayorder":"27","pcrid":"10622","cannotpay":0,"showbysort":1,"items":[{"pid":"372","sid":"4419","itemid":"26552","price":"10.00","cannotpay":"0","folderid":"3950","iname":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","summary":"\u591c\u6821\u7b80\u4ecb","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":"\u6253\u5305\u8bfe\u7a0b","showbysort":"1","sdisplayorder":"0","grank":"184","prank":"1","srank":"1"},{"pid":"372","sid":"4419","itemid":"26553","price":"123.00","cannotpay":"0","folderid":"3962","iname":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u6570\u5b66","summary":"\u8bfe\u7a0b\u4ecb\u7ecd\u4e0d\u80fd\u4e3a\u7a7a\u8bfe\u7a0b\u5206\u7c7b\u591c\u6708","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u6570\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/195_247_147.jpg","viewnum":"0","coursewarenum":"16","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":"\u6253\u5305\u8bfe\u7a0b","showbysort":"1","sdisplayorder":"0","grank":"53","prank":"53","srank":"53"}]},{"pid":"1144","sid":"1599","itemid":"36","price":"2.00","cannotpay":"0","folderid":"3066","iname":"1\u70b9\u70b9\u6ef4\u6ef4 444","summary":"\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"1\u70b9\u70b9\u6ef4\u6ef4","cover":null,"viewnum":"272","coursewarenum":"0","isschoolfree":"0","fcrid":"10443","showmode":"0","speaker":"","pname":"\u8863\u670d","pdisplayorder":"8","pcrid":"10622","located":"0","sname":"\u4e0b\u88e4","showbysort":"0","sdisplayorder":"1","grank":null,"prank":null,"srank":null},{"pid":"1144","sid":"1599","itemid":"87","price":"10.00","cannotpay":"0","folderid":"3334","iname":"\u8bed\u6587\u6d4b\u8bd51","summary":"\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u997f","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"\u8bed\u6587\u6d4b\u8bd51","cover":null,"viewnum":"150","coursewarenum":"3","isschoolfree":"0","fcrid":"10525","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8863\u670d","pdisplayorder":"8","pcrid":"10622","located":"0","sname":"\u4e0b\u88e4","showbysort":"0","sdisplayorder":"1","grank":null,"prank":null,"srank":null},{"pid":"379","sid":"350","itemid":"378","price":"48.00","cannotpay":"0","folderid":"3959","iname":"\u79d1\u5b66","summary":"\u79d1\u5b66\u79d1\u5b66","view_mode":"1","islimit":"0","limitnum":"0","imonth":"99","iday":"0","foldername":"\u79d1\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","viewnum":"0","coursewarenum":"10","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u5947\u5de7","pdisplayorder":"14","pcrid":"10622","located":"0","sname":"\u5947\u5de74","showbysort":"0","sdisplayorder":"0","grank":"186","prank":"2","srank":"2"},{"pid":"380","sid":"344","itemid":"384","price":"0.00","cannotpay":"1","folderid":"3958","iname":"\u5730\u7406","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"\u5730\u7406","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/19.jpg","viewnum":"0","coursewarenum":"4","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u9876\u6234","pdisplayorder":"12","pcrid":"10622","located":"0","sname":"\u6234\uff11","showbysort":"0","sdisplayorder":"0","grank":"185","prank":"1","srank":"1"},{"pid":"932","sid":"1291","itemid":"770","price":"0.00","cannotpay":"1","folderid":"3958","iname":"\u5730\u7406","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21\u677f\u7684\u5730\u7406\u5730\u7406\u5730\u7406\u5730\u7406\u6a21","view_mode":"1","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u5730\u7406","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/19.jpg","viewnum":"0","coursewarenum":"4","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6253\u5305\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"3","pcrid":"10622","located":"0","sname":"\u6253\u5305","showbysort":"0","sdisplayorder":"2","grank":"185","prank":"1","srank":"1"},{"pid":"932","sid":"1292","itemid":"773","price":"10.00","cannotpay":"0","folderid":"3950","iname":"\u7f8e\u5de5","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6253\u5305\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"3","pcrid":"10622","located":"0","sname":"\u90e8\u5206\u514d\u8d39","showbysort":"0","sdisplayorder":"1","grank":"184","prank":"1","srank":"1"},{"pid":"973","sid":"0","itemid":"792","price":"0.00","cannotpay":"0","folderid":"4434","iname":"\u52b3\u6280","summary":"\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280\u52b3\u6280","view_mode":"2","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u52b3\u6280","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/30_247_147.jpg","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8d77\u4f0f","pdisplayorder":"7","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"183","prank":"1","srank":"1"},{"pid":"987","sid":"1429","itemid":"822","price":"111.00","cannotpay":"0","folderid":"3950","iname":"\u7f8e\u5de5\u6346\u7ed1","summary":"\u8fd9\u662fplate\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60\u6a21\u677f\u7684\u7f8e\u5de5\u8bfe\uff0cphotoshop\u5b66\u4e60","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u7f8e\u5de5","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":"\u6346\u7ed1","showbysort":"0","sdisplayorder":"0","grank":"184","prank":"1","srank":"1"},{"pid":"987","sid":"1429","itemid":"823","price":"10.00","cannotpay":"0","folderid":"3959","iname":"\u79d1\u5b66","summary":"\u79d1\u5b66\u79d1\u5b66","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u79d1\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/3.jpg","viewnum":"0","coursewarenum":"10","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":"\u6346\u7ed1","showbysort":"0","sdisplayorder":"0","grank":"186","prank":"2","srank":"2"},{"pid":"1016","sid":"1454","itemid":"959","price":"1000.00","cannotpay":"0","folderid":"4588","iname":"\u516d(\u4e0a)","summary":"\u516d(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u516d(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"182","prank":"12","srank":"12"},{"pid":"1016","sid":"1454","itemid":"960","price":"0.00","cannotpay":"0","folderid":"4589","iname":"\u516d(\u4e0b)","summary":"\u516d(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u516d(\u4e0b)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"181","prank":"11","srank":"11"},{"pid":"1016","sid":"1454","itemid":"961","price":"0.00","cannotpay":"0","folderid":"4590","iname":"\u4e94(\u4e0a)","summary":"\u4e94(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e94(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"180","prank":"10","srank":"10"},{"pid":"1016","sid":"1454","itemid":"962","price":"0.00","cannotpay":"0","folderid":"4591","iname":"\u4e94(\u4e0b)","summary":"\u4e94(\u4e0b)\u4e94(\u4e0b)\u4e94(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e94(\u4e0b)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"179","prank":"9","srank":"9"},{"pid":"1016","sid":"1454","itemid":"963","price":"0.00","cannotpay":"0","folderid":"4592","iname":"\u56db(\u4e0a)","summary":"\u56db(\u4e0a)\u56db(\u4e0a)\u56db(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u56db(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"178","prank":"8","srank":"8"},{"pid":"1016","sid":"1454","itemid":"964","price":"0.00","cannotpay":"0","folderid":"4593","iname":"\u56db(\u4e0b)","summary":"\u56db(\u4e0b)\u56db(\u4e0b)\u56db(\u4e0b)\u56db(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u56db(\u4e0b)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"177","prank":"7","srank":"7"},{"pid":"1016","sid":"1454","itemid":"965","price":"0.00","cannotpay":"0","folderid":"4594","iname":"\u4e09(\u4e0a)","summary":"\u4e09(\u4e0a)\u4e09(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e09(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"176","prank":"6","srank":"6"},{"pid":"1016","sid":"1454","itemid":"966","price":"0.00","cannotpay":"0","folderid":"4595","iname":"\u4e09(\u4e0b)","summary":"\u4e09(\u4e0b)\u4e09(\u4e0b)\u4e09(\u4e0b)\u4e09(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e09(\u4e0b)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"175","prank":"5","srank":"5"},{"pid":"1016","sid":"1454","itemid":"967","price":"0.00","cannotpay":"0","folderid":"4596","iname":"\u4e8c(\u4e0a)","summary":"\u4e8c(\u4e0a)\u4e8c(\u4e0a)\u4e8c(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e8c(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"174","prank":"4","srank":"4"},{"pid":"1016","sid":"1454","itemid":"968","price":"0.00","cannotpay":"0","folderid":"4597","iname":"\u4e8c(\u4e0b)","summary":"\u4e8c(\u4e0b)\u4e8c(\u4e0b)\u4e8c(\u4e0b)\u4e8c(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e8c(\u4e0b)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"173","prank":"3","srank":"3"},{"pid":"1016","sid":"1454","itemid":"969","price":"0.00","cannotpay":"0","folderid":"4598","iname":"\u4e00(\u4e0a)","summary":"\u4e00(\u4e0a)\u4e00(\u4e0a)\u4e00(\u4e0a)\u4e00(\u4e0a)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e00(\u4e0a)","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"172","prank":"2","srank":"2"},{"pid":"1016","sid":"1454","itemid":"970","price":"0.00","cannotpay":"0","folderid":"4599","iname":"\u4e00(\u4e0b)","summary":"\u4e00(\u4e0b)\u4e00(\u4e0b)\u4e00(\u4e0b)\u4e00(\u4e0b)\u4e00(\u4e0b)\u4e00(\u4e0b)","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"\u4e00(\u4e0b)","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/06\/08\/14968914081623_243_144.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"bug","pname":"bug","pdisplayorder":"29","pcrid":"10622","located":"0","sname":"\u53ea\u6709\u4e00\u4e2a\u6536\u8d39","showbysort":"0","sdisplayorder":"0","grank":"171","prank":"1","srank":"1"},{"pid":"987","sid":"0","itemid":"1065","price":"0.00","cannotpay":"0","folderid":"4740","iname":"qwqw","summary":"qwqw","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"qwqw","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/06\/08\/14968915444172_243_144.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"qwqwqwq","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"170","prank":"4","srank":"4"},{"pid":"1066","sid":"0","itemid":"1068","price":"2.00","cannotpay":"0","folderid":"4805","iname":"\u670d\u52a1\u5305,\u4e0d\u5f00\u901a\u8bfe\u7a0b1","summary":"\u670d\u52a1\u5305,\u4e0d\u5f00\u901a\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"\u670d\u52a1\u5305,\u4e0d\u5f00\u901a\u8bfe\u7a0b1","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"555","pdisplayorder":"92","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"169","prank":"1","srank":"1"},{"pid":"1065","sid":"0","itemid":"1069","price":"10000.00","cannotpay":"0","folderid":"4763","iname":"\u5206\u6210\u6536\u8d39","summary":"\u5206\u6210\u6536\u8d39","view_mode":"0","islimit":"0","limitnum":"0","imonth":"500","iday":"0","foldername":"\u5206\u6210\u6536\u8d39","cover":"","viewnum":"18","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u6653\u7d2b\u670d\u52a1","pdisplayorder":"93","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"168","prank":"1","srank":"1"},{"pid":"1071","sid":"0","itemid":"1070","price":"1.00","cannotpay":"0","folderid":"4813","iname":"\u6536\u8d39","summary":"1","view_mode":"0","islimit":"0","limitnum":"0","imonth":"30","iday":"0","foldername":"\u6536\u8d39xx","cover":"","viewnum":"1450","coursewarenum":"136","isschoolfree":"0","fcrid":"10622","showmode":"3","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u670d\u52a1\u53051","pdisplayorder":"87","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"167","prank":"1","srank":"1"},{"pid":"1073","sid":"0","itemid":"1071","price":"1.00","cannotpay":"0","folderid":"4820","iname":"ss","summary":"ssssss","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"linziliang","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"ss1","pdisplayorder":"85","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"166","prank":"1","srank":"1"},{"pid":"1073","sid":"0","itemid":"1072","price":"1.00","cannotpay":"0","folderid":"4820","iname":"ss","summary":"ssssss","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"linziliang","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"ss1","pdisplayorder":"85","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"166","prank":"1","srank":"1"},{"pid":"1091","sid":"0","itemid":"1073","price":"2.00","cannotpay":"0","folderid":"4817","iname":"\u54c7\u54c8\u54c8","summary":"123","view_mode":"0","islimit":"0","limitnum":"0","imonth":"44","iday":"0","foldername":"\u54c7\u54c8\u54c8","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/15\/14606994837347.jpg","viewnum":"400","coursewarenum":"8","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u4f53\u80b2\u8bfe\u670d\u52a1\u5305","pdisplayorder":"67","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"165","prank":"1","srank":"1"},{"pid":"1092","sid":"0","itemid":"1074","price":"5.00","cannotpay":"0","folderid":"4816","iname":"00","summary":"0000","view_mode":"0","islimit":"0","limitnum":"0","imonth":"66","iday":"0","foldername":"00","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/2.jpg","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u7f8e\u672f\u8bfe\u670d\u52a1\u5305","pdisplayorder":"66","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"164","prank":"1","srank":"1"},{"pid":"1093","sid":"0","itemid":"1075","price":"0.00","cannotpay":"0","folderid":"4819","iname":"\u8bfa\u4e00\u6536","summary":"1","view_mode":"0","islimit":"0","limitnum":"0","imonth":"55","iday":"0","foldername":"\u8bfa\u4e00\u6536","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/26\/14616370583464.jpg","viewnum":"0","coursewarenum":"3","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u624b\u5de5\u8bfe\u670d\u52a1\u5305","pdisplayorder":"65","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"163","prank":"1","srank":"1"},{"pid":"1094","sid":"0","itemid":"1076","price":"3.00","cannotpay":"0","folderid":"4809","iname":"\u547c\u54c8\u8bfe\u7a0b1","summary":"1231","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u547c\u54c8\u8bfe\u7a0b1","cover":"","viewnum":"250","coursewarenum":"13","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u547c\u547c\u54c8\u54c8\u670d\u52a1\u5305","pdisplayorder":"64","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"162","prank":"1","srank":"1"},{"pid":"1095","sid":"0","itemid":"1077","price":"2.00","cannotpay":"0","folderid":"4807","iname":"xx","summary":"\u662f\u53cd\u5012\u662f\u53cd\u5012\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u751f\u7684\u7684\u8bf4\u6cd5\u90fd\u662f\u5b9e\u5f97\u5206\u662f\u5426\u662f\u7684\u7b2c\u4e09\u65b9\u7684\u8bf4\u6cd5\u5b9e\u5f97\u5206\u5b9e\u5f97\u5206\u5b9e\u5f97\u5206","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"xx","cover":"","viewnum":"50","coursewarenum":"2","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"cesguga","pdisplayorder":"63","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"161","prank":"1","srank":"1"},{"pid":"1096","sid":"1546","itemid":"1078","price":"0.10","cannotpay":"0","folderid":"4808","iname":"\u8be6\u60c5\u6a21\u5f0f","summary":"123","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u8be6\u60c5\u6a21\u5f0f","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/guwen.jpg","viewnum":"300","coursewarenum":"5","isschoolfree":"1","fcrid":"10622","showmode":"3","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u81ea\u7531\u5206\u7c7b","pdisplayorder":"62","pcrid":"10622","located":"0","sname":"\u751f\u547d1\u53f7","showbysort":"0","sdisplayorder":"0","grank":"160","prank":"1","srank":"1"},{"pid":"1068","sid":"0","itemid":"1079","price":"1.00","cannotpay":"0","folderid":"4814","iname":"a","summary":"\u5b9e\u5f97\u5206\u8bfe\u7a0b\u8be6\u7ec6\u4ecb\u7ecd","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"a","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/08\/14600953805895.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"159","prank":"7","srank":"7"},{"pid":"1068","sid":"0","itemid":"1080","price":"1.00","cannotpay":"0","folderid":"4815","iname":"b","summary":"b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"b","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/08\/14600956611896.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"158","prank":"6","srank":"6"},{"pid":"1068","sid":"0","itemid":"1081","price":"1.00","cannotpay":"0","folderid":"4810","iname":"1","summary":"1","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"1","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"157","prank":"5","srank":"5"},{"pid":"1068","sid":"0","itemid":"1082","price":"1.00","cannotpay":"0","folderid":"4806","iname":"zz","summary":"zzzzzzzzzzzzz","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"zz","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"1","speaker":"","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"156","prank":"4","srank":"4"},{"pid":"1068","sid":"0","itemid":"1083","price":"1.00","cannotpay":"0","folderid":"4786","iname":"2","summary":"2","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"2","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"155","prank":"3","srank":"3"},{"pid":"1068","sid":"0","itemid":"1084","price":"1.00","cannotpay":"0","folderid":"4752","iname":"3333333","summary":"333333333333333","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"3333333","cover":"","viewnum":"285","coursewarenum":"4","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"154","prank":"2","srank":"2"},{"pid":"1068","sid":"0","itemid":"1085","price":"1.00","cannotpay":"0","folderid":"4750","iname":"222222222222222","summary":"222222222222222","view_mode":"0","islimit":"0","limitnum":"0","imonth":"111","iday":"0","foldername":"222222222222222","cover":"","viewnum":"153","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u6653\u7d2b\u7684\u670d\u52a1\u5305","pdisplayorder":"90","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"153","prank":"1","srank":"1"},{"pid":"1098","sid":"0","itemid":"1086","price":"0.00","cannotpay":"0","folderid":"4823","iname":"\u672c\u6821ye\u514d\u8d39\u9ed8\u8ba4","summary":"\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6\u672c\u6821\u5b66\u4e60\u54e6","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"\u672c\u6821\u5b66\u4e60\u54e6","cover":"","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821","pdisplayorder":"60","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"152","prank":"1","srank":"1"},{"pid":"1099","sid":"0","itemid":"1087","price":"0.00","cannotpay":"0","folderid":"4818","iname":"6\u7ec4\u514d\u8d39","summary":"123000","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"(*^__^*) \u563b\u563b\u2026\u2026","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/16\/14607787882734.jpg","viewnum":"0","coursewarenum":"16","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u5e74\u7ea7\u7ec4","pdisplayorder":"59","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"151","prank":"1","srank":"1"},{"pid":"1100","sid":"0","itemid":"1088","price":"1.00","cannotpay":"0","folderid":"4824","iname":"\u54b3\u54b3\u54b3","summary":"\u6240\u53d1\u751f\u7684\u53d1\u58eb\u5927\u592b\u5b9e\u5f97\u5206\u662f\u7684","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u54b3\u54b3\u54b3","cover":"","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"58","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"150","prank":"1","srank":"1"},{"pid":"1101","sid":"0","itemid":"1089","price":"0.00","cannotpay":"0","folderid":"4825","iname":"\u8bbe\u7f6e\u4e0d\u514d\u8d39,\u91d1\u989d\u662f0 ,\u4f60\u731c\u514d\u4e0d\u514d\u8d39","summary":"\uff58\uff58\uff11\uff12\uff13\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u201c\u8303\u5fb7\u8428\u8303\u5fb7\u201d\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u2018\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u2019\u7684\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\uff1f\u8428\u8303\u5fb7\u3002\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\uff0c\u6cd5\u7684\u662f\u8303\u5fb7\u8428.....\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u300a\u6cd5\u7684\u662f\u8303\u300b\u5fb7\u8428\u8303&lt;\u5fb7\u8428\u53d1\u7684&gt;\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5\u7684\u662f\u8303\u5fb7\u8428\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5\u80dc\u591a\u8d1f\u5c11\u7b2c\u4e09\u65b9\u53d1\u7684\u662f\u975e\u5f97\u5931\u5730\u65b9\u5730\u65b9\u5730\u65b9\u5730\u65b9\u90fd\u662f\u5730\u65b9\u5730\u65b9\u6495\u6389\u3000\u5927\u5e45\u5ea6","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u8bbe\u7f6e\u4e0d\u514d\u8d39,\u91d1\u989d\u662f0 ,\u4f60\u731c\u514d\u4e0d\u514d\u8d39","cover":"","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"nicai","pdisplayorder":"57","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"149","prank":"1","srank":"1"},{"pid":"1102","sid":"0","itemid":"1090","price":"0.00","cannotpay":"0","folderid":"4827","iname":"\u8bfe\u7a0b","summary":"11111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"\u8bfe\u7a0b","cover":"","viewnum":"100","coursewarenum":"2","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"kecheng","pdisplayorder":"56","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"148","prank":"1","srank":"1"},{"pid":"1103","sid":"1549","itemid":"1091","price":"0.50","cannotpay":"0","folderid":"4822","iname":"www\u514d\u8d39\u5f00\u8bbe\u7684\u8bfe\u7a0b","summary":"\u514d\u8d39\u7684\u8bfe\u7a0b\u54e6","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"www\u514d\u8d39\u5f00\u8bbe\u7684\u8bfe\u7a0b","cover":"","viewnum":"450","coursewarenum":"14","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u8d3e\u8001\u5e08\u5594","pname":"^(*\uffe3(oo)\uffe3)^","pdisplayorder":"55","pcrid":"10622","located":"0","sname":"O(\u2229_\u2229)O\u8c22\u8c22","showbysort":"0","sdisplayorder":"0","grank":"147","prank":"1","srank":"1"},{"pid":"1105","sid":"0","itemid":"1092","price":"0.00","cannotpay":"0","folderid":"4828","iname":"m","summary":"mmm","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"m","cover":"","viewnum":"300","coursewarenum":"37","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"yiyiyaya","pname":"mmm","pdisplayorder":"53","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"146","prank":"1","srank":"1"},{"pid":"1107","sid":"0","itemid":"1093","price":"0.00","cannotpay":"0","folderid":"4835","iname":"\u514d1","summary":"11111111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u514d","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/guwen.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"mian","pdisplayorder":"51","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"145","prank":"1","srank":"1"},{"pid":"1108","sid":"0","itemid":"1094","price":"1.00","cannotpay":"0","folderid":"4836","iname":"+","summary":"+","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"+","cover":"","viewnum":"750","coursewarenum":"35","isschoolfree":"1","fcrid":"10622","showmode":"3","speaker":"=","pname":"\u52a0\u53f7","pdisplayorder":"50","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"144","prank":"1","srank":"1"},{"pid":"1109","sid":"0","itemid":"1095","price":"0.00","cannotpay":"0","folderid":"4837","iname":"=_=!!!","summary":"=_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_==_=","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"=_=","cover":"","viewnum":"1000","coursewarenum":"69","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"=_=","pdisplayorder":"49","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"143","prank":"1","srank":"1"},{"pid":"1110","sid":"0","itemid":"1096","price":"2.00","cannotpay":"0","folderid":"4839","iname":"=_=sssss","summary":"=_=","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"=_=s","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"=_=sss","pdisplayorder":"48","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"142","prank":"1","srank":"1"},{"pid":"1111","sid":"0","itemid":"1097","price":"6.00","cannotpay":"0","folderid":"4840","iname":"xx","summary":"xx","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"xx","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"(*^__^*)","pdisplayorder":"47","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"141","prank":"1","srank":"1"},{"pid":"1112","sid":"0","itemid":"1098","price":"2.00","cannotpay":"0","folderid":"4841","iname":"\u547c\u54d2\u54d224","summary":"\u547c\u54d2\u54d224","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u547c\u54d2\u54d224","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u547c\u54d2\u54d224\u670d\u52a1\u5305","pdisplayorder":"46","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"140","prank":"1","srank":"1"},{"pid":"1114","sid":"0","itemid":"1099","price":"2.00","cannotpay":"0","folderid":"4842","iname":"\u975e\u5927\u5b66\u7248\u6dfb\u52a0\u7684\u8bfe\u7a0b(*^__^*) \u563b\u563b\u2026\u2026","summary":"\u975e\u5927\u5b66\u7248\u6dfb\u52a0\u7684\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"33","iday":"0","foldername":"\u975e\u5927\u5b66\u7248\u6dfb\u52a0\u7684\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/92.jpg","viewnum":"100","coursewarenum":"11","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u975e\u5927\u5b66","pdisplayorder":"44","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"139","prank":"1","srank":"1"},{"pid":"1115","sid":"0","itemid":"1100","price":"3.00","cannotpay":"0","folderid":"4843","iname":"\u6d4b\u8bd5\u670d\u52a1\u5305\u5220\u9664\u7684\u8bfe\u7a0b","summary":"\u6d4b\u8bd5\u670d\u52a1\u5305\u5220\u9664\u7684\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"22","iday":"0","foldername":"\u6d4b\u8bd5\u670d\u52a1\u5305\u5220\u9664\u7684\u8bfe\u7a0b","cover":"","viewnum":"0","coursewarenum":"4","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u5929\u738b\u76d6\u5730\u864e\u7684\u670d\u52a1\u5305","pdisplayorder":"43","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"138","prank":"2","srank":"2"},{"pid":"1116","sid":"0","itemid":"1101","price":"1.00","cannotpay":"0","folderid":"4845","iname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b,\u4ef7\u683c1,\u671f\u96501\u65e5","summary":"school\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0bschool\u514d\u8d39\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/17.jpg","viewnum":"50","coursewarenum":"2","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u5de6\u9752\u9f99\u53f3\u767d\u864e\u7684\u670d\u52a1\u5305","pdisplayorder":"42","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"137","prank":"1","srank":"1"},{"pid":"1115","sid":"0","itemid":"1102","price":"1.00","cannotpay":"0","folderid":"4844","iname":"all\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684,\u4ef7\u683c1,\u671f\u96501\u65e5","summary":"\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"all\u514d\u8d39\u8bfe\u7a0b\u4e0b\u8981\u5e03\u7f6e\u4f5c\u4e1a\u7684","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/13.jpg","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u8d3e\u8001\u5e08O(\u2229_\u2229)O\u54c8\u54c8~","pname":"\u5929\u738b\u76d6\u5730\u864e\u7684\u670d\u52a1\u5305","pdisplayorder":"43","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"136","prank":"1","srank":"1"},{"pid":"1118","sid":"0","itemid":"1103","price":"5.00","cannotpay":"0","folderid":"4846","iname":"pay\u6536\u8d39\u5c0f\u8bfe\u7a0b","summary":"pay\u6536\u8d39\u5c0f\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"pay\u6536\u8d39\u5c0f\u8bfe\u7a0b","cover":"","viewnum":"395","coursewarenum":"7","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u8d3e\u8001\u5e08O(\u2229_\u2229)O\u54c8\uff01","pname":"pay","pdisplayorder":"40","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"135","prank":"1","srank":"1"},{"pid":"1119","sid":"0","itemid":"1104","price":"2.00","cannotpay":"0","folderid":"4848","iname":"\u5feb\u4e50\u81f3\u4e0a3\u5929","summary":"\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8\u54c8","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"3","foldername":"\u5feb\u4e50\u81f3\u4e0a~","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/12.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"kuail\u81f3\u4e0a","pdisplayorder":"39","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"134","prank":"1","srank":"1"},{"pid":"1120","sid":"0","itemid":"1105","price":"1.00","cannotpay":"0","folderid":"4832","iname":"\u6570\u5b66\u4e0a123456789123456789123456789123456789123456789","summary":"1","view_mode":"0","islimit":"0","limitnum":"0","imonth":"23","iday":"0","foldername":"\u6570\u5b66\u4e0a","cover":"","viewnum":"0","coursewarenum":"5","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"kuailexxx","pdisplayorder":"38","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"133","prank":"1","srank":"1"},{"pid":"1121","sid":"0","itemid":"1106","price":"0.00","cannotpay":"0","folderid":"4849","iname":"\u7edd\u5bf9\u514d\u8d39","summary":"\u7edd\u5bf9\u514d\u8d39","view_mode":"0","islimit":"0","limitnum":"0","imonth":"44","iday":"0","foldername":"\u7edd\u5bf9\u514d\u8d39","cover":"","viewnum":"12000","coursewarenum":"75","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u7edd\u5bf9\u514d","pdisplayorder":"37","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"132","prank":"2","srank":"2"},{"pid":"1123","sid":"1551","itemid":"1108","price":"2.00","cannotpay":"0","folderid":"4853","iname":"\u8bfe\u7a0b2","summary":"\u7684\u7684\u8303\u5fb7\u8428\u53d1\u7684\u8bf4\u6cd5","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0b2","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"duo","pdisplayorder":"35","pcrid":"10622","located":"0","sname":"1","showbysort":"0","sdisplayorder":"0","grank":"130","prank":"5","srank":"1"},{"pid":"1123","sid":"0","itemid":"1109","price":"3.00","cannotpay":"0","folderid":"4854","iname":"\u8bfe\u7a0b3","summary":"\u8bfe\u7a0b3","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0b3","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"duo","pdisplayorder":"35","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"129","prank":"4","srank":"1"},{"pid":"1124","sid":"1553","itemid":"1113","price":"2.00","cannotpay":"1","folderid":"4858","iname":"\u601d\u60f3123456789123456789123456789123456789123456789","summary":"\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"2","foldername":"\u601d\u60f3123456789123456789123456789123456789123456789","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/10_247_147.jpg","viewnum":"0","coursewarenum":"6","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u6a21\u5f0f","pdisplayorder":"34","pcrid":"10622","located":"0","sname":"\u8fd9\u662f\u5206\u7c7b","showbysort":"0","sdisplayorder":"0","grank":"125","prank":"1","srank":"1"},{"pid":"1125","sid":"1554","itemid":"1114","price":"1.00","cannotpay":"0","folderid":"4859","iname":"\u57f9\u8bad","summary":"1111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u57f9\u8bad","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad","pdisplayorder":"33","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"0","sdisplayorder":"0","grank":"124","prank":"4","srank":"3"},{"pid":"1125","sid":"1554","itemid":"1115","price":"2.00","cannotpay":"0","folderid":"4860","iname":"\u518d\u57f9\u8bad","summary":"111111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u518d\u57f9\u8bad","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad","pdisplayorder":"33","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"0","sdisplayorder":"0","grank":"123","prank":"3","srank":"2"},{"pid":"1129","sid":"0","itemid":"1119","price":"1.00","cannotpay":"0","folderid":"4861","iname":"\u4e00\u6b21","summary":"1111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u4e00\u6b21","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u4e00\u6b21\u670d\u52a1\u5305\u5206\u5f00","pdisplayorder":"28","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"121","prank":"1","srank":"1"},{"pid":"1130","sid":"0","itemid":"1122","price":"1.00","cannotpay":"0","folderid":"4867","iname":"\u8bfe\u7a0b\u8fdb\u5ea6","summary":"1111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bfe\u7a0b\u8fdb\u5ea6-\u8be6\u60c5\u6a21\u5f0f","cover":"","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"3","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u5929\u7075\u7075\u5730\u7075\u7075","pdisplayorder":"25","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"118","prank":"1","srank":"1"},{"pid":"1125","sid":"0","itemid":"1123","price":"1.00","cannotpay":"0","folderid":"4868","iname":"\u8bad0","summary":"1111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8bad0","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad","pdisplayorder":"33","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"117","prank":"2","srank":"1"},{"pid":"1125","sid":"1554","itemid":"1124","price":"1.00","cannotpay":"0","folderid":"4857","iname":"\u7f8e\u672f","summary":"\u7f8e\u672f","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u7f8e\u672f","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/40_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8bad","pdisplayorder":"33","pcrid":"10622","located":"0","sname":"\u8bad\u7c7b","showbysort":"0","sdisplayorder":"0","grank":"116","prank":"1","srank":"1"},{"pid":"1132","sid":"0","itemid":"1125","price":"1.00","cannotpay":"1","folderid":"4870","iname":"a b c \u9ed8\u8ba4","summary":"a b ca b ca b ca b ca b c","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"2","foldername":"a b c \u9ed8\u8ba4","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u997f \u997f \u997f","pdisplayorder":"21","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"115","prank":"1","srank":"1"},{"pid":"1133","sid":"1560","itemid":"1126","price":"1.00","cannotpay":"0","folderid":"4869","iname":"\u8fdc\u5927\u524d\u7a0b","summary":"11111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u8fdc\u5927\u524d\u7a0b","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":"\u591a\u591a","showbysort":"0","sdisplayorder":"0","grank":"114","prank":"6","srank":"4"},{"pid":"1133","sid":"1560","itemid":"1127","price":"1.00","cannotpay":"0","folderid":"4871","iname":"\u60b2\u60e8\u4e16\u754c","summary":"abcd","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u60b2\u60e8\u4e16\u754c","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":"\u591a\u591a","showbysort":"0","sdisplayorder":"0","grank":"113","prank":"5","srank":"3"},{"pid":"1133","sid":"1560","itemid":"1128","price":"1.00","cannotpay":"0","folderid":"4872","iname":"\u767e\u4e07\u5bcc\u7fc1","summary":"abcde","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u767e\u4e07\u5bcc\u7fc1","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":"\u591a\u591a","showbysort":"0","sdisplayorder":"0","grank":"112","prank":"4","srank":"2"},{"pid":"1133","sid":"1560","itemid":"1129","price":"0.00","cannotpay":"0","folderid":"4873","iname":"abcdef \u6709\u5206\u7c7b\uff0c0\u5143","summary":"abcdef","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u4eca\u5929\u4f60\u4ece\u5bb9\u4e86\u5417","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":"\u591a\u591a","showbysort":"0","sdisplayorder":"0","grank":"111","prank":"3","srank":"1"},{"pid":"1138","sid":"0","itemid":"1130","price":"99.00","cannotpay":"0","folderid":"4744","iname":"\u8bed\u6587\u8bfe\u516c\u5f00\u8bfe\u514d\u8d39\u7684","summary":"\u540c\u6b65\u7684\u8bed\u6587\u8bfe\u516c\u5f00\u8bfe\u514d\u8d39\u7684","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u8bed\u6587\u8bfe\u516c\u5f00\u8bfe\u514d\u8d39\u7684","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/9.jpg","viewnum":"100","coursewarenum":"9","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"9","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"110","prank":"5","srank":"5"},{"pid":"1138","sid":"0","itemid":"1131","price":"99.00","cannotpay":"0","folderid":"4826","iname":"\u54c8\u54c8\u54c8\u54c8\u54c8","summary":"\u540c\u6b65\u7684\u54c8\u54c8\u54c8\u54c8\u54c8","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u54c8\u54c8\u54c8\u54c8\u54c8","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"9","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"109","prank":"4","srank":"4"},{"pid":"1138","sid":"0","itemid":"1132","price":"99.00","cannotpay":"0","folderid":"4829","iname":"\u9ed8\u8ba4\u4e0d\u9009\u73ed\u7ea7\u7684\u8bfe\u7a0b","summary":"\u540c\u6b65\u7684\u9ed8\u8ba4\u4e0d\u9009\u73ed\u7ea7\u7684\u8bfe\u7a0b","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u9ed8\u8ba4\u4e0d\u9009\u73ed\u7ea7\u7684\u8bfe\u7a0b","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"9","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"108","prank":"3","srank":"3"},{"pid":"1138","sid":"0","itemid":"1133","price":"99.00","cannotpay":"0","folderid":"4833","iname":"jifendekecheng","summary":"\u540c\u6b65\u7684jifendekecheng","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"jifendekecheng","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"9","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"107","prank":"2","srank":"2"},{"pid":"1138","sid":"0","itemid":"1134","price":"99.00","cannotpay":"0","folderid":"4834","iname":"\u6709word,swf,ppt\u7b49","summary":"\u540c\u6b65\u7684\u6709word,swf,ppt\u7b49","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6709word,swf,ppt\u7b49","cover":"","viewnum":"50","coursewarenum":"6","isschoolfree":"1","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"9","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"106","prank":"1","srank":"1"},{"pid":"1139","sid":"0","itemid":"1135","price":"10.00","cannotpay":"0","folderid":"4876","iname":"GG\u560e\u560e","summary":"\u560e\u560e\u560e\u560e","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"2","foldername":"GG\u560e\u560e","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u547c\u547chh","pdisplayorder":"94","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"105","prank":"2","srank":"1"},{"pid":"1141","sid":"1569","itemid":"1137","price":"2.00","cannotpay":"0","folderid":"4878","iname":"20170227","summary":"\u5931\u7720\u4e00\u665a\uff0c\u56f0\u56f0\u54d2","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"20170227","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/02\/27\/1488159625910_th.gif","viewnum":"0","coursewarenum":"27","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u8d3e\u8001\u5e08\uff0c\u601d\u5bc6\u8fbe","pname":"0227\u7684\u5206\u7c7b","pdisplayorder":"26","pcrid":"10622","located":"0","sname":"\u4e8c\u7ea7\u5206\u7c7b","showbysort":"0","sdisplayorder":"0","grank":"103","prank":"2","srank":"1"},{"pid":"1141","sid":"0","itemid":"1138","price":"0.00","cannotpay":"0","folderid":"4879","iname":"\u514d\u8d3920170227","summary":"\u514d\u8d39\u8bfe\u7a0b1\u65e5\u6e38\u54e620170227","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u514d\u8d3920170227","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/02\/27\/14881625656415_th.png","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u8d3e\u8001\u5e08\uff0c\u54e6\u4e5f","pname":"0227\u7684\u5206\u7c7b","pdisplayorder":"26","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"102","prank":"1","srank":"1"},{"pid":"1142","sid":"1570","itemid":"1139","price":"3.00","cannotpay":"0","folderid":"4880","iname":"170228\uff08\u671f\u96501\u5929\u54e6\uff09","summary":"...............................................","view_mode":"1","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"170228\uff08\u671f\u96501\u5929\u54e6\uff09","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/02\/28\/14882781388500_th.jpg","viewnum":"0","coursewarenum":"5","isschoolfree":"0","fcrid":"10622","showmode":"1","speaker":"\u8d3e\u8001\u5e08\u554a\u554a\u554a\u554a\u554a","pname":"\u7b97\u4f60\u72e0","pdisplayorder":"20","pcrid":"10622","located":"0","sname":"\u7b97\u4f60\u72e0+1","showbysort":"0","sdisplayorder":"0","grank":"101","prank":"1","srank":"1"},{"pid":"1144","sid":"1599","itemid":"1142","price":"1.00","cannotpay":"1","folderid":"4883","iname":"\u841d\u535c\u72792","summary":"1111111111111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u841d\u535c\u72792","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"123","pname":"\u8863\u670d","pdisplayorder":"8","pcrid":"10622","located":"0","sname":"\u4e0b\u88e4","showbysort":"0","sdisplayorder":"1","grank":"99","prank":"2","srank":"1"},{"pid":"1144","sid":"0","itemid":"1143","price":"2.00","cannotpay":"0","folderid":"4884","iname":"\u6709\u4f18\u60e0","summary":"111111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u6709\u4f18\u60e0","cover":"","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"2","pname":"\u8863\u670d","pdisplayorder":"8","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"98","prank":"1","srank":"1"},{"pid":"1140","sid":"1576","itemid":"1144","price":"0.00","cannotpay":"0","folderid":"4887","iname":"\u6c34\u7535\u8d39\u5927\u53d4\u5927\u5a76\u5bf9\u5e94","summary":"\u963f\u65af\u8482\u82ac\u963f\u8fbe\u8bf4\u7684","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"\u6c34\u7535\u8d39\u5927\u53d4\u5927\u5a76\u5bf9\u5e94","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/04\/07\/14915648706317.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u963f\u8fbe\u554a","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":"\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a\u554a","showbysort":"0","sdisplayorder":"1","grank":"97","prank":"28","srank":"1"},{"pid":"1121","sid":"0","itemid":"1145","price":"1.00","cannotpay":"0","folderid":"4888","iname":"\u9644\u52a0\u8bfe\u7a0b","summary":"11111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u9644\u52a0\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/28_247_147.jpg","viewnum":"0","coursewarenum":"5","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"","pname":"\u7edd\u5bf9\u514d","pdisplayorder":"37","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"96","prank":"1","srank":"1"},{"pid":"1133","sid":"0","itemid":"1146","price":"1.00","cannotpay":"0","folderid":"4889","iname":"\u9644\u5c5e","summary":"111111111","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u9644\u5c5e","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/05\/18\/14950783517355_243_144.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u9644\u5c5e\u8001\u5e08","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"95","prank":"2","srank":"2"},{"pid":"1140","sid":"0","itemid":"1148","price":"100.00","cannotpay":"0","folderid":"4891","iname":"2017-06-09\u8bfe\u4ef6\u5b66\u4e60\u7edf\u8ba1\u6d4b\u8bd5","summary":"2017-06-09\u8bfe\u4ef6\u5b66\u4e60\u7edf\u8ba1\u6d4b\u8bd5","view_mode":"1","islimit":"0","limitnum":"0","imonth":"3","iday":"0","foldername":"2017-06-09\u8bfe\u4ef6\u5b66\u4e60\u7edf\u8ba1\u6d4b\u8bd5","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"2017-06-09\u8bfe\u4ef6\u5b66\u4e60\u7edf\u8ba1\u6d4b\u8bd5","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"94","prank":"27","srank":"26"},{"pid":"1140","sid":"0","itemid":"1149","price":"0.00","cannotpay":"0","folderid":"4892","iname":"\u5176\u4ed6\u8bfe\u7a0b","summary":"\u5176\u4ed6\u8bfe\u7a0b","view_mode":"1","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"\u5176\u4ed6\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u5176\u4ed6\u8bfe\u7a0b","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"93","prank":"26","srank":"25"},{"pid":"987","sid":"1429","itemid":"1150","price":"0.00","cannotpay":"1","folderid":"4893","iname":"\u6dfb\u52a0\u8bfe\u7a0b","summary":"\u6dfb\u52a0\u8bfe\u7a0b","view_mode":"2","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"\u6dfb\u52a0\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u6211","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":"\u6346\u7ed1","showbysort":"0","sdisplayorder":"0","grank":"92","prank":"25","srank":"1"},{"pid":"1133","sid":"0","itemid":"8631","price":"0.00","cannotpay":"0","folderid":"26662","iname":"ssssssssssssss","summary":"ssssssssssssssssss","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"ssssssssssssss","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"fsfsfsf","pname":"\u591a\u4e2a","pdisplayorder":"18","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"91","prank":"1","srank":"1"},{"pid":"379","sid":"350","itemid":"18777","price":"10.00","cannotpay":"0","folderid":"29079","iname":"\u5927\u89c4\u6a21","summary":"\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21\u5927\u89c4\u6a21","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5927\u89c4\u6a21","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u5927\u89c4\u6a21","pname":"\u5947\u5de7","pdisplayorder":"14","pcrid":"10622","located":"0","sname":"\u5947\u5de74","showbysort":"0","sdisplayorder":"0","grank":"86","prank":"1","srank":"1"},{"pid":"987","sid":"0","itemid":"19298","price":"0.00","cannotpay":"0","folderid":"29776","iname":"7.18\u6d4b\u8bd5","summary":"7.18\u6d4b\u8bd5","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"7.18\u6d4b\u8bd5","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u80e1\u8001\u5e08","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"85","prank":"3","srank":"3"},{"pid":"1140","sid":"0","itemid":"19299","price":"0.00","cannotpay":"0","folderid":"29777","iname":"123456","summary":"123456","view_mode":"0","islimit":"0","limitnum":"0","imonth":"5","iday":"0","foldername":"123456","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"123","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"84","prank":"24","srank":"24"},{"pid":"2142","sid":"0","itemid":"19300","price":"0.00","cannotpay":"0","folderid":"29778","iname":"456","summary":"456","view_mode":"0","islimit":"0","limitnum":"0","imonth":"3","iday":"0","foldername":"456","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"456","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"83","prank":"5","srank":"5"},{"pid":"1140","sid":"0","itemid":"19301","price":"0.00","cannotpay":"0","folderid":"29779","iname":"sdfasdf","summary":"asdfasdf","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"sdfasdf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dsfadf","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"82","prank":"23","srank":"23"},{"pid":"1140","sid":"0","itemid":"19302","price":"0.00","cannotpay":"0","folderid":"29780","iname":"sdfasdf","summary":"asdfasdf","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"sdfasdf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dsfadf","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"81","prank":"22","srank":"22"},{"pid":"1140","sid":"0","itemid":"19303","price":"0.00","cannotpay":"0","folderid":"29781","iname":"sdfasdf","summary":"asdfasdf","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"sdfasdf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dsfadf","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"80","prank":"21","srank":"21"},{"pid":"1140","sid":"0","itemid":"19304","price":"0.00","cannotpay":"0","folderid":"29782","iname":"sdfasdf","summary":"asdfasdf","view_mode":"0","islimit":"0","limitnum":"0","imonth":"10","iday":"0","foldername":"sdfasdf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dsfadf","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"79","prank":"20","srank":"20"},{"pid":"1140","sid":"0","itemid":"19305","price":"11.00","cannotpay":"0","folderid":"29783","iname":"ds","summary":"fasdfadsf","view_mode":"1","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"ds","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sdfasdfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"78","prank":"19","srank":"19"},{"pid":"1140","sid":"0","itemid":"19306","price":"0.00","cannotpay":"0","folderid":"29784","iname":"dafdasf","summary":"fg","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"dafdasf","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"dsfasdfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"77","prank":"18","srank":"18"},{"pid":"2142","sid":"0","itemid":"19307","price":"0.00","cannotpay":"0","folderid":"29785","iname":"dsfasfa","summary":"sdfasdfasdf","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"dsfasfa","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sdfadsfa","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"76","prank":"4","srank":"4"},{"pid":"2142","sid":"0","itemid":"19308","price":"0.00","cannotpay":"0","folderid":"29786","iname":"sssss","summary":"sssssssss","view_mode":"0","islimit":"0","limitnum":"0","imonth":"11","iday":"0","foldername":"sssss","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sssss","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"75","prank":"3","srank":"3"},{"pid":"1140","sid":"0","itemid":"19309","price":"0.00","cannotpay":"0","folderid":"29787","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"74","prank":"17","srank":"17"},{"pid":"1140","sid":"0","itemid":"19310","price":"0.00","cannotpay":"0","folderid":"29788","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"73","prank":"16","srank":"16"},{"pid":"1140","sid":"0","itemid":"19311","price":"0.00","cannotpay":"0","folderid":"29789","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"72","prank":"15","srank":"15"},{"pid":"1140","sid":"0","itemid":"19312","price":"0.00","cannotpay":"0","folderid":"29790","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"71","prank":"14","srank":"14"},{"pid":"1140","sid":"0","itemid":"19313","price":"0.00","cannotpay":"0","folderid":"29791","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"70","prank":"13","srank":"13"},{"pid":"1140","sid":"0","itemid":"19314","price":"0.00","cannotpay":"0","folderid":"29792","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"69","prank":"12","srank":"12"},{"pid":"1140","sid":"0","itemid":"19315","price":"0.00","cannotpay":"0","folderid":"29793","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"68","prank":"11","srank":"11"},{"pid":"1140","sid":"0","itemid":"19316","price":"0.00","cannotpay":"0","folderid":"29794","iname":"ff","summary":"fff","view_mode":"1","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"67","prank":"10","srank":"10"},{"pid":"1140","sid":"0","itemid":"19317","price":"0.00","cannotpay":"0","folderid":"29795","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"66","prank":"9","srank":"9"},{"pid":"1140","sid":"0","itemid":"19318","price":"0.00","cannotpay":"0","folderid":"29796","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"65","prank":"8","srank":"8"},{"pid":"1140","sid":"0","itemid":"19319","price":"0.00","cannotpay":"0","folderid":"29797","iname":"ff","summary":"fff","view_mode":"2","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"64","prank":"7","srank":"7"},{"pid":"1140","sid":"0","itemid":"19320","price":"0.00","cannotpay":"0","folderid":"29798","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"63","prank":"6","srank":"6"},{"pid":"1140","sid":"0","itemid":"19321","price":"0.00","cannotpay":"0","folderid":"29799","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"62","prank":"5","srank":"4"},{"pid":"1140","sid":"0","itemid":"19322","price":"0.00","cannotpay":"0","folderid":"29800","iname":"ff","summary":"fff","view_mode":"1","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"61","prank":"4","srank":"3"},{"pid":"1140","sid":"0","itemid":"19323","price":"0.00","cannotpay":"0","folderid":"29801","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"60","prank":"3","srank":"2"},{"pid":"1140","sid":"0","itemid":"19324","price":"0.00","cannotpay":"0","folderid":"29800","iname":"ff","summary":"fff","view_mode":"2","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"61","prank":"4","srank":"3"},{"pid":"1140","sid":"0","itemid":"19325","price":"0.00","cannotpay":"0","folderid":"29803","iname":"ff","summary":"fff","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"ff","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"sfasfa","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"59","prank":"1","srank":"1"},{"pid":"987","sid":"0","itemid":"19326","price":"0.00","cannotpay":"0","folderid":"29804","iname":"7.19ceshi","summary":"7.19ceshi","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"7.19ceshi","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"hu","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"58","prank":"2","srank":"2"},{"pid":"987","sid":"0","itemid":"19327","price":"0.00","cannotpay":"0","folderid":"29805","iname":"7.19ceshi","summary":"7.19ceshi","view_mode":"0","islimit":"0","limitnum":"0","imonth":"1","iday":"0","foldername":"7.19ceshi","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"hu","pname":"\u6346\u7ed1\u9500\u552e","pdisplayorder":"2","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"57","prank":"1","srank":"1"},{"pid":"1140","sid":"0","itemid":"19328","price":"0.00","cannotpay":"0","folderid":"29806","iname":"shi[ing","summary":"shipin","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"shi[ing","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"hu","pname":"\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba\u6c11\u670d\u52a11\u4e3a\u4eba","pdisplayorder":"1","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"188","prank":"2","srank":"5"},{"pid":"2142","sid":"0","itemid":"19329","price":"0.00","cannotpay":"0","folderid":"29807","iname":"1234556","summary":"123123123","view_mode":"0","islimit":"0","limitnum":"0","imonth":"2","iday":"0","foldername":"1234556","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"12312","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"56","prank":"2","srank":"2"},{"pid":"2142","sid":"0","itemid":"19332","price":"0.00","cannotpay":"0","folderid":"29810","iname":"\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5","summary":"\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5","view_mode":"0","islimit":"0","limitnum":"0","imonth":"0","iday":"1","foldername":"\u91cd\u65b0\u5f00\u8bbe\u7684\u8bfe\u7a0b\u6d4b\u8bd5","cover":"http:\/\/img.ebanhui.com\/aroomv3\/2017\/07\/24\/15008662298100_243_144.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u91cd\u8001\u5e08","pname":"\u5751\u4eba\u7684\u5305","pdisplayorder":"10","pcrid":"10622","located":"1","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"187","prank":"1","srank":"1"},{"pid":"372","sid":"0","itemid":"26488","price":"1.00","cannotpay":"0","folderid":"3957","iname":"\u5316\u5b66","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5316\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/2.jpg","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"55","prank":"55","srank":"55"},{"pid":"372","sid":"0","itemid":"26489","price":"24.00","cannotpay":"0","folderid":"3961","iname":"\u751f\u7269","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u751f\u7269","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/12\/23\/14824589913046_th.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"54","prank":"54","srank":"54"},{"pid":"372","sid":"0","itemid":"26490","price":"333.00","cannotpay":"0","folderid":"3962","iname":"\u6570\u5b66","summary":"\u8bfe\u7a0b\u4ecb\u7ecd\u4e0d\u80fd\u4e3a\u7a7a\u8bfe\u7a0b\u5206\u7c7b\u591c\u6708","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6253\u5305\u8bfe\u7a0b\u4e4b\u6570\u5b66","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/195_247_147.jpg","viewnum":"0","coursewarenum":"16","isschoolfree":"0","fcrid":"10622","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"53","prank":"53","srank":"53"},{"pid":"372","sid":"0","itemid":"26491","price":"1111.00","cannotpay":"0","folderid":"3964","iname":"\u82f1\u6587","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u82f1\u6587","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/65.jpg","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"52","prank":"52","srank":"52"},{"pid":"372","sid":"0","itemid":"26492","price":"0.00","cannotpay":"0","folderid":"4743","iname":"1","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"1","cover":"","viewnum":"10","coursewarenum":"13","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"51","prank":"51","srank":"51"},{"pid":"372","sid":"0","itemid":"26493","price":"0.00","cannotpay":"0","folderid":"4745","iname":"\u6ca1\u6709\u8bfe\u4ef6\u7684\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6ca1\u6709\u8bfe\u4ef6\u7684\u8bfe\u7a0b","cover":"http:\/\/img.ebanhui.com\/ebh\/2015\/04\/29\/1430291317_x.png","viewnum":"248","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"50","prank":"50","srank":"50"},{"pid":"372","sid":"0","itemid":"26494","price":"0.00","cannotpay":"0","folderid":"4746","iname":"\u6709\u8bfe\u4ef6\u7684\u8bfe\u7a0b\u4e5f\u6709\u4f5c\u4e1a","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6709\u8bfe\u4ef6\u7684\u8bfe\u7a0b\u4e5f\u6709\u4f5c\u4e1a","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/7.jpg","viewnum":"350","coursewarenum":"7","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"49","prank":"49","srank":"49"},{"pid":"372","sid":"0","itemid":"26495","price":"0.00","cannotpay":"0","folderid":"4747","iname":"\u514d\u8d39\u7684\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u514d\u8d39\u7684\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/2.jpg","viewnum":"1200","coursewarenum":"38","isschoolfree":"0","fcrid":"10622","showmode":"3","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"48","prank":"48","srank":"48"},{"pid":"372","sid":"0","itemid":"26496","price":"0.00","cannotpay":"0","folderid":"4748","iname":"NO 2","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"NO 2","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/1.jpg","viewnum":"1000","coursewarenum":"38","isschoolfree":"0","fcrid":"10622","showmode":"3","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"47","prank":"47","srank":"47"},{"pid":"372","sid":"0","itemid":"26497","price":"0.00","cannotpay":"0","folderid":"4749","iname":"\u661f\u661f\u70b9\u706f","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u661f\u661f\u70b9\u706f","cover":"","viewnum":"258","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"46","prank":"46","srank":"46"},{"pid":"372","sid":"0","itemid":"26498","price":"0.00","cannotpay":"0","folderid":"4751","iname":"11111111","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"11111111","cover":"","viewnum":"215","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"45","prank":"45","srank":"45"},{"pid":"372","sid":"0","itemid":"26499","price":"0.00","cannotpay":"0","folderid":"4753","iname":"\u7f51\u6821\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7f51\u6821\u8bfe\u7a0b","cover":"","viewnum":"228","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"44","prank":"44","srank":"44"},{"pid":"372","sid":"0","itemid":"26500","price":"0.00","cannotpay":"0","folderid":"4754","iname":"111111111111","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"111111111111","cover":"","viewnum":"225","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"43","prank":"43","srank":"43"},{"pid":"372","sid":"0","itemid":"26501","price":"0.00","cannotpay":"0","folderid":"4755","iname":"\u4f1a\u4e0d\u4f1a\u6709upid\u5462","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u4f1a\u4e0d\u4f1a\u6709upid\u5462","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/38.jpg","viewnum":"140","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"42","prank":"42","srank":"42"},{"pid":"372","sid":"0","itemid":"26502","price":"0.00","cannotpay":"0","folderid":"4756","iname":"\u7684\u8303\u5fb7\u8428","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7684\u8303\u5fb7\u8428","cover":"","viewnum":"127","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"41","prank":"41","srank":"41"},{"pid":"372","sid":"0","itemid":"26503","price":"0.00","cannotpay":"0","folderid":"4757","iname":"\u7b2c\u4e09\u65b9\u7b2c\u4e09\u65b9","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7b2c\u4e09\u65b9\u7b2c\u4e09\u65b9","cover":"","viewnum":"112","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"40","prank":"40","srank":"40"},{"pid":"372","sid":"0","itemid":"26504","price":"0.00","cannotpay":"0","folderid":"4758","iname":"\u5fc3\u7406\u8bfe","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5fc3\u7406\u8bfe","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/16.jpg","viewnum":"300","coursewarenum":"11","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"39","prank":"39","srank":"39"},{"pid":"372","sid":"0","itemid":"26505","price":"0.00","cannotpay":"0","folderid":"4759","iname":"1111111111111","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"1111111111111","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/1.jpg","viewnum":"175","coursewarenum":"5","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"38","prank":"38","srank":"38"},{"pid":"372","sid":"0","itemid":"26506","price":"0.00","cannotpay":"0","folderid":"4760","iname":"11111111111111","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"11111111111111","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/1.jpg","viewnum":"257","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"37","prank":"37","srank":"37"},{"pid":"372","sid":"0","itemid":"26507","price":"0.00","cannotpay":"0","folderid":"4761","iname":"\u7b80\u5355\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7b80\u5355\u8bfe\u7a0b","cover":"","viewnum":"266","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"36","prank":"36","srank":"36"},{"pid":"372","sid":"0","itemid":"26508","price":"0.00","cannotpay":"0","folderid":"4762","iname":"\u6d4b\u8bd5\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6d4b\u8bd5\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/guwen.jpg","viewnum":"134","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"35","prank":"35","srank":"35"},{"pid":"372","sid":"0","itemid":"26509","price":"0.00","cannotpay":"0","folderid":"4764","iname":"\u8981\u6709\u53cd\u9988\u7684\u8bfe\u7a0b\u54e6\u662f1","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u8981\u6709\u53cd\u9988\u7684\u8bfe\u7a0b\u54e6\u662f1","cover":"http:\/\/img.ebanhui.com\/ebh\/2014\/12\/30\/1419918098_x.jpg","viewnum":"218","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"34","prank":"34","srank":"34"},{"pid":"372","sid":"0","itemid":"26510","price":"0.00","cannotpay":"0","folderid":"4765","iname":"new","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"new","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"33","prank":"33","srank":"33"},{"pid":"372","sid":"0","itemid":"26511","price":"0.00","cannotpay":"0","folderid":"4766","iname":"test","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"test","cover":"http:\/\/img.ebanhui.com\/ebh\/2015\/01\/20\/1421720742_x.jpg","viewnum":"250","coursewarenum":"19","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"32","prank":"32","srank":"32"},{"pid":"372","sid":"0","itemid":"26512","price":"0.00","cannotpay":"0","folderid":"4767","iname":"\u591a\u7ea7\u76ee\u5f55","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u591a\u7ea7\u76ee\u5f55","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/81.jpg","viewnum":"133","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"31","prank":"31","srank":"31"},{"pid":"372","sid":"0","itemid":"26513","price":"0.00","cannotpay":"0","folderid":"4768","iname":"\u591a\u51e0\u591a\u7ea7","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u591a\u51e0\u591a\u7ea7","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/5.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"30","prank":"30","srank":"30"},{"pid":"372","sid":"0","itemid":"26514","price":"0.00","cannotpay":"0","folderid":"4769","iname":"xx","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"xx","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/12.jpg","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"29","prank":"29","srank":"29"},{"pid":"372","sid":"0","itemid":"26515","price":"0.00","cannotpay":"0","folderid":"4771","iname":"\u8d3e\u8001\u5e08\u5f00\u8bfe\u4e86","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u8d3e\u8001\u5e08\u5f00\u8bfe\u4e86","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/04\/26\/14616364929962.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"28","prank":"28","srank":"28"},{"pid":"372","sid":"0","itemid":"26516","price":"0.00","cannotpay":"0","folderid":"4776","iname":"\u4e2b\u4e2b\u5b66\u8bed","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u4e2b\u4e2b\u5b66\u8bed","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/2.jpg","viewnum":"0","coursewarenum":"6","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"27","prank":"27","srank":"27"},{"pid":"372","sid":"0","itemid":"26517","price":"0.00","cannotpay":"0","folderid":"4779","iname":"\u76f4\u63a5\u5220\u9664\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u76f4\u63a5\u5220\u9664\u8bfe\u7a0b","cover":"","viewnum":"650","coursewarenum":"9","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"26","prank":"26","srank":"26"},{"pid":"372","sid":"0","itemid":"26518","price":"0.00","cannotpay":"0","folderid":"4780","iname":"\u82b1","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u82b1","cover":"","viewnum":"8","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"25","prank":"25","srank":"25"},{"pid":"372","sid":"0","itemid":"26519","price":"0.00","cannotpay":"0","folderid":"4781","iname":"\u5b66\u5b66\u5b66\u5b66","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5b66\u5b66\u5b66\u5b66","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"24","prank":"24","srank":"24"},{"pid":"372","sid":"0","itemid":"26520","price":"0.00","cannotpay":"0","folderid":"4782","iname":"\u5b66\u5b66\u5b66\u5b661","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5b66\u5b66\u5b66\u5b661","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"23","prank":"23","srank":"23"},{"pid":"372","sid":"0","itemid":"26521","price":"0.00","cannotpay":"0","folderid":"4783","iname":"xuexuexuexue2","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"xuexuexuexue2","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"22","prank":"22","srank":"22"},{"pid":"372","sid":"0","itemid":"26522","price":"0.00","cannotpay":"0","folderid":"4784","iname":"\u5b66\u4e60\u8bfe\u7a0b\u4ecb\u7ecd","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5b66\u4e60\u8bfe\u7a0b\u4ecb\u7ecd","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"21","prank":"21","srank":"21"},{"pid":"372","sid":"0","itemid":"26523","price":"0.00","cannotpay":"0","folderid":"4785","iname":"1","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"1","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"20","prank":"20","srank":"20"},{"pid":"372","sid":"0","itemid":"26524","price":"0.00","cannotpay":"0","folderid":"4787","iname":"xxxxxxxx","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"xxxxxxxx","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"19","prank":"19","srank":"19"},{"pid":"372","sid":"0","itemid":"26526","price":"0.00","cannotpay":"0","folderid":"4789","iname":"\u5446\u54461","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5446\u54461","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"17","prank":"17","srank":"17"},{"pid":"372","sid":"0","itemid":"26527","price":"0.00","cannotpay":"0","folderid":"4790","iname":"\u5446\u54462","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5446\u54462","cover":"","viewnum":"100","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"16","prank":"16","srank":"16"},{"pid":"372","sid":"0","itemid":"26528","price":"0.00","cannotpay":"0","folderid":"4804","iname":"\u670d\u52a1\u5305,\u4e0d\u5f00\u901a\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u670d\u52a1\u5305,\u4e0d\u5f00\u901a\u8bfe\u7a0b","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"15","prank":"15","srank":"15"},{"pid":"372","sid":"0","itemid":"26529","price":"0.00","cannotpay":"0","folderid":"4811","iname":"\u6700\u65b0\u8bfe\u7a0b\u54e6","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u6700\u65b0\u8bfe\u7a0b\u54e6","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/03\/22\/14586393409664.jpg","viewnum":"45","coursewarenum":"25","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"14","prank":"14","srank":"14"},{"pid":"372","sid":"0","itemid":"26530","price":"0.00","cannotpay":"0","folderid":"4812","iname":"\u514d\u8d39","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u514d\u8d39","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"13","prank":"13","srank":"13"},{"pid":"372","sid":"0","itemid":"26531","price":"0.00","cannotpay":"0","folderid":"4821","iname":"\u666e\u901a\u5b66\u6821","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u666e\u901a\u5b66\u6821","cover":"","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"12","prank":"12","srank":"12"},{"pid":"372","sid":"0","itemid":"26532","price":"0.00","cannotpay":"0","folderid":"4830","iname":"\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u8bfe\u7a0b","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"11","prank":"11","srank":"11"},{"pid":"372","sid":"0","itemid":"26533","price":"0.00","cannotpay":"0","folderid":"4831","iname":"\u8bed\u6587\u4e0a","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u8bed\u6587\u4e0a","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"10","prank":"10","srank":"10"},{"pid":"372","sid":"0","itemid":"26534","price":"0.00","cannotpay":"0","folderid":"4838","iname":"=_=m","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"=_=m","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"9","prank":"9","srank":"9"},{"pid":"372","sid":"0","itemid":"26535","price":"0.00","cannotpay":"0","folderid":"4847","iname":"\u5b66\u6821\u5e73\u53f0\u7684\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u5b66\u6821\u5e73\u53f0\u7684\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/guwen.jpg","viewnum":"0","coursewarenum":"2","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"8","prank":"8","srank":"8"},{"pid":"372","sid":"0","itemid":"26536","price":"0.00","cannotpay":"0","folderid":"4851","iname":"\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41\u79df\u8d41","cover":"","viewnum":"0","coursewarenum":"3","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"7","prank":"7","srank":"7"},{"pid":"372","sid":"0","itemid":"26537","price":"1.00","cannotpay":"0","folderid":"4863","iname":"\u4e8c\u6b21","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u4e8c\u6b21","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"6","prank":"6","srank":"6"},{"pid":"372","sid":"0","itemid":"26538","price":"1.00","cannotpay":"0","folderid":"4864","iname":"\u4e8c\u6b21\u5143","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u4e8c\u6b21\u5143","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"5","prank":"5","srank":"5"},{"pid":"372","sid":"0","itemid":"26539","price":"0.00","cannotpay":"0","folderid":"4874","iname":"\u7206\u53d1\u5c0f\u5b87\u5b99","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u7206\u53d1\u5c0f\u5b87\u5b99","cover":"http:\/\/img.ebanhui.com\/ebh\/2017\/01\/07\/14837685103006_th.gif","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u8d3e\u8001\u5e08\u6069\u5443\u5443","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"4","prank":"4","srank":"4"},{"pid":"372","sid":"0","itemid":"26540","price":"0.00","cannotpay":"0","folderid":"4875","iname":"\u9ed8\u8ba4\u514d\u8d39\uff0c\u4f60\u5374\u9700\u8981\u652f\u4ed8","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u9ed8\u8ba4\u514d\u8d39\uff0c\u4f60\u5374\u9700\u8981\u652f\u4ed8","cover":"","viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"3","prank":"3","srank":"3"},{"pid":"372","sid":"0","itemid":"26541","price":"0.00","cannotpay":"0","folderid":"4885","iname":"\u79df\u8d41\u7684\u8bfe\u7a0b","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u79df\u8d41\u7684\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/guwen_247_147.jpg","viewnum":"0","coursewarenum":"7","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"2","prank":"2","srank":"2"},{"pid":"372","sid":"0","itemid":"26542","price":"0.00","cannotpay":"0","folderid":"4886","iname":"\u79df","summary":"","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"\u79df","cover":"","viewnum":"0","coursewarenum":"1","isschoolfree":"0","fcrid":"10622","showmode":"0","speaker":"","pname":"\u591c\u6708","pdisplayorder":"27","pcrid":"10622","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":"1","prank":"1","srank":"1"},{"pid":"5865","sid":"0","itemid":"26547","price":"100.00","cannotpay":"0","folderid":"3982","iname":"a111111","summary":"php\u9ad8\u7ea7","view_mode":"0","islimit":"0","limitnum":"0","imonth":"12","iday":"0","foldername":"php\u9ad8\u7ea7","cover":null,"viewnum":"0","coursewarenum":"0","isschoolfree":"0","fcrid":"10194","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"CCCC","pdisplayorder":"2","pcrid":"10641","located":"0","sname":null,"showbysort":null,"sdisplayorder":null,"grank":null,"prank":null,"srank":null},{"price":"2","pid":"389","sid":"0","itemid":"396","iprice":"100.00","folderid":"3974","iname":"\u9ad8\u4e2d\u7406\u5316\u751f\u7efc\u5408","summary":"\u9ad8\u4e2d\u7406\u5316\u751f\u7efc\u5408~\u8bfe\u7a0b\u4ecb\u7ecd","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u9ad8\u4e2d\u7406\u5316\u751f\u7efc\u5408","cover":null,"coursewarenum":"0","viewnum":"500","showmode":"3","speaker":"\u9ec4\u671d\u534e\u8001\u5e08","pname":"ceshi999","pdisplayorder":"4","sname":null,"sdisplayorder":null,"grank":"31","prank":"1","srank":"1"},{"price":"2","pid":"391","sid":"0","itemid":"399","iprice":"8.00","folderid":"3979","iname":"html\u57fa\u7840","summary":"html\u57fa\u7840","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"html\u57fa\u7840","cover":null,"coursewarenum":"6","viewnum":"0","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"php\u7f51\u7edc\u57f9\u8bad","pdisplayorder":"2","sname":null,"sdisplayorder":null,"grank":"29","prank":"2","srank":"2"},{"price":"2","pid":"391","sid":"0","itemid":"401","iprice":"100.00","folderid":"3981","iname":"php\u57fa\u7840","summary":"php\u57fa\u7840","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"php\u57fa\u7840","cover":null,"coursewarenum":"24","viewnum":"1000","showmode":"3","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"php\u7f51\u7edc\u57f9\u8bad","pdisplayorder":"2","sname":null,"sdisplayorder":null,"grank":"27","prank":"1","srank":"1"},{"price":"2","pid":"1013","sid":"4405","itemid":"404","iprice":"0.00","folderid":"3984","iname":"linux\u9ad8\u7ea7","summary":"linux\u9ad8\u7ea7","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"linux\u9ad8\u7ea7","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/9_247_147.jpg","coursewarenum":"3","viewnum":"0","showmode":"2","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"wap\u6709\u4e48\u670911","pdisplayorder":"18","sname":"123456","sdisplayorder":"0","grank":"26","prank":"4","srank":"3"},{"price":"2","pid":"995","sid":"0","itemid":"845","iprice":"99.00","folderid":"3924","iname":"\u795e\u884c\u672f","summary":"\u540c\u6b65\u7684\u795e\u884c\u672f","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u795e\u884c\u672f","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimg\/2.jpg","coursewarenum":"16","viewnum":"300","showmode":"3","speaker":"\u91d1\u8001\u5e08","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b\u827e\u4e1d\u51e1","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"23","prank":"4","srank":"5"},{"price":"2","pid":"989","sid":"0","itemid":"952","iprice":"0.00","folderid":"4578","iname":"\u514d\u8d39 or \u6536\u8d39\uff1f\uff1f\uff1f","summary":"fasfafasfasfa","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u514d\u8d39 or \u6536\u8d39\uff1f\uff1f\uff1f","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","coursewarenum":"19","viewnum":"0","showmode":"3","speaker":"\u963f\u53f8\u6cd5\u6240\u5206","pname":"\u672c\u6821\u514d\u8d39\u8bfe\u7a0b","pdisplayorder":"3","sname":null,"sdisplayorder":null,"grank":"24","prank":"5","srank":"4"},{"price":"2","pid":"1007","sid":"0","itemid":"983","iprice":"0.00","folderid":"4612","iname":"\u6d4b\u8bd5100\u4e2a\u8bfe\u4ef6\u5206\u9875","summary":"\u6d4b\u8bd5100\u4e2a\u8bfe\u4ef6\u5206\u9875","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u6d4b\u8bd5100\u4e2a\u8bfe\u4ef6\u5206\u9875","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/guwen_247_147.jpg","coursewarenum":"2","viewnum":"0","showmode":"2","speaker":"\u5927\u9ec4\u74dc","pname":"\u4e00\u7ea7\u5206\u7c7b","pdisplayorder":"12","sname":null,"sdisplayorder":null,"grank":"25","prank":"1","srank":"1"},{"price":"2","pid":"2143","sid":"3766","itemid":"18778","iprice":"500.00","folderid":"29080","iname":"\u56fd\u571f\u5b66\u4e60\u83b7\u53d6\u5b66\u5206\u89c6\u9891","summary":"\u56fd\u571f\u5b66\u4e60\u83b7\u53d6\u5b66\u5206\u89c6\u9891","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u56fd\u571f\u5b66\u4e60\u83b7\u53d6\u5b66\u5206\u89c6\u9891","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/93_247_147.jpg","coursewarenum":"23","viewnum":"1000","showmode":"2","speaker":"\u5927\u9ec4\u8001\u5e08","pname":"\u5927\u9ec4\u4e13\u5c5e\u89c6\u9891\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"1","sname":"M3U8\u5207\u7247\u89c6\u9891","sdisplayorder":"0","grank":"34","prank":"5","srank":"2"},{"price":"2","pid":"2143","sid":"3767","itemid":"19295","iprice":"10.00","folderid":"29773","iname":"\u5355\u4e2a\u8bfe\u4ef6\u8d2d\u4e70\u6d4b\u8bd5--\u9ec4","summary":"\u5355\u4e2a\u8bfe\u4ef6\u8d2d\u4e70\u6d4b\u8bd5--\u9ec4","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u5355\u4e2a\u8bfe\u4ef6\u8d2d\u4e70\u6d4b\u8bd5--\u9ec4","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","coursewarenum":"8","viewnum":"114","showmode":"2","speaker":"\u5927\u9ec4","pname":"\u5927\u9ec4\u4e13\u5c5e\u89c6\u9891\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"1","sname":"FLV\u4e13\u5c5e\u89c6\u9891","sdisplayorder":"0","grank":"32","prank":"4","srank":"1"},{"price":"10","pid":"2524","sid":"4175","itemid":"19296","iprice":"0.00","folderid":"29774","iname":"\u9f50\u5929\u5927\u5723\u00b7","summary":"\u897f\u5929\u53d6\u7ecf","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u9f50\u5929\u5927\u5723\u00b7","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/5_247_147.jpg","coursewarenum":"40","viewnum":"6000","showmode":"2","speaker":"\u5e38\u8001\u5e08","pname":"\u738b\u601d\u8d8a\uff08\u4e0d\u8981\u5220\uff09","pdisplayorder":"20","sname":"\u82b1\u679c\u5c71","sdisplayorder":"2","grank":"21","prank":"2","srank":"1"},{"price":"2","pid":"2143","sid":"3768","itemid":"19680","iprice":"0.00","folderid":"30252","iname":"\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????","summary":"\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u514d\u8d39\u8bfe\u7a0b0\u5143 \u77e5\u9053\u4e0d????","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/10_247_147.jpg","coursewarenum":"2","viewnum":"239","showmode":"2","speaker":"\u5927\u9ec4","pname":"\u5927\u9ec4\u4e13\u5c5e\u89c6\u9891\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"1","sname":"MP4\u539f\u521b\u89c6\u9891","sdisplayorder":"0","grank":"33","prank":"3","srank":"1"},{"price":"2","pid":"2143","sid":"3770","itemid":"19744","iprice":"1.00","folderid":"30358","iname":"word\u8bfe\u7a0b","summary":"word\u8bfe\u7a0b","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"word\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","coursewarenum":"4","viewnum":"0","showmode":"2","speaker":"\u9ec4\u9ec4","pname":"\u5927\u9ec4\u4e13\u5c5e\u89c6\u9891\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"1","sname":"\u975e\u89c6\u9891\u7c7b\u8bfe\u4ef6","sdisplayorder":"4","grank":"30","prank":"2","srank":"1"},{"price":"2","pid":"1013","sid":"0","itemid":"19746","iprice":"10.00","folderid":"30361","iname":"\u6536\u8d39\u8bfe\u7a0b--\u6d4b\u8bd5\u5355\u8bfe\u6536\u8d39","summary":"\u6536\u8d39\u8bfe\u7a0b--\u6d4b\u8bd5\u5355\u8bfe\u6536\u8d39","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"\u6536\u8d39\u8bfe\u7a0b--\u6d4b\u8bd5\u5355\u8bfe\u6536\u8d39","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/9_247_147.jpg","coursewarenum":"1","viewnum":"0","showmode":"2","speaker":"\u5927\u9ec4","pname":"wap\u6709\u4e48\u670911","pdisplayorder":"18","sname":null,"sdisplayorder":null,"grank":"18","prank":"3","srank":"1"},{"price":"2","pid":"2143","sid":"3766","itemid":"19749","iprice":"0.00","folderid":"30364","iname":"0000123\u8bfe\u7a0b","summary":"0000123\u8bfe\u7a0b","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"0000123\u8bfe\u7a0b","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","coursewarenum":"3","viewnum":"0","showmode":"2","speaker":"0000123\u8bfe\u7a0b","pname":"\u5927\u9ec4\u4e13\u5c5e\u89c6\u9891\u8bfe\u7a0b\u670d\u52a1\u5305","pdisplayorder":"1","sname":"M3U8\u5207\u7247\u89c6\u9891","sdisplayorder":"0","grank":"28","prank":"1","srank":"1"},{"price":"10","pid":"2524","sid":"0","itemid":"26546","iprice":"0.00","folderid":"30615","iname":"1111111111","summary":"11111111111","crid":"10194","crname":"\u4e2d\u5b66\u6f14\u793a\u5e73\u53f06","displayorder":"1","foldername":"1111111111","cover":"http:\/\/static.ebanhui.com\/ebh\/tpl\/default\/images\/folderimgs\/course_cover_default_247_147.jpg","coursewarenum":"0","viewnum":"0","showmode":"2","speaker":"1111111111111111","pname":"\u738b\u601d\u8d8a\uff08\u4e0d\u8981\u5220\uff09","pdisplayorder":"20","sname":null,"sdisplayorder":null,"grank":"19","prank":"1","srank":"1"},{"price":"0","pid":"113","sid":"0","itemid":"210","iprice":"100.00","folderid":"3311","iname":"\u4fe1\u606f\u6280\u672f","summary":"\u4fe1\u606f\u6280\u672f","crid":"10524","crname":"\u8d44\u6e90\u65b9\u7f51\u6821","displayorder":"600","foldername":"\u4fe1\u606f\u6280\u672f","cover":null,"coursewarenum":"0","viewnum":"137","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8d44\u6e90\u7f51\u6821\u81ea\u5df1\u7684\u670d\u52a1\u5305","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":null,"prank":null,"srank":null},{"price":"0","pid":"113","sid":"262","itemid":"211","iprice":"250.00","folderid":"3310","iname":"\u9ad8\u4e00\u6570\u5b66","summary":"\u9ad8\u4e00\u6570\u5b66","crid":"10524","crname":"\u8d44\u6e90\u65b9\u7f51\u6821","displayorder":"600","foldername":"\u9ad8\u4e00\u6570\u5b66","cover":null,"coursewarenum":"0","viewnum":"17","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8d44\u6e90\u7f51\u6821\u81ea\u5df1\u7684\u670d\u52a1\u5305","pdisplayorder":"0","sname":"\u8bed\u65871","sdisplayorder":"0","grank":null,"prank":null,"srank":null},{"price":"10","pid":"113","sid":"262","itemid":"212","iprice":"500.00","folderid":"3308","iname":"\u9ad8\u4e00\u82f1\u8bed","summary":"\u9ad8\u4e00\u82f1\u8bed","crid":"10524","crname":"\u8d44\u6e90\u65b9\u7f51\u6821","displayorder":"600","foldername":"\u9ad8\u4e00\u82f1\u8bed","cover":null,"coursewarenum":"4","viewnum":"210","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u8d44\u6e90\u7f51\u6821\u81ea\u5df1\u7684\u670d\u52a1\u5305","pdisplayorder":"0","sname":"\u8bed\u65871","sdisplayorder":"0","grank":null,"prank":null,"srank":null},{"price":"6","pid":"72","sid":"0","itemid":"88","iprice":"20.00","folderid":"3335","iname":"\u8bed\u6587\u6d4b\u8bd52","summary":"\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u997f","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u8bed\u6587\u6d4b\u8bd52","cover":null,"coursewarenum":"6","viewnum":"20","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u9ec4\u7684\u8bed\u6587","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"1","prank":"1","srank":"4"},{"price":"5","pid":"72","sid":"0","itemid":"90","iprice":"5.00","folderid":"3336","iname":"\u8bed\u6587\u6d4b\u8bd53","summary":"\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u9e45\u997f","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u8bed\u6587\u6d4b\u8bd53","cover":null,"coursewarenum":"0","viewnum":"0","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u9ec4\u7684\u8bed\u6587","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"2","prank":"2","srank":"3"},{"price":"4","pid":"72","sid":"0","itemid":"91","iprice":"5.00","folderid":"3337","iname":"\u8bed\u6587\u6d4b\u8bd54","summary":"\u5443\u5443\u5443\u5443\u5443\u5443\u9e45\u9e45\u9e45","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u8bed\u6587\u6d4b\u8bd54","cover":null,"coursewarenum":"0","viewnum":"0","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u9ec4\u7684\u8bed\u6587","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"3","prank":"3","srank":"2"},{"price":"3","pid":"72","sid":"0","itemid":"94","iprice":"20.00","folderid":"3348","iname":"\u8bed\u6587\u6d4b\u8bd5-\u6d4b\u8bd5\u62a5\u540d\u4eba\u6570\u7528","summary":"i\u5f00\u6237\u884ci\u5f00\u6237\u884ci\u5f00\u6237\u884ci\u5f00\u6237\u884ci\u5f00\u6237\u884ci\u5f00\u6237\u884ci\u5f00\u6237\u884c","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u8bed\u6587\u6d4b\u8bd5-\u6d4b\u8bd5\u62a5\u540d\u4eba\u6570\u7528","cover":null,"coursewarenum":"2","viewnum":"0","showmode":"0","speaker":"\u4e3b\u8bb2\u8001\u5e08","pname":"\u9ec4\u7684\u8bed\u6587","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"4","prank":"4","srank":"1"},{"price":"2","pid":"115","sid":"74","itemid":"243","iprice":"8.00","folderid":"3899","iname":"\u9ec4\u9ec4\u804aPHP","summary":"PHP\uff08\u5916\u6587\u540d:PHP: Hypertext Preprocessor\uff0c\u4e2d\u6587\u540d\uff1a\u201c\u8d85\u6587\u672c\u9884\u5904\u7406\u5668\u201d\uff09\u662f\u4e00\u79cd\u901a\u7528\u5f00\u6e90\u811a\u672c\u8bed\u8a00\u3002\u8bed\u6cd5\u5438\u6536\u4e86C\u8bed\u8a00\u3001Java\u548cPerl\u7684\u7279\u70b9\uff0c\u5229\u4e8e\u5b66\u4e60\uff0c\u4f7f\u7528\u5e7f\u6cdb\uff0c\u4e3b\u8981\u9002\u7528\u4e8eWeb\u5f00\u53d1\u9886\u57df\u3002PHP \u72ec\u7279\u7684\u8bed\u6cd5\u6df7\u5408\u4e86C\u3001Java\u3001Perl\u4ee5\u53caPHP\u81ea\u521b\u7684\u8bed\u6cd5\u3002\u5b83\u53ef\u4ee5\u6bd4CGI\u6216\u8005Perl\u66f4\u5feb\u901f\u5730\u6267\u884c\u52a8\u6001\u7f51\u9875\u3002\u7528PHP\u505a\u51fa\u7684\u52a8\u6001\u9875\u9762\u4e0e\u5176\u4ed6\u7684\u7f16\u7a0b\u8bed\u8a00\u76f8\u6bd4\uff0cPHP\u662f\u5c06\u7a0b\u5e8f\u5d4c\u5165\u5230HTML\uff08\u6807\u51c6\u901a\u7528\u6807\u8bb0\u8bed\u8a00\u4e0b\u7684\u4e00\u4e2a\u5e94\u7528\uff09\u6587\u6863\u4e2d\u53bb\u6267\u884c\uff0c\u6267\u884c\u6548\u7387\u6bd4\u5b8c\u5168\u751f\u6210HTML\u6807\u8bb0\u7684CGI\u8981\u9ad8\u8bb8\u591a\uff1bPHP\u8fd8\u53ef\u4ee5\u6267\u884c\u7f16\u8bd1\u540e\u4ee3\u7801\uff0c\u7f16\u8bd1\u53ef\u4ee5\u8fbe\u5230\u52a0\u5bc6\u548c\u4f18\u5316\u4ee3\u7801\u8fd0\u884c\uff0c\u4f7f\u4ee3\u7801\u8fd0\u884c\u66f4\u5feb\u3002","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u9ec4\u9ec4\u804aPHP","cover":"http:\/\/img.ebanhui.com\/ebh\/2016\/02\/19\/1455861000281.jpg","coursewarenum":"0","viewnum":"0","showmode":"0","speaker":"\u9ec4\u9ec4","pname":"\u9ec4\u9ec4\u7684\u670d\u52a1\u5305","pdisplayorder":"0","sname":"s1","sdisplayorder":"0","grank":"5","prank":"1","srank":"1"},{"price":"1","pid":"115","sid":"0","itemid":"264","iprice":"0.00","folderid":"3914","iname":"\u600e\u4e48\u641e\uff01","summary":"11111","crid":"10525","crname":"\u9ec4\u57d4\u519b\u8f83","displayorder":"600","foldername":"\u600e\u4e48\u641e\uff01","cover":null,"coursewarenum":"0","viewnum":"0","showmode":"0","speaker":"sb","pname":"\u9ec4\u9ec4\u7684\u670d\u52a1\u5305","pdisplayorder":"0","sname":null,"sdisplayorder":null,"grank":"6","prank":"2","srank":"1"}]', true);
        $cacheKey = 'plate-platform-'.$crid;
        $list = $this->redis->get($cacheKey);
        if (!$useCache || empty($list)) {
            //课程包
            $bundles = $this->model->getBundleList($crid, array(), null, true);
            if (!empty($bundles)) {
                //注入课程包服务项
                $bids = array_keys($bundles);
                $bundleItems = $this->model->getBundleItemList($bids);
                foreach ($bundleItems as $item) {
                    $bundles[$item['bid']]['items'][] = $item;
                }
                $bundles = array_filter($bundles, function ($bundle) {
                    return !empty($bundle['items']);
                });
                $bundles = array_values($bundles);
            }
            //本校服务项
            $schoolItems = $this->model->getSchoolCourseList($crid);
            if (!empty($schoolItems)) {
                $showbysorts = array_filter($schoolItems, function ($item) {
                    return !empty($item['showbysort']);
                });
                $schoolItems = array_diff_key($schoolItems, $showbysorts);
                $sorts = array();
                foreach ($showbysorts as $showbysort) {
                    $sid = $showbysort['sid'];
                    if (!isset($sorts[$sid])) {
                        $sorts[$sid] = array(
                            'sid' => $sid,
                            'pid' => $showbysort['pid'],
                            'sname' => $showbysort['sname'],
                            'pname' => $showbysort['pname'],
                            'located' => $showbysort['located'],
                            'pdisplayorder' => $showbysort['pdisplayorder'],
                            'sdisplayorder' => $showbysort['sdisplayorder'],
                            'pcrid' => $showbysort['pcrid'],
                            'cannotpay' => true,
                            'showbysort' => 1,
                            'items' => array()
                        );
                    }
                    $sorts[$sid]['cannotpay'] &= $showbysort['cannotpay'];
                    $sorts[$sid]['items'][] = $showbysort;
                }
                unset($showbysorts);
                if (!empty($sorts)) {
                    $sids = array_keys($sorts);
                    $sortDetails = $this->model->getSortDetails($sids);
                    array_walk($sorts, function (&$sort, $sid, $details) {
                        $sort['cannotpay'] = intval($sort['cannotpay']);
                        $details[$sid]['summary'] = strip_tags($details[$sid]['summary']);
                        $sort = array_merge($details[$sid], $sort);
                    }, $sortDetails);
                }
                $schoolItems = array_merge($sorts, $schoolItems);
                $schoolItems = array_values($schoolItems);
                unset($sorts);
            }
            //企业选课课程服务项
            $otherItems = $this->model->getOtherCourseList($crid);
            $list = array_merge($bundles, $schoolItems, $otherItems);
            $this->redis->set($cacheKey, $list);
        }
        if (empty($list)) {
            return array();
        }
        return $list;
    }

    /**
     * 服务组合并成单一项
     * @param array $item 服务项
     * @param string $rank 排序范围：grank-全局，prank-大类，srank-小类
     * @param int $orderType 排序方式
     * @param bool $saveChildren 是否保留子列表
     * @return bool
     */
    private function tagged(&$item, $orderType = 0, $saveChildren = false) {
        if (empty($item['items'])) {
            return false;
        }
        $item['viewnum'] = 0;
        $item['coursewarenum'] = 0;
        $item['haspower'] = true;
        $priceIndex = isset($item['price']) ? 'iprice' : 'price';
        $item[$priceIndex] = 0;
        $foldernames = array();
        $cannotpay = true;
        $subRanks = array();
        $imonths = $idays = array();
        $sort = in_array($orderType, array(1, 2)) ? SORT_DESC : SORT_ASC;
        foreach ($item['items'] as $index => $payitem) {
            $imonths[] = $payitem['imonth'];
            $idays[] = isset($payitem['iday']) ? $payitem['iday'] : 0;
            $foldernames[] = $payitem['foldername'];
            $viewnum = $this->redis->hget('folderviewnum', $payitem['folderid'], false);
            if (!empty($viewnum)) {
                $payitem['viewnum'] = $viewnum;
            }
            $item['viewnum'] += $payitem['viewnum'];
            $item['coursewarenum'] += $payitem['coursewarenum'];
            $item['haspower'] = $item['haspower'] && isset($this->userpermissions[$payitem['folderid']]);
            if (!empty($showbysort['isschoolfree']) && $this->isStudent) {
                //本校免费课程价格置0
                $item['items'][$index]['price'] = $payitem['price'] = 0;
            }
            $item[$priceIndex] += $payitem['price'];
            if (!empty($item['bid'])) {
                continue;
            }
            if ($orderType == self::ORDER_VIEWNUM_DESC) {
                $subRanks[0][] = $payitem['viewnum'];
            } else if ($orderType == self::ORDER_PRICE_DESC || $orderType == self::ORDER_PRICE_ASC) {
                $subRanks[0][] = $payitem['price'];
            } else if ($orderType == self::ORDER_SRANK_ASC) {
                $subRanks[0][] = isset($payitem['srank']) ? $payitem['srank'] : 4294967295;
            } else if ($orderType == self::ORDER_PRANK_ASC) {
                $subRanks[0][] = isset($payitem['prank']) ? $payitem['prank'] : 4294967295;
            } else {
                $subRanks[0][] = isset($payitem['grank']) ? $payitem['grank'] : 4294967295;
            }
            $subRanks[1][] = $payitem['itemid'];
            if (!empty($payitem['islimit']) && isset($this->itemReportCounts[$payitem['itemid']]) && $this->itemReportCounts[$payitem['itemid']]['c'] >= $payitem['limitnum']) {
                //课程限制报名并且报名人数达到限制数，课程禁止报名
                $item['items'][$index]['cannotpay'] = $payitem['cannotpay'] = 1;
            }
            $cannotpay &= !empty($payitem['cannotpay']);
            $item['speakers'][] = $payitem['speaker'];
        }
        if (!empty($item['showbysort'])) {
            //打包课程内部排序
            array_multisort($subRanks[0], $sort, SORT_NUMERIC,
                $subRanks[1], SORT_DESC, SORT_NUMERIC, $item['items']);
        }
        $imonths = max($imonths);
        if ($imonths > 0) {
            $item['imonth'] = $imonths;
        } else {
            $item['iday'] = max($idays);
        }
        $firstFolder = reset($item['items']);
        $item['folderid'] = $firstFolder['folderid'];
        $item['showmode'] = $firstFolder['showmode'];
        $item['haspower'] = intval($item['haspower']);
        $item['foldername'] = implode(',', $foldernames);
        if (empty($item['bid'])) {
            $item['cannotpay'] = intval($cannotpay);
            $item['speaker'] = $firstFolder['speaker'];
            $item['itemid'] = $firstFolder['itemid'];
        } else if (isset($item['islimit']) && isset($this->bundleReportCounts[$item['bid']]) && $this->bundleReportCounts[$item['bid']]['c'] >= $item['limitnum']){
            $item['cannotpay'] = 1;
        }
        unset($foldernames);
        if ($saveChildren) {
            $item['items'] = array_map(function($course) {
                return array(
                    'itemid' => $course['itemid'],
                    'price' => $course['price'],
                    'cannotpay' => $course['cannotpay'],
                    'iname' => $course['iname'],
                    'folderid' => $course['folderid'],
                    'foldername' => $course['foldername'],
                    'summary' => $course['summary'],
                    'imonth' => $course['imonth'],
                    'iday' => $course['iday'],
                    'cover' => $course['cover'],
                    'coursewarenum' => $course['coursewarenum'],
                    'viewnum' => $course['viewnum'],
                    'showmode' => $course['showmode'],
                    'speaker' => $course['speaker']
                );
            }, $item['items']);
        } else {
            unset($item['items']);
        }
        return true;
    }
    /**
     * 服务项分组
     * @param array $items 服务项
     * @param array $packages
     * @param int $column 列数:0-全屏，3, 4
     * @param int $pid 定位的pid,0定位全部
     * @return array
     */
    private function group($items, $packages, $column = 0, $pid = 0) {
        if ($column == 0) {
            //选课中心
            return array(
                array(
                    'services' => $items
                )
            );
        }
        //最大行数
        $maxRow = 20;
        if ($pid == 0) {
            //定位package全部，按package分组，最多返回[$maxRow]组并且最多显示[$maxRow]行，超过[$maxRow]组的每组一行数据，不足[$maxRow]组的可能显示多行数据但总数不能超[$maxRow]行，优先payitem全列的数据
            $keys = array_column($packages, 'pid');
            $group = array_combine($keys, array_map(function($package) {
                return array(
                    'pid' => $package['pid'],
                    'pname' => $package['pname'],
                    'services' => array()
                );
            }, $packages));
            //分组数据统计、行数统计
            $rowCounts = $counts = array_fill_keys($keys, 0);
            foreach ($items as $item) {
                $pid = $item['pid'];
                $group[$pid]['services'][] = $item;
                $rowCounts[$pid] = ceil(++$counts[$pid] / $column);
            }
            $rowCounts = array_sum($rowCounts);
            if ($rowCounts <= $maxRow) {
                //课程少于最大行数，无需筛选直接全部返回
                return array_values($group);
            }
            $groupCount = count($group);
            if ($groupCount > $maxRow) {
                $indexs = array_keys($packages);
                //大于[$maxRow]组选择[$maxRow]组数据,优先选择整列数据的分组
                $ranks = array_map(function($count) use($column) {
                    return $count >= $column ? 1 : 0;
                }, $counts);
                array_multisort($ranks, SORT_DESC, SORT_NUMERIC,
                    $indexs, SORT_ASC, SORT_NUMERIC, $keys);
                $keys = array_slice($keys, 0, $maxRow);
                $keys = array_flip($keys);
                $group = array_intersect_key($group, $keys);
                $counts = array_intersect_key($counts, $keys);
                unset($keys, $indexs, $ranks);
            }
            if ($groupCount == $maxRow) {
                array_walk($group, function(&$groupitem, $pid, $column) {
                    $groupitem['services'] = array_slice($groupitem['services'], 0, $column);
                }, $column);
                return array_values($group);
            }
            $rowCounts = array_map(function() {
                return 1;
            }, $counts);
            $step = 2;
            $largeGroup = array_filter($counts, function($c) use($column, $step) {
                return $c >= $column * $step;
            });
            while (!empty($largeGroup)) {
                $surplus = $maxRow - array_sum($rowCounts);
                $len = count($largeGroup);
                $largeGroup = array_slice($largeGroup, 0, $surplus, true);
                array_walk($rowCounts, function(&$rowCount, $idx, $counts) {
                    if (isset($counts[$idx])) {
                        $rowCount++;
                    }
                }, $largeGroup);
                if ($len >= $surplus) {
                    break;
                }
                $step++;
                $largeGroup = array_filter($largeGroup, function($c) use($column, $step) {
                    return $c >= $column * $step;
                });
            }
            $surplus = $maxRow - array_sum($rowCounts);
            if ($surplus > 0) {
                //行数不足处理
                $largeGroup = array_filter($counts, function($c) use($column) {
                    return $c > $column && ($c % $column) > 0;
                });
                $largeGroup = array_slice($largeGroup, 0, $surplus, true);
                array_walk($rowCounts, function(&$row, $idx, $largeGroup) {
                    if (isset($largeGroup[$idx])) {
                        $row++;
                    }
                }, $largeGroup);
            }
            $rowCounts = array_map(function($rowCount) use($column) {
                return $rowCount * $column;
            }, $rowCounts);
            array_walk($group, function(&$gitem, $idx, $rowCounts) {
                if (isset($rowCounts[$idx])) {
                    $gitem['services'] = array_slice($gitem['services'], 0, $rowCounts[$idx]);
                }
            }, $rowCounts);
            return array_values($group);
        }
        if ($column == 4) {
            //主页4列课程列表
            return array(
                array(
                    'services' => array_slice($items, 0, 4 * $maxRow)
                )
            );
        }
        //主页3列课程列表
        $group_big = array();//全列
        $group_normal = array();//三分之一列
        $group_small = array();//二分之一列
        $rows = 0;
        foreach ($items as $id => $course) {
            $viewMode = empty($course['view_mode']) ? 0 : intval($course['view_mode']);
            if ($viewMode == 2) {
                $last_group = end($group_big);
                if ($last_group === false || key($group_big) + 1 != $rows) {
                    $group_big[$rows++] = array($course);
                    continue;
                }
                $k = key($group_big);
                $group_big[$k][] = $course;
                continue;
            }
            if ($viewMode == 0) {
                $last_group = end($group_normal);
                if ($last_group === false || count($last_group) % 3 == 0 && key($group_normal) + 1 != $rows) {
                    $group_normal[$rows++] = array($course);
                    continue;
                }
                $k = key($group_normal);
                $group_normal[$k][] = $course;
                continue;
            }
            if ($viewMode == 1) {
                $last_group = end($group_small);
                if ($last_group === false || count($last_group) % 2 == 0 && key($group_small) + 1 != $rows) {
                    $group_small[$rows++] = array($course);
                    continue;
                }
                $k = key($group_small);
                $group_small[$k][] = $course;
                continue;
            }
        }
        $normal_rows = 0;
        $other_rows = 0;
        for ($i = 0; $i < $rows; $i++) {
            if ($normal_rows + $other_rows >= $maxRow) {
                break;
            }
            if (key_exists($i, $group_big)) {
                $tmp[] = array('view_mode' => 2, 'services' => $group_big[$i]);
                $other_rows += count($group_big[$i]);
                continue;
            }
            if (key_exists($i, $group_normal)) {
                $tmp[] = array('view_mode' => 0, 'services' => $group_normal[$i]);
                $normal_rows += ceil(count($group_normal[$i]) / 3);
                continue;
            }
            if (key_exists($i, $group_small)) {
                $tmp[] = array('view_mode' => 1, 'services' => $group_small[$i]);
                $other_rows += ceil(count($group_small[$i]) / 2);
                continue;
            }
        }
        if (!empty($tmp)) {
            return $tmp;
        }
    }
}
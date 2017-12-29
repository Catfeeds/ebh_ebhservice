<?php

/**
 * 学习相关服务接口
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/12/14
 * Time: 10:52
 */
class StudyServiceController extends Controller {
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
                )
            ),
            'teacherListAction' => array(
                'bid' => array(
                    'name' => 'bid',
                    'type' => 'int',
                    'default' => 0
                ),
                'itemid' => array(
                    'name' => 'itemid',
                    'type' => 'int',
                    'default' => 0
                )
            )
        );
    }

    /**
     * 课程列表接口：课程包、本校课程、企业选课课程，接口说明文档：http://doc.ebh.net/index.php?s=/8&page_id=464
     */
    public function indexAction() {
        $list = $this->getService($this->crid);
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
        $rank = 'grank';
        if ($this->sid !== null && $this->pid > 0) {
            $rank = 'srank';
        } else if ($this->pid > 0) {
            $rank = 'prank';
        }
        $bids = $itemids = array();
        foreach ($list as $item) {
            //读取报名限制的课程包ID与课程ID
            if (isset($item['bid'])) {
                if (!empty($item['islimit'])) {
                    $bids[] = $item['bid'];
                }
                continue;
            }
            if (!empty($item['items'])) {
                foreach ($item['items'] as $subitem) {
                    if (!empty($subitem['islimit'])) {
                        $itemids[] = $subitem['itemid'];
                    }
                }
                continue;
            }
            if (!empty($item['islimit'])) {
                $itemids[] = $item['itemid'];
            }
        }
        $this->bundleReportCounts = $this->model->reportCount($this->crid, $bids, $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        $this->itemReportCounts = $this->model->reportCount($this->crid, $itemids, $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        unset($bids, $itemids);
        array_walk($list, function(&$item, $index, $args) {
            if (!empty($item['items'])) {
                $this->tagged($item, $args['rank'], $args['orderType']);
                return;
            }
            $viewnum = $this->redis->hget('folderviewnum', $item['folderid'], false);
            if (!empty($viewnum)) {
                $item['viewnum'] = $viewnum;
            }
            $item['haspower'] = isset($this->userpermissions[$item['folderid']]) ? 1 : 0;
            if (empty($item['crid']) && !empty($item['isschoolfree']) && $this->isStudent) {
                //本校免费课程价格置0
                $item['price'] = 0;
            }
            if (!empty($item['islimit']) && isset($this->itemReportCounts[$item['itemid']]) && $this->itemReportCounts[$item['itemid']]['c'] >= $item['limitnum']) {
                //本校课程限制报名并且报名人数达到限制数，禁止课程报名
                $item['cannotpay'] = 1;
            }
        }, array(
            'orderType' => $orderType,
            'rank' => $rank
        ));
        if ($this->filterbuyed && $this->uid > 0) {
            //过滤已报名的服务项
            $list = array_filter($list, function($item) {
                return empty($item['haspower']);
            });
        }
        if (empty($list)) {
            return array();
        }
        //读取来源网校、服务包、服务包分类
        $classrooms = $packages = $sorts = array();
        //定位的PID
        $localPid = 0;
        //定位的SID
        $localSid = null;
        $hasOther = 0;
        unset($item);
        foreach ($list as $item) {
            if (isset($item['crid']) && !isset($classrooms[$item['crid']])) {
                $classrooms[$item['crid']] = array(
                    'crid' => $item['crid'],
                    'crname' => $item['crname'],
                    'subcount' => 0,
                    'displayorder' => $item['displayorder']
                );
            }
            if (!isset($packages[$item['pid']])) {
                if (isset($item['crid'])) {
                    $pcrid = $item['crid'];
                } else if (isset($item['pcrid']) && $item['pcrid'] != $this->crid) {
                    $pcrid = $item['pcrid'];
                } else {
                    $pcrid = 0;
                }
                $packages[$item['pid']] = array(
                    'pid' => $item['pid'],
                    'pname' => $item['pname'],
                    'subcount' => 0,
                    'displayorder' => $item['pdisplayorder'],
                    'crid' => $pcrid
                );
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
                    'sdisplayorder' => $item['sdisplayorder'],
                    'pid' => $item['pid'],
                    'crid' => $pcrid
                );
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
        }
        //排序classrooms
        $displayorders = array_column($classrooms, 'displayorder');
        $ids = array_keys($classrooms);
        array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
            $ids, SORT_DESC, SORT_NUMERIC, $classrooms);
        //排序packages：本校的package,本校item引用的package,企业选课的package
        $parentids = array_column($classrooms, 'crid');
        $parentids = array_flip($parentids);
        $displayorders = $ids = $porders = array();
        foreach ($packages as $id => $package) {
            $displayorders[] = $package['displayorder'];
            $ids[] = $id;
            if (isset($parentids[$package['crid']])) {
                $porders[] = $parentids[$package['crid']];
            } else if (!empty($package['crid'])) {
                $porders[] = -1;
            } else {
                $porders[] = -2;
            }
        }
        array_multisort($porders, SORT_ASC, SORT_NUMERIC,
            $displayorders, SORT_ASC, SORT_NUMERIC,
            $ids, SORT_DESC, SORT_NUMERIC, $packages);
        //排序sorts
        $displayorders = $ids = array();
        foreach ($sorts as $id => $sort) {
            $displayorders[] = $sort['sdisplayorder'];
            $ids[] = $id;
            unset($sorts[$id]['sdisplayorder']);
        }
        array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
            $ids, SORT_DESC, SORT_NUMERIC, $sorts);
        unset($ids, $displayorders, $parentids, $porders);
        if (in_array($this->column, array(3, 4))) {
            $firstPackage = reset($packages);
            if ($firstPackage['displayorder'] > 0) {
                $localPid = $this->pid = $firstPackage['pid'];
                $packages[0]['cur'] = 1;
            }
        }
        $q = trim($this->q);
        $isfree = $this->isfree;
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

        $groups = array();//分组优先级：课程包，打包课程，本校课程，企业选课程
        $ranks = array();//排序主参数
        $auxiliarys = array();//排序副参数
        $sort = in_array($orderType, array(1, 2)) ? SORT_DESC : SORT_ASC;
        //排序服务项
        foreach($list as &$item) {
            if (isset($item['bid'])) {
                //设置排序参数
                $groups[] = 0;
                if ($orderType == 1) {
                    $ranks[] = $item['viewnum'];
                } else if ($orderType == 2 || $orderType == 3) {
                    $ranks[] = $item['price'];
                } else {
                    $ranks[] = $item['bdisplayorder'];
                }
                $auxiliarys[] = $item['bid'];
                unset($item['bdisplayorder']);
                continue;
            }
            if (!empty($item['showbysort'])) {
                $groups[] = 1;
                if ($orderType == 1) {
                    $ranks[] = $item['viewnum'];
                } else if ($orderType == 2 || $orderType == 3) {
                    $ranks[] = $item['price'];
                } else {
                    $ranks[] = empty($item['pcrid']) ? $item['sdisplayorder'] : 2147483647;
                }
                $auxiliarys[] = $item['sid'];
                unset($item['sdisplayorder'], $item['pcrid']);
                continue;
            }
            $groups[] = empty($item['crid']) ? 2 : 3;
            if ($orderType == 1) {
                $ranks[] = $item['viewnum'];
            } else if ($orderType == 2 || $orderType == 3) {
                $ranks[] = $item['price'];
            } else if ($this->pid > 0 && $this->sid !== null) {
                $ranks[] = $item['srank'];
            } else if ($this->pid > 0) {
                $ranks[] = $item['prank'];
            } else {
                $ranks[] = $item['grank'];
            }
            $auxiliarys[] = $item['itemid'];
            unset($item['displayorder'], $item['pdisplayorder'], $item['sdisplayorder'], $item['pcrid'], $item['grank'], $item['prank'], $item['srank']);
        }
        $count = count($list);
        array_multisort($groups, SORT_ASC, SORT_NUMERIC,
            $ranks, $sort, SORT_NUMERIC,
            $auxiliarys, SORT_DESC, SORT_NUMERIC, $list);
        unset($groups, $ranks, $auxiliarys);
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
        array_walk($list, function(&$item) {
            $this->tagged($item, 'grank', 0);
        });
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
        foreach ($list as $item) {
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
        $bids = $itemids = array();
        foreach ($list as $item) {
            //读取报名限制的课程包ID与课程ID
            if (isset($item['bid'])) {
                if (!empty($item['islimit'])) {
                    $bids[] = $item['bid'];
                }
                continue;
            }
            if (!empty($item['items'])) {
                foreach ($item['items'] as $subitem) {
                    if (!empty($subitem['islimit'])) {
                        $itemids[] = $subitem['itemid'];
                    }
                }
                continue;
            }
            if (!empty($item['islimit'])) {
                $itemids[] = $item['itemid'];
            }
        }
        $this->bundleReportCounts = $this->model->reportCount($this->crid, $bids, $this->uid, StudyServiceModel::SERVICE_TYPE_BUNDLE);
        $this->itemReportCounts = $this->model->reportCount($this->crid, $itemids, $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        unset($bids, $itemids);
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        //排序结果：课程包第一组，打包课程第二组，零售课程第三组
        //组内排序：排序号升序、ID降序
        $rankTypes = $ranks = $ids = array();
        foreach ($list as &$item) {
            $this->tagged($item, 'srank');
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
            return array();
        }
        $folderid = max(0, $this->folderid);
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
        }
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
        $this->tagged($bundle, 'grank', 0, true);
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
        $sort['content'] = strip_tags($sort['content']);
        $now = SYSTIME - 86400;
        $this->userpermissions = array_filter($this->userpermissions, function($userpermission) use($now) {
            return $userpermission['enddate'] > $now;
        });
        $itemids = array_column($limitItems, 'itemid');
        $this->itemReportCounts = $this->model->reportCount($this->crid, $itemids, $this->uid, StudyServiceModel::SERVICE_TYPE_COURSE);
        $this->tagged($sort, 'grank', 0, true);
        return $sort;
    }

    /**
     * 全校课程服务列表
     * @param int $crid 网校ID
     * @param bool $useCache 使用缓存
     * @return array
     */
    private function getService($crid, $useCache = true) {
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
    private function tagged(&$item, $rank = 'grank', $orderType = 0, $saveChildren = false) {
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
        $sort = in_array($orderType, array(1, 2)) ? SORT_DESC : SORT_ASC;
        foreach ($item['items'] as &$payitem) {
            $foldernames[] = $payitem['foldername'];
            $viewnum = $this->redis->hget('folderviewnum', $payitem['folderid'], false);
            if (!empty($viewnum)) {
                $payitem['viewnum'] = $viewnum;
            }
            if ($orderType == 1) {
                $subRanks[0][] = $payitem['viewnum'];
            } else if ($orderType == 2 || $orderType == 3) {
                $subRanks[0][] = $payitem['price'];
            } else {
                $subRanks[0][] = $payitem[$rank];
            }
            $subRanks[1][] = $payitem['itemid'];
            $item['viewnum'] += $payitem['viewnum'];
            $item['coursewarenum'] += $payitem['coursewarenum'];
            $item['haspower'] = $item['haspower'] && isset($this->userpermissions[$payitem['folderid']]);
            if (!empty($showbysort['isschoolfree']) && $this->isStudent) {
                //本校免费课程价格置0
                $payitem['price'] = 0;
            }
            $item[$priceIndex] += $payitem['price'];
            if (!empty($item['bid'])) {
                continue;
            }
            if (!empty($payitem['islimit']) && isset($this->itemReportCounts[$payitem['itemid']]) && $this->itemReportCounts[$payitem['itemid']]['c'] >= $payitem['limitnum']) {
                //课程限制报名并且报名人数达到限制数，课程禁止报名
                $payitem['cannotpay'] = 1;
            }
            $cannotpay &= !empty($payitem['cannotpay']);
            if (empty($item['bid'])) {
                $item['speakers'][] = $payitem['speaker'];
            }
        }
        array_multisort($subRanks[0], $sort, SORT_NUMERIC,
            $subRanks[1], SORT_DESC, SORT_NUMERIC, $item['items']);
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
        $items = $item['items'];
        unset($item['items'], $foldernames);
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
            }, $items);
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
        if ($pid == 0) {
            //定位package全部，按package分组，每组一行数据，最多返回20组，优先payitem全列的数据
            $keys = array_column($packages, 'pid');
            $indexs = array_keys($packages);
            $values = array_map(function($package, $index) {
                return array(
                    'pid' => $package['pid'],
                    'pname' => $package['pname'],
                    'index' => $index,
                    'count' => 0,
                    'services' => array()
                );
            }, $packages, $indexs);
            $group = array_combine($keys, $values);
            unset($keys, $values, $indexs);
            foreach ($items as $item) {
                $pid = $item['pid'];
                if ($group[$pid]['count'] < $column) {
                    $group[$pid]['count']++;
                    $group[$pid]['services'][] = $item;
                    continue;
                }
            }
            $columns = $indexs = array();
            foreach ($group as &$item) {
                $columns[] = $item['count'] == $column ? 1 : 0;
                $indexs[] = $item['index'];
                unset($item['count'], $item['index']);
            }
            array_multisort($columns, SORT_DESC, SORT_NUMERIC,
                $indexs, SORT_ASC, SORT_NUMERIC, $group);
            return array_slice($group, 0, 20);
        }
        if ($column == 4) {
            //主页4列课程列表
            return array(
                array(
                    'services' => array_slice($items, 0, 80)
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
            if ($normal_rows + $other_rows >= 20) {
                break;
            }
            if (key_exists($i, $group_big)) {
                $tmp[] = array('view_mode' => 2, 'services' => $group_big[$i]);
                $other_rows++;
                continue;
            }
            if (key_exists($i, $group_normal)) {
                $tmp[] = array('view_mode' => 0, 'services' => $group_normal[$i]);
                $normal_rows++;
                continue;
            }
            if (key_exists($i, $group_small)) {
                $tmp[] = array('view_mode' => 1, 'services' => $group_small[$i]);
                $other_rows++;
                continue;
            }
        }
        if (!empty($tmp)) {
            return $tmp;
        }
    }
}
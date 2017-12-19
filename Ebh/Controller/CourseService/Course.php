<?php

/**
 * 课程服务
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/8/29
 * Time: 17:32
 */
class CourseController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //课程主页
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'default' => 0
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
                'free' => array(
                    'name' => 'free',
                    'type' => 'int'
                ),
                'otype' => array(
                    'name' => 'otype',
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
                    'default' => 20
                )
            ),
            //打包分类详情
            'sortAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //课程包分类下课程集
            'sortCourseListAction' => array(
                'sid' => array(
                    'name' => 'sid',
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
            //课程包列表
            'courseListAction' => array(
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
                'isfree' => array(
                    'name' => 'isfree',
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
                'otype' => array(
                    'name' => 'otype',
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
                ),
                //课程列表列数：0-选课中心,3-主页3列，4-主页4列
                'column' => array(
                    'name' => 'column',
                    'type' => 'int',
                    'default' => 0
                ),

            ),
            //教师授课列表
            'teacherCourseListAction'=>array(
                'pid'=>array(
                    'name'=>'pid',
                    'type'=>'int',

                ),
                'sid'=>array(
                    'name'=>'sid',
                    'type'=>'int'
                ),
                'p'=>array(
                    'name'=>'p',
                    'type'=>'int'
                ),
                'listRows'=>array(
                    'name'=>'listRows',
                    'type'=>'int'
                ),
                'orderBy'=>array(
                    'name'=>'orderBy',
                    'type'=>'int'
                ),
                'uid'=>array(
                    'name'=>'uid',
                    'type'=>'int',
                    'require'=>true
                ),
                'crid'=>array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true
                ),

            ),
            //教师授课学生排序
            'getCreditSoreAction'=>array(
                'folderid'=>array(
                    'name'=>'folderid',
                    'type'=>'int',
                    'require'=>true
                    ),
                'orderBy'=>array(
                    'name'=>'orderBy',
                    'type'=>'int'
                )
            ,
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require'=>true
                ),
                'school_type'=>array(
                    'name'=>'school_type',
                    'type'=>'int',
                    'reqire'=>true
                )

            ),
            //教师授课文件统计
            'getFileCountAction'=>array(
                'folderid'=>array(
                    'name'=>'folderid',
                    'type'=>'int',
                    'require'=>true
                ),

            ),
            'getCourseClassAction'=>array(
                'uid'=>array(
                    'name'=>'uid',
                    'type'=>'int',
                    'require'=>true
                ),
                'crid'=>array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true
                )
            )
        );
    }

    /**
     * 课程主页,内容：普通课程，打包课程，企业选课课程
     */
    public function index2Action() {
        $redis = Ebh()->cache;
        $roomUserModel = new RoomUserModel();
        //是否本校学生
        $isStudent = false;
        $userpermisionModel = new UserpermisionsModel();
        //课程权限
        $userpermissions = array();
        if ($this->uid > 0) {
            $isStudent = $roomUserModel->isAlumni($this->crid, $this->uid);
            $userpermissions = $userpermisionModel->checkPermission(array(), $this->uid, $this->crid);
        }
        $courseServiceModel = new CourseServiceModel();
        $partOne = $courseServiceModel->getRoomBundleList($this->crid);
        $pids = $sids = array();
        if (!empty($partOne)) {
            //课程包处理
            $bids = array_keys($partOne);
            $pids = array_column($partOne, 'pid');
            $bundleItems = $courseServiceModel->getBundleFolderList($bids);
            foreach ($bundleItems as $bundleItem) {
                $bid = $bundleItem['bid'];
                if (!isset($partOne[$bid]['viewnum'])) {
                    $partOne[$bid]['viewnum'] = 0;
                    $partOne[$bid]['coursewarenum'] = 0;
                    $partOne[$bid]['folderid'] = $bundleItem['folderid'];
                    $partOne[$bid]['showmode'] = $bundleItem['showmode'];
                    $partOne[$bid]['hasPower'] = in_array($bundleItem['folderid'], $userpermissions);
                    $partOne[$bid]['cannotpay'] = !empty($bundleItem['cannotpay']);
                }
                $viewnum = $redis->hget('folderviewnum', $bundleItem['folderid'], false);
                $partOne[$bid]['viewnum'] += !empty($viewnum) ? $viewnum : $bundleItem['viewnum'];
                $partOne[$bid]['coursewarenum'] += $bundleItem['coursewarenum'];
                $partOne[$bid]['hasPower'] = $partOne[$bid]['hasPower'] && in_array($bundleItem['folderid'], $userpermissions);
                $partOne[$bid]['cannotpay'] = $partOne[$bid]['cannotpay'] && !empty($bundleItem['cannotpay']);
                $partOne[$bid]['foldername'][] = $bundleItem['foldername'];
            }
            unset($bundleItems);
            array_walk($partOne, function(&$course) {
               $course['foldername'] = array_unique($course['foldername']);
               $course['foldername'] = implode(',', $course['foldername']);
               $course['t'] = 2;
            });
        }
        $partTwo = $courseServiceModel->getRoomCourseList($this->crid);
        if (!empty($partTwo)) {
            array_walk($partTwo, function(&$course, $k, $args) {
                $course['t'] = !empty($course['showbysort']) ? 1 : 0;
                $viewnum = $args['cache']->hget('folderviewnum', $course['folderid'], false);
                if (!empty($viewnum)) {
                    $course['viewnum'] = $viewnum;
                }
                $course['hasPower'] = in_array($course['folderid'], $args['userpermissions']);
                if (empty($args['isStudent'])) {
                    return;
                }
                if (!empty($course['isschoolfree'])) {
                    $course['iprice'] = 0;
                }
            }, array(
                'isStudent' => $isStudent,
                'cache' => $redis,
                'userpermissions' => $userpermissions
            ));

            //合并打包课程
            $tagged = array_filter($partTwo, function($course) {
                return !empty($course['showbysort']);
            });
            $partTwo = array_diff_key($partTwo, $tagged);
            foreach ($tagged as $item) {
                $sid = $item['sid'];
                $sorts[$sid][] = array(
                    'id' => $item['id'],
					'sid' => $item['sid'],
                    'folderid' => $item['folderid'],
                    'foldername' => $item['foldername'],
                    'iprice' => $item['iprice'],
                    'viewnum' => $item['viewnum'],
                    'coursewarenum' => $item['coursewarenum'],
                    'img' => $item['img'],
                    'speaker' => $item['speaker'],
                    'srank' => intval($item['srank']),
                    'hasPower' => $item['hasPower'],
                    'showmode' => $item['showmode'],
                    'cannotpay' => $item['cannotpay']
                );
            }
            unset($tagged);
            if (!empty($sorts)) {
                $sid = array_keys($sorts);
                $taggeds = $courseServiceModel->getTaggedList($sid);
                array_walk($taggeds, function(&$tagged, $k, $items) {
                    //服务项排序
                    $hasPower = true;
                    $cannotpay = true;
                    $foldername = array();
                    foreach ($items[$k] as $item) {
                        $prices[] = $item['iprice'];
                        $viewnums[] = $item['viewnum'];
                        $coursewares[] = $item['coursewarenum'];
                        $sranks[] = $item['srank'];
                        $itemids[] = $item['id'];
                        $hasPower = $hasPower && !empty($item['hasPower']);
                        $cannotpay = $cannotpay && !empty($item['cannotpay']);
                        $foldername[] = $item['foldername'];
                    }
                    array_multisort($sranks, SORT_ASC, SORT_NUMERIC,
                        $itemids, SORT_DESC, SORT_NUMERIC, $items[$k]);
                    $firstItem = reset($items[$k]);
                    $tagged['iprice'] = array_sum($prices);
                    $tagged['viewnum'] = array_sum($viewnums);
                    $tagged['coursewarenum'] = array_sum($coursewares);
                    if (empty($tagged['showaslongblock']) || empty($tagged['img'])) {
                        $tagged['img'] = $firstItem['img'];
                    }
                    $tagged['speaker'] = $firstItem['speaker'];
                    $tagged['hasPower'] = $hasPower;
                    $tagged['folderid'] = $firstItem['folderid'];
                    $tagged['itemid'] = $firstItem['id'];
                    $tagged['showmode'] = $firstItem['showmode'];
                    $tagged['cannotpay'] = $cannotpay;
					$tagged['sid'] = $firstItem['sid'];
                    $tagged['summary'] = strip_tags($tagged['summary']);
                    $foldername = array_unique($foldername);
                    $tagged['foldername'] = implode(',', $foldername);
                    $tagged['t'] = 1;
                    unset($prices, $viewnums, $coursewares, $sranks, $itemids, $tagged['showaslongblock']);
                }, $sorts);
                unset($sorts);
                $partTwo = array_merge($taggeds, $partTwo);
                unset($taggeds);
            }
            $tmpPids = array_column($partTwo, 'pid');
            $pids = array_merge($pids, $tmpPids);
            unset($tmpPids);
        }
        $partThree = $courseServiceModel->getOtherCourseList($this->crid);
        array_walk($partThree, function(&$course, $k, $args) {
            //将企业选课的cannotpay置为0
            $course['cannotpay'] = 0;
            $course['t'] = 0;
            $viewnum = $args['cache']->hget('folderviewnum', $course['folderid'], false);
            if (!empty($viewnum)) {
                $course['viewnum'] = $viewnum;
            }
            $course['hasPower'] = in_array($course['folderid'], $args['userpermissions']);
        }, array(
            'cache' => $redis,
            'userpermissions' => $userpermissions
        ));
        if (!empty($partThree)) {
            $tmpPids = array_column($partThree, 'pid');
            $pids = array_merge($pids, $tmpPids);
            unset($tmpPids);
        }
        //return $partThree;
        /*$partOne = $partTwo = array();*/
        //查询
        if ($this->pid > 0) {
            $tmp = array();
            foreach ($partOne as $v) {
                if ($v['pid'] == $this->pid) {
                    $tmp[] = $v;
                }
            }
            $partOne = $tmp;
            $tmp = array();
            foreach ($partTwo as $v) {
                if ($v['pid'] == $this->pid) {
                    $tmp[] = $v;
                }
            }
            $partTwo = $tmp;
            $tmp = array();
            foreach ($partThree as $v) {
                if ($v['pid'] == $this->pid) {
                    $tmp[] = $v;
                }
            }
            $partThree = $tmp;
            $sid1 = array_column($partOne, 'sid');
            $sid2 = array_column($partTwo, 'sid');
            $sid3 = array_column($partThree, 'sid');
            $sids = array_merge($sid1, $sid2, $sid3);
            unset($sid1, $sid2, $sid3);
            if ($this->sid !== null) {
                $tmp = array();
                foreach ($partOne as $v) {
                    if ($v['sid'] == $this->sid) {
                        $tmp[] = $v;
                    }
                }
                $partOne = $tmp;
                $tmp = array();
                foreach ($partTwo as $v) {
                    if ($v['sid'] == $this->sid) {
                        $tmp[] = $v;
                    }
                }
                $partTwo = $tmp;
                $tmp = array();
                foreach ($partThree as $v) {
                    if ($v['sid'] == $this->sid) {
                        $tmp[] = $v;
                    }
                }
                $partThree = $tmp;
            }
        }
        if (!empty($this->free)) {
            $partOne = array_filter($partOne, function($course) {
                return $course['iprice'] == 0;
            });
            $partTwo = array_filter($partTwo, function($course) {
                return $course['iprice'] == 0;
            });
            $partThree = array_filter($partThree, function($course) {
                return $course['iprice'] == 0;
            });
        }
        if (!empty($this->k)) {
            $tmp = array();
            foreach ($partOne as $v) {
                if (stripos($v['iname'], $this->k) !== false) {
                    $tmp[] = $v;
                    continue;
                }
                if (stripos($v['foldername'], $this->k) !== false) {
                    $tmp[] = $v;
                }
            }
            $partOne = $tmp;
            $tmp = array();
            foreach ($partTwo as $v) {
                if (stripos($v['iname'], $this->k) !== false) {
                    $tmp[] = $v;
                    continue;
                }
                if (stripos($v['foldername'], $this->k) !== false) {
                    $tmp[] = $v;
                }
            }
            $partTwo = $tmp;
            $tmp = array();
            foreach ($partThree as $v) {
                if (stripos($v['iname'], $this->k) !== false) {
                    $tmp[] = $v;
                    continue;
                }
                if (stripos($v['foldername'], $this->k) !== false) {
                    $tmp[] = $v;
                }
            }
            $partThree = $tmp;
        }
        //排序,0-默认排序[排序号、时间]，1-热度降序，2-价格降序，3-价格升序
        if ($this->otype == 0) {
            if (!empty($partOne)) {
                $bids = $displayorders = array();
                foreach ($partOne as $k => $v) {
                    $bids[] = $v['id'];
                    $displayorders[] = $v['displayorder'];
                }
                array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
                    $bids, SORT_DESC, SORT_NUMERIC, $partOne);
                unset($bids, $displayorders);
            }

            if (!empty($partTwo)) {
                $taggs = $ids = $ranks = array();
                foreach ($partTwo as $k => $v) {
                    $taggs[] = !empty($v['t']) ? intval($v['t']) : 0;
                    $ids[] = $v['id'];
                    if ($this->pid > 0 && $this->sid !== null) {
                        $ranks[] = isset($v['srank']) ? intval($v['srank']) : 0;
                        continue;
                    }
                    if ($this->pid > 0) {
                        $ranks[] = isset($v['prank']) ? intval($v['prank']) : 0;
                        continue;
                    }
                    $ranks[] = isset($v['grank']) ? intval($v['grank']) : 0;
                }
                array_multisort($taggs, SORT_DESC, SORT_NUMERIC,
                    $ranks, SORT_ASC, SORT_NUMERIC,
                    $ids, SORT_DESC, SORT_NUMERIC, $partTwo);
                unset($taggs, $ranks, $ids);
            }

            if (!empty($partThree)) {
                $ranks = array();
                foreach ($partThree as $k => $v) {
                    $sourceids[] = $v['sourcecrid'];
                    $ids[] = $v['id'];
                    if ($this->pid > 0 && $this->sid !== null) {
                        $ranks[] = isset($v['srank']) ? intval($v['srank']) : 0;
                        continue;
                    }
                    if ($this->pid > 0) {
                        $ranks[] = isset($v['prank']) ? intval($v['prank']) : 0;
                        continue;
                    }
                    $ranks[] = isset($v['grank']) ? intval($v['grank']) : 0;
                }
                array_multisort($sourceids, SORT_DESC, SORT_NUMERIC,
                    $ranks, SORT_ASC, SORT_NUMERIC, $ranks,
                    $ids, SORT_DESC, SORT_NUMERIC, $partThree);
                unset($sourceids, $ids);
            }
        }
        if ($this->otype == 1) {
            $bids = $viewnum = array();
            foreach ($partOne as $k => $v) {
                $bids[] = $v['id'];
                $viewnum[] = $v['viewnum'];
            }
            if (!empty($bids)) {
                array_multisort($viewnum, SORT_DESC, SORT_NUMERIC,
                    $bids, SORT_DESC, SORT_NUMERIC, $partOne);
            }
            $taggs = $ids = $viewnum = array();
            foreach ($partTwo as $k => $v) {
                $viewnum[] = $v['viewnum'];
                $ids[] = $v['id'];
                $taggs[] = empty($v['t']) ? 0 : intval($v['t']);
            }
            if (!empty($ids)) {
                array_multisort($taggs, SORT_DESC, SORT_NUMERIC,
                    $viewnum, SORT_DESC, SORT_NUMERIC,
                    $ids, SORT_DESC, SORT_NUMERIC, $partTwo);
                unset($taggs, $ranks, $ids);
            }
            $sourceids = $ids = $viewnum = array();
            foreach ($partThree as $k => $v) {
                $sourceids[] = $v['sourcecrid'];
                $ids[] = $v['id'];
                $viewnum[] = $v['viewnum'];
            }
            if (!empty($sourceids)) {
                array_multisort($sourceids, SORT_DESC, SORT_NUMERIC,
                    $viewnum, SORT_DESC, SORT_NUMERIC,
                    $ids, SORT_DESC, SORT_NUMERIC, $partThree);
            }
        }
        if ($this->otype == 2 || $this->otype == 3) {
            $priceOrderType = $this->otype == 2 ? SORT_DESC : SORT_ASC;
            if (!empty($partOne)) {
                $bid = $price = array();
                foreach ($partOne as $item) {
                    $bid[] = $item['id'];
                    $iprice[] = $item['iprice'];
                }
                array_multisort($iprice, $priceOrderType, SORT_NUMERIC,
                    $bid, SORT_DESC, SORT_NUMERIC, $partOne);
            }
            if (!empty($partTwo)) {
                $taggs = $ids = $prices = array();
                foreach ($partTwo as $item) {
                    $taggs[] = isset($item['t']) ? intval($item['t']) : 0;
                    $ids[] = $item['id'];
                    $prices[] = $item['iprice'];
                }
                array_multisort($taggs, SORT_DESC, SORT_NUMERIC,
                    $prices, $priceOrderType, SORT_NUMERIC,
                    $ids, SORT_DESC, SORT_NUMERIC, $partTwo);
            }
        }

        $courses = array_merge($partOne, $partTwo, $partThree);
        unset($partOne, $partTwo, $partThree);
        $pids = array_unique($pids);
        $packages = $sorts = array();
        if (!empty($pids)) {
            $packageModel = new PaypackageModel();
            $packages = $packageModel->getPackageMenus($pids, $this->crid);
        }
        if (!empty($sids)) {
            $sids = array_unique($sids);
            $scount = count($sids);
            $sids = array_filter($sids, function($sid) {
               return $sid > 0;
            });
            $hasOther = count($sids) < $scount;
            $sorts = array();
            if (!empty($sids)) {
                $sortModel = new PaysortModel();
                $sorts = $sortModel->getSortList(array('sid' => $sids));
            }
            if ($hasOther) {
                $sorts[] = array('sid' => 0, 'sname' => '其他课程');
            }
        }
        $count = count($courses);
        $page = max(1, $this->page);
        $pagesize = max(1, $this->pagesize);
        $offset = ($page - 1) * $pagesize;
        $courses = array_slice($courses, $offset, $pagesize);
        return array(
            'count' => $count,
            'packages' => $packages,
            'sorts' => $sorts,
            'courses' => $courses
        );
    }

    /**
     * 课程服务列表
     */
    public function courseListAction() {
        $redis = Ebh()->cache;
        $roomUserModel = new RoomUserModel();
        //是否本校学生
        $isStudent = false;
        $userpermisionModel = new UserpermisionsModel();
        //课程权限
        $userpermissions = array();
        if ($this->uid > 0) {
            $isStudent = $roomUserModel->isAlumni($this->crid, $this->uid);
            $userpermissions = $userpermisionModel->checkPermission(array(), $this->uid, $this->crid);
        }

        $model = new BundleModel();
        $sortModel = new PaysortModel();
        //第一部分课程包列表
        $bundles = $model->simpleList($this->crid);
        if (!empty($bundles)) {
            $bids = array_keys($bundles);
            $bundleCourses = $model->courseList($bids);
            foreach ($bundleCourses as $itemid => $bundleCourse) {
                if (empty($bundleCourse['itemid'])) {
                    continue;
                }
                $viewnum = $redis->hget('folderviewnum', $bundleCourse['folderid'], false);
                if (!empty($viewnum)) {
                    $bundleCourse['viewnum'] = $viewnum;
                }
                $bundleCourse['hasPower'] = (!isset($bundleCourse['hasPower']) || !empty($bundleCourse['hasPower'])) && isset($userpermissions[$bundleCourse['folderid']]);
                $bundles[$bundleCourse['bid']]['courses'][] = $bundleCourse;
            }
            unset($bundleCourses);
            $bundles = array_filter($bundles, function($bundle) {
                return !empty($bundle['courses']);
            });
            $rankType = 'g';
            if ($this->pid > 0) {
                $rankType = 'p';
                if ($this->sid !== null) {
                    $rankType = 's';
                }
            }
            array_walk($bundles, function(&$bundle, $k, $rankType) {
                $bundle['coursewarenum'] = 0;
                $bundle['viewnum'] = 0;
                $bundle['hasPower'] = true;
                $ranks = $itemids = $foldernames = array();
                foreach ($bundle['courses'] as $course) {
                    if ($rankType == 'p') {
                        $ranks[] = intval($course['prank']);
                    } else if ($rankType == 's') {
                        $ranks[] = intval($course['srank']);
                    } else {
                        $ranks[] = intval($course['grank']);
                    }
                    $itemids[] = $course['itemid'];
                    $foldernames[] = $course['foldername'];
                    $bundle['coursewarenum'] += $course['coursewarenum'];
                    $bundle['viewnum'] += $course['viewnum'];
                    $bundle['hasPower'] = $bundle['hasPower'] && $course['hasPower'];
                }
                array_multisort($ranks, SORT_ASC, SORT_NUMERIC,
                    $itemids, SORT_DESC, SORT_NUMERIC, $bundle['courses']);
                $firstCourse = reset($bundle['courses']);
                $bundle['itemid'] = $firstCourse['itemid'];
                $bundle['folderid'] = $firstCourse['folderid'];
                $bundle['showmode'] = $firstCourse['showmode'];
                $bundle['foldername'] = implode(',', $foldernames);
                unset($itemids, $ranks, $foldernames, $bundle['courses']);
            }, $rankType);
        }
        //第二部分本校课程
        $courseServiceModel = new CourseServiceModel();
        $items = $courseServiceModel->courseList($this->crid);
        if (!empty($items)) {
            $showBySorts = array_filter($items, function($item) {
                return !empty($item['showbysort']);
            });
            if (!empty($showBySorts)) {
                //捆绑销售课程处理
                $sorts = array();
                $items = array_diff_key($items, $showBySorts);
                foreach ($showBySorts as $item) {
                    $item['hasPower'] = isset($userpermissions[$item['folderid']]);
                    $viewnum = $redis->hget('folderviewnum', $item['folderid'], false);
                    if (!empty($viewnum)) {
                        $item['viewnum'] = $viewnum;
                    }
                    if ($isStudent && !empty($item['isschoolfree'])) {
                        $item['iprice'] = 0;
                    }
                    $sorts[$item['sid']][] = $item;
                }
                $sids = array_keys($sorts);
                $showBySorts = $sortModel->getSortList(array('sid' => $sids), true);
                array_walk($showBySorts, function(&$sort, $k, $args) {
                    $sort['content'] = strip_tags($sort['content']);
                    if (empty($args[$k])) {
                        return;
                    }
                    $sort['viewnum'] = 0;
                    $sort['hasPower'] = true;
                    $sort['coursewarenum'] = 0;
                    $sort['iprice'] = 0;
                    $sort['cannotpay'] = true;
                    $sranks = $itemids = $foldernames = $powers = $cannotpays = array();
                    foreach ($args[$k] as $item) {
                        $sort['viewnum'] += $item['viewnum'];
                        $sort['coursewarenum'] += $item['coursewarenum'];
                        $sort['hasPower'] = $sort['hasPower'] && $item['hasPower'];
                        if (empty($item['cannotpay'])) {
                            $sort['cannotpay'] = false;
                        }
                        $cannotpays[] = intval($item['cannotpay']);
                        $sort['iprice'] += $item['iprice'];
                        $sranks[] = intval($item['srank']);
                        $itemids[] = $item['itemid'];
                        $foldernames[] = $item['foldername'];
                        $powers[] = intval($item['hasPower']);
                    }
                    array_multisort($cannotpays, SORT_ASC, SORT_NUMERIC,
                        $powers, SORT_ASC, SORT_NUMERIC,
                        $sranks, SORT_ASC, SORT_NUMERIC,
                        $itemids, SORT_DESC, SORT_NUMERIC, $args[$k]);
                    $firstItem = reset($args[$k]);
                    $sort['folderid'] = $firstItem['folderid'];
                    $sort['itemid'] = $firstItem['itemid'];
                    $sort['showmode'] = $firstItem['showmode'];
                    $sort['speaker'] = $firstItem['speaker'];
                    $sort['foldername'] = implode(',', $foldernames);
                    if (empty($sort['imgurl'])) {
                        $sort['imgurl'] = $firstItem['img'];
                    }
                    $sort['tagged'] = 1;
                }, $sorts);
                unset($sorts, $sids);
                $showBySorts = array_filter($showBySorts, function($sort) {
                    return !empty($sort['tagged']);
                });
            }
            array_walk($items, function(&$item, $k, $args) {
                $item['hasPower'] = isset($args['userpermissions'][$item['folderid']]);
                $viewnum = $args['cache']->hget('folderviewnum', $item['folderid'], false);
                if (!empty($viewnum)) {
                    $item['viewnum'] = $viewnum;
                }
                if ($args['isStudent'] && !empty($item['isschoolfree'])) {
                    $item['iprice'] = 0;
                }
            }, array(
                'cache' => $redis,
                'userpermissions' => $userpermissions,
                'isStudent' => $isStudent
            ));
            if (!empty($showBySorts)) {
                $items = array_merge($showBySorts, $items);
                unset($showBySorts);
            }
        }
        //第三部分企业选课
        $schcourses = $courseServiceModel->schCourseList($this->crid);
        if (!empty($schcourses)) {
            array_walk($schcourses, function(&$course, $k, $args) {
                $course['hasPower'] = isset($args['userpermissions'][$course['folderid']]);
                $viewnum = $args['cache']->hget('folderviewnum', $course['folderid'], false);
                if (!empty($viewnum)) {
                    $course['viewnum'] = $viewnum;
                }
            }, array(
                'cache' => $redis,
                'userpermissions' => $userpermissions
            ));
        }
        //课程集组合
        $items = array_merge($bundles, $items, $schcourses);
        unset($bundles, $schcourses);
        if (empty($items)) {
            return array(
                'count' => 0
            );
        }
        //课程packages
        $pids = array_column($items, 'pid');
        $pids = array_unique($pids);
        $packageModel = new PaypackageModel();
        $packages = $packageModel->getPackageMenus($pids, $this->crid);
        $forindex = false;
        $firstDisplayorder = 0;
        if (!empty($packages) && in_array($this->column, array(3, 4))) {
            $forindex = true;
            //首页课程列表调用，当本校的服务包排序号不等于-1时，取包下的课程列表，否则取所有的课程
            $firstPackage = reset($packages);
            if ($firstPackage['t'] == 0 && $firstPackage['displayorder'] != -1) {
                $this->pid = $firstPackage['pid'];
            }
            $firstDisplayorder = $firstPackage['displayorder'];
            unset($firstPackage);
        }
        //查询
        if ($this->pid > 0 && isset($packages[$this->pid])) {
            $packages[$this->pid]['cur'] = true;
            $pid = $this->pid;
            $items = array_filter($items, function($item) use($pid) {
                return $item['pid'] == $pid;
            });
            //课程包分类列表
            $sids = array();
            $hasOther = false;
            foreach ($items as $item) {
                if ($item['sid'] == 0) {
                    $hasOther = true;
                    continue;
                }
                if (!isset($sids[$item['sid']])) {
                    $sids[$item['sid']] = $item['sid'];
                }
            }
            if (!empty($sids)) {
                //读取课程包分类
                $packageSorts = $sortModel->getSortPackedList($sids);
            }
            if ($hasOther === true) {
                $packageSorts[0] = array(
                    'sid' => 0,
                    'pid' => $this->pid,
                    'showbysort' => 0,
                    'sname' => '其他课程'
                );
            }
            if ($this->sid !== null && isset($packageSorts[$this->sid])) {
                $sid = $this->sid;
                $items = array_filter($items, function($item) use($sid) {
                    return $item['sid'] == $sid;
                });
                if (isset($packageSorts[$sid])) {
                    $packageSorts[$sid]['cur'] = true;
                }
            }
        }
        $wheres = array();
        if (!empty($this->isfree)) {
            //免费
            $wheres['isfree'] = true;
        }
        if (!empty($this->k)) {
            //查询课程名、服务项名称、课程包名
            $wheres['k'] = $this->k;
        }
        if (!empty($wheres)) {
            $items = array_filter($items, function($item) use($wheres) {
                if (isset($wheres['isfree']) && (isset($item['bprice']) && $item['bprice'] > 0
                        || isset($item['iprice']) && $item['iprice'] > 0
                        || isset($item['price']) && $item['price'] > 0)) {
                    return false;
                }
                if (empty($wheres['k'])) {
                    return true;
                }
                if (isset($item['bid'])) {
                    //课程包名称，包中课程名称
                    return (stripos($item['name'], $wheres['k']) !== false || stripos($item['foldername'], $wheres['k']) !== false);
                }
                if (isset($item['tagged'])) {
                    //打包课程搜索包中课程名,包分类名
                    return (stripos($item['foldername'], $wheres['k']) !== false || stripos($item['sname'], $wheres['k'] !== false));
                }
                return (stripos($item['iname'], $wheres['k']) !== false || stripos($item['foldername'], $wheres['k']) !== false);
            });
        }
        //排序,0-默认排序[排序号、时间]，1-热度降序，2-价格降序，3-价格升序
        $orderGroup = array(
            0 => array(),//课程分组标志
            1 => array(),//来源网校
            2 => array(),//主排序属性
            3 => array()//ID,辅助排序
        );
        $sortCount = 0;
        if (!empty($packageSorts)) {
            $sortCount = count($packageSorts);
        }
        foreach ($items as $index => $item) {
            $priceKey = 'iprice';
            if (isset($item['bid'])) {
                $orderGroup[0][] = 0;
                $orderGroup[1][] = 0;
                $orderGroup[3][] = $item['bid'];
                $priceKey = 'bprice';
            } else if (isset($item['tagged'])) {
                $orderGroup[0][] = 1;
                $orderGroup[1][] = 0;
                $orderGroup[3][] = $item['sid'];
            } else if (isset($item['sourcecrid'])) {
                $orderGroup[0][] = 3 + $item['displayorder'];
                $orderGroup[1][] = $item['sourcecrid'];
                $orderGroup[3][] = $item['itemid'];
                $priceKey = 'price';
            } else {
                $orderGroup[0][] = 2;
                $orderGroup[1][] = 0;
                $orderGroup[3][] = $item['itemid'];
            }

            if ($this->otype == 1) {
                $orderGroup[2][] = $item['viewnum'];
            } else if ($this->otype == 2 || $this->otype == 3) {
                $orderGroup[2][] = $item[$priceKey];
            } else {
                if (isset($item['bid'])) {
                    $orderGroup[2][] = $item['displayorder'] + (empty($item['display']) ? PHP_INT_MAX : 0);
                } else if (isset($item['tagged'])) {
                    $orderGroup[2][] = $item['sdisplayorder'];
                } else if ($this->pid > 0 && (isset($this->sid) || $sortCount == 1)) {
                    $orderGroup[2][] = intval($item['srank']);
                } else if ($this->pid > 0) {
                    $orderGroup[2][] = intval($item['prank']);
                } else {
                    $orderGroup[2][] = intval($item['grank']);
                }
            }
            if (isset($item['bid'])) {
                $items[$index]['title'] = $item['name'];
                $items[$index]['summary'] = $item['remark'];
                $items[$index]['price'] = $item['bprice'];
                $items[$index]['t'] = 2;
                $items[$index]['id'] = $item['bid'];
            } else if (isset($item['tagged'])) {
                $items[$index]['title'] = $item['sname'];
                $items[$index]['cover'] = $item['imgurl'];
                $items[$index]['summary'] = $item['content'];
                $items[$index]['price'] = $item['iprice'];
                $items[$index]['t'] = 1;
                $items[$index]['id'] = $item['sid'];
            } else if (isset($item['sourcecrid'])) {
                $items[$index]['title'] = $item['iname'];
                $items[$index]['cover'] = $item['img'];
                $items[$index]['t'] = 0;
                $items[$index]['id'] = $item['itemid'];
            } else {
                $items[$index]['title'] = $item['iname'];
                $items[$index]['cover'] = $item['img'];
                $items[$index]['price'] = $item['iprice'];
                $items[$index]['t'] = 0;
                $items[$index]['id'] = $item['itemid'];
            }
            unset($items[$index]['displayorder'], $items[$index]['grank'], $items[$index]['prank'], $items[$index]['srank'],
                $items[$index]['imgurl'], $items[$index]['img'], $items[$index]['name'], $items[$index]['iname'],
                $items[$index]['sname'], $items[$index]['remark'], $items[$index]['content'], $items[$index]['bprice'],
                $items[$index]['iprice'], $items[$index]['bid'], $items[$index]['sid']);
        }
        $orderType = $this->otype == 1 || $this->otype == 2 ? SORT_DESC : SORT_ASC;
        array_multisort($orderGroup[0], SORT_ASC, SORT_NUMERIC,
            $orderGroup[1], SORT_DESC, SORT_NUMERIC,
            $orderGroup[2], $orderType, SORT_NUMERIC,
            $orderGroup[3], SORT_DESC, SORT_NUMERIC, $items);
        $count = count($items);
        $group = false;
        if ($forindex === true && $count > 0) {
            //主页结果集
            $group = $firstDisplayorder == -1 || $this->column == 3;
            $items = $this->_groupItems($this->column, $items, $firstDisplayorder, $packages);
        }
        //分页
        if ($this->page > 0 && !$group) {
            $pagesize = max($this->pagesize, 1);
            $offset = ($this->page - 1) * $pagesize;
            $items = array_slice($items, $offset, $pagesize);
        }
        if (empty($packages)) {
            $packages = array();
        }
        if (count($packages) == 1) {
            $firstIndex = key($packages);
            $packages[$firstIndex]['cur'] = true;
        }
        if (empty($packageSorts)) {
            $packageSorts = array();
        }
        if (count($packageSorts) == 1) {
            $firstIndex = key($packageSorts);
            $packageSorts[$firstIndex]['cur'] = true;
        }
        return array(
            'packages' => $packages,
            'sorts' => $packageSorts,
            'count' => $count,
            'courses' => $items,
            'grouped' => $group,
            'pid' => $this->pid
        );
    }

    /**
     * 打包分类详情
     * @return array
     */
    public function sortAction() {
        $sortModel = new PaysortModel();
        $sort = $sortModel->getSortdetail($this->sid);
        if (empty($sort)) {
            return false;
        }
        $itemModel = new PayitemModel();
        $courses = $itemModel->getSortCourseList($this->sid);
        if (empty($courses)) {
            return false;
        }
        $sranks = $folderids = $prices = array();
        $roomUserModel = new RoomUserModel();
        $isStudent = $roomUserModel->isAlumni($this->crid, $this->uid);
        foreach ($courses as $course) {
            $folderids[] = $course['folderid'];
            $sranks[] = intval($course['srank']);
            if ($isStudent && !empty($course['isschoolfree'])) {
                //本校免费课程对本校学生免费
                $course['iprice'] = 0;
            }
            $prices[] = $course['iprice'];
        }
        $folderids = array_unique($folderids);

        array_multisort($sranks, SORT_ASC, SORT_NUMERIC,
            $folderids, SORT_DESC, SORT_NUMERIC, $courses);
        //是否本校学生
        $isStudent = false;
        $userpermisionModel = new UserpermisionsModel();
        //课程权限
        $userpermissions = array();
        $userpermissions = $userpermisionModel->checkPermission($folderids, $this->uid, $this->crid);
        $unallows = array_diff($folderids, $userpermissions);
        //是否所有课程都开通过
        $allow = empty($unallows);
        $firstItem = reset($courses);
        if (empty($sort['showaslongblock'])) {
            $sort['imgurl'] = $firstItem['img'];
        }
        unset($sort['showaslongblock']);
        if ($allow) {
            $sort['hasPower'] = true;
        }
        $sort['folderid'] = $firstItem['folderid'];
        $sort['itemid'] = $firstItem['itemid'];
        $sort['price'] = array_sum($prices);
        return $sort;
    }

    /**
     * 课程包分类下课程集
     * @return mixed
     */
    public function sortCourseListAction() {
        $model = new CourseServiceModel();
        $sortCourses = $model->getPayItemsUnderSort($this->sid, $this->crid);
        if (empty($sortCourses)) {
            return array();
        }
        //是否本校学生
        $isStudent = false;
        $userpermisionModel = new UserpermisionsModel();
        //课程权限
        $userpermissions = array();
        if ($this->uid > 0) {
            $roomUserModel = new RoomUserModel();
            $isStudent = $roomUserModel->isAlumni($this->crid, $this->uid);
            $userpermissions = $userpermisionModel->checkPermission(array(), $this->uid, $this->crid);
        }
        array_walk($sortCourses, function(&$course, $k, $args) {
            if ($args['isStudent'] && !empty($course['isschoolfree'])) {
                $course['iprice'] = 0;
            }
            $course['hasPower'] = in_array($course['folderid'], $args['userpermissions']);
        }, array(
            'userpermissions' => $userpermissions,
            'isStudent' => $isStudent
        ));
        return $sortCourses;
    }

    /**
     * 课程列表展示分组
     * @param int $column 展示列数：3列或4列
     * @param array $items 课程列表
     * @param int $firstDisplayorder package最小排序号
     * @param array $groups packages列表
     * @return array
     */
    private function _groupItems($column, $items, $firstDisplayorder, $groups) {
        if ($firstDisplayorder == -1) {
            //定位package全部，按package分组，每组一行数据
            $ret = array();
            $displayorders = $pids = array();
            foreach ($items as $item) {
                if (!isset($groups[$item['pid']])) {
                    continue;
                }
                if (!isset($ret[$item['pid']])) {
                    $ret[$item['pid']] = array(
                        'pname' => $groups[$item['pid']]['pname']
                    );
                    $displayorders[] = $groups[$item['pid']]['displayorder'];
                    $pids[] = $item['pid'];
                }
                if (!empty($ret[$item['pid']]['courses']) && count($ret[$item['pid']]['courses']) >= $column) {
                    //每组只要一行数据($column个)
                    continue;
                }
                $ret[$item['pid']]['courses'][] = $item;
            }
            array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
                $pids, SORT_DESC, SORT_NUMERIC, $ret);
            return array_slice($ret, 0, 20);
        }
        if ($column == 4) {
            //四列，定位优先级最高的packages,返回20行数据
            return array_slice($items, 0, $column * 20);
        }
        $group_big = array();
        $group_normal = array();
        $group_small = array();
        $rows = 0;
        foreach ($items as $id => $course) {
            $viewMode = empty($course['view_mode']) ? 0 : intval($course['view_mode']);
            if ($viewMode == 2) {
                $group_big[$rows++] = $course;
                continue;
            }
            if ($viewMode == 0) {
                $last_group = end($group_normal);
                if ($last_group === false || count($last_group) % 3 == 0) {
                    $group_normal[$rows++] = array($course);
                    continue;
                }
                $k = key($group_normal);
                $group_normal[$k][] = $course;
                continue;
            }
            if ($viewMode == 1) {
                $last_group = end($group_small);
                if ($last_group === false || count($last_group) % 2 == 0) {
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
                $tmp[] = array('view_mode' => 2, 'courses' => array($group_big[$i]));
                $other_rows++;
                continue;
            }
            if (key_exists($i, $group_normal)) {
                $tmp[] = array('view_mode' => 0, 'courses' => $group_normal[$i]);
                $normal_rows++;
                continue;
            }
            if (key_exists($i, $group_small)) {
                $tmp[] = array('view_mode' => 1, 'courses' => $group_small[$i]);
                $other_rows++;
                continue;
            }
        }
        if (!empty($tmp)) {
            return $tmp;
        }
    }


    /**
     * @describe:教师授课列表
     * @User:tzq
     * @Date:2017/11/25
     * @param int $pid 课程主类id
     * @param int $sid 课程子类id
     * @param int $p   当前分页码
     * @param int $listRows 每页显示条数
     * @param int $uid 教师uid
     * @param int $crid 当前网校id
     * @param  int $orderBy 排序
     * 0 默认排序
     * 1 学分从高到低
     * 2 学分从低到高
     * 3 时长从高到低
     * 4 时长从低到高
     * 5 人气从高到低
     * 6 人气从低到高
     * 7 点赞从高到低
     * 8 点赞从低到高
     * 9 评论从高到低
     * 10 评论从低到高
     * 11 价格从高到低
     * 12 价格从低到高
     * 13 课件数从高到低
     * 14 课件数从低到高
     */
    public function teacherCourseListAction(){
        $params['pid'] = $this->pid;
        $params['sid'] = $this->sid;
        $params['curr'] = $this->p;
        $params['listRows'] = $this->listRows;
        $params['uid'] = $this->uid;
        $params['crid'] = $this->crid;
        $params['orderBy'] = $this->orderBy;
        if(0 >= $params['uid']){
            return '教师uid不能为空';
        }
        if(0 >= $params['crid']){
            return '网校id不能为空';
        }


        $model = new FolderModel();
        $ret   = $model->teacherCourseList($params);

        return $ret;
    }

    /**
     * @describe:教师授课-学生排序
     * @User:tzq
     * @Date:2017/11/25
     * @param int $folderid 课程id
     * @return  array
     */
    public function getCreditSoreAction(){

        $params['folderid'] = $this->folderid;
        $params['orderBy']  = $this->orderBy;
        $params['crid']     = $this->crid;
        $params['school_type']     = $this->school_type;
        switch ($params['orderBy']){
            case 1:
                $params['orderBy'] = array('credit','SORT_DESC');

                break;
            case 2:
                $params['orderBy'] =array('credit' ,'SORT_ASC') ;

                break;
            case 3:
                $params['orderBy'] = array('score','SORT_DESC');

                break;
            case 4:

                $params['orderBy'] =array('score' ,'SORT_ASC') ;

                break;
            case 5:
                $params['orderBy'] = array('ltime','SORT_DESC');


                break;
            case 6:
                $params['orderBy'] = array('ltime','SORT_ASC');


                break;
            default:
                $params['orderBy'] = '';

        }
        $foldermodel = new FolderModel();
        $ret =$foldermodel->courseStudentSort($params);
        return $ret;
    }

    /**
     * @describe:教师授课-文件统计
     * @User:tzq
     * @Date:2017/11/25
     * @param int $folderid 课程id
     * @return array
     */
    public function getFileCountAction(){
        $params['folderid'] = $this->folderid;
        $params['crid'] = $this->crid;
        $params['apiName']  = 'getFileCountAction';//防止和其他key冲突
        $model = new FolderModel();
        $ret   = $model->fileCount($params);
        return $ret;
    }

    /**
     * @describe:获取教师关联的课程分类
     * @User:tzq
     * @Date:2017/11/29
     * @param int $uid 教师id
     * @param int $crid 网校id
     */
   public function getCourseClassAction(){
       $params['uid'] = $this->uid;
       $params['crid']= $this->crid;
       $model = new FolderModel();
       $ret   = $model->getClass($params);
       return $ret;
   }
}

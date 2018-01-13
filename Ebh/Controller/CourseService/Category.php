<?php

/**
 * 课程分类
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/11/8
 * Time: 17:32
 */
class CategoryController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //课程分类
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            )
        );
    }

    /**
     * 课程分类，本校课程分类+企业选课课程分类
     */
    public function indexAction() {
        $payItemModel = new PayitemModel();
        $packageModel = new PaypackageModel();
        $sortModel = new PaysortModel();
        $bundleModel = new BundleModel();
        $schsourceitemModel = new SchsourceitemModel();
        $schsourceModel = new SchsourceModel();
        $ret = array();
        //本校服务
        $categoryIds = $payItemModel->getCategoryIds($this->crid);
        $bundleCategoryIds = $bundleModel->getCategoryIds($this->crid);
        $categoryIds = array_merge($categoryIds, $bundleCategoryIds);
        unset($bundleCategoryIds);
        if (!empty($categoryIds)) {
            $pids = $sids = $others = array();
            foreach ($categoryIds as $categoryId) {
                if (!isset($pids[$categoryId['pid']])) {
                    $pids[$categoryId['pid']] = $categoryId['pid'];
                }
                if ($categoryId['sid'] != 0 && !isset($sids[$categoryId['sid']])) {
                    $sids[$categoryId['sid']] = $categoryId['sid'];
                }
                if ($categoryId['sid'] == 0 && !isset($others[$categoryId['pid']])) {
                    $others[$categoryId['pid']] = 0;
                }
            }
            $packages = $packageModel->getPackageMenuList($pids);
            $sorts = $sortModel->getSortMenuList($sids);
            foreach($sorts as $sort) {
                if (!isset($packages[$sort['pid']])) {
                    continue;
                }
                $packages[$sort['pid']]['sorts'][] = $sort;
            }
            foreach ($others as $k => $other) {
                if (!isset($packages[$k])) {
                    continue;
                }
                $packages[$k]['sorts'][] = array(
                    'sid' => 0,
                    'sname' => '其他课程',
                    'pid' => $k
                );
            }
            $ret[] = array(
                'sourcecrid' => $this->crid,
                'name' => '本校课程',
                'packages' => array_values($packages)
            );
        }
        //企业选课
        $sourcecrids = $pids = $sids = $others = array();
        $categoryIds = $schsourceitemModel->getCategoryIds($this->crid);
        foreach ($categoryIds as $categoryId) {
            if (!isset($sourcecrids[$categoryId['sourcecrid']])) {
                $sourcecrids[$categoryId['sourcecrid']] = $categoryId['sourcecrid'];
            }
            if (!isset($pids[$categoryId['pid']])) {
                $pids[$categoryId['pid']] = $categoryId['sourcecrid'];
            }
            if ($categoryId['sid'] != 0 && !isset($sids[$categoryId['sid']])) {
                $sids[$categoryId['sid']] = $categoryId['sid'];
            }
            if ($categoryId['sid'] == 0 && !isset($others[$categoryId['pid']])) {
                $others[$categoryId['pid']] = 0;
            }
        }
        $apid = array_keys($pids);
        $packages = $packageModel->getPackageMenuList($apid);
        $sorts = $sortModel->getSortMenuList($sids);
        $schsources = $schsourceModel->getSourceSchoolList($this->crid, $sourcecrids);
        foreach($sorts as $sort) {
            if (!isset($packages[$sort['pid']])) {
                continue;
            }
            $packages[$sort['pid']]['sorts'][] = $sort;
        }
        foreach ($others as $k => $other) {
            if (!isset($packages[$k])) {
                continue;
            }
            $packages[$k]['sorts'][] = array(
                'sid' => 0,
                'sname' => '其他课程',
                'pid' => $k
            );
        }
        foreach ($packages as $package) {
            $sourcecrid = $pids[$package['pid']];
            if (!isset($schsources[$sourcecrid])) {
                continue;
            }
            $schsources[$sourcecrid]['packages'][] = $package;
        }
        if (!empty($schsources)) {
            $ret = array_merge($ret, $schsources);
        }
        return $ret;
    }
}

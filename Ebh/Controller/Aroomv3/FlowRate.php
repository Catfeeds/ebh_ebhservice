<?php

/**
 * 流量分析
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/4/25
 * Time: 13:52
 */
class FlowRateController extends Controller {
    public function __construct() {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //流量分析
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'starttime' => array(
                    'name' => 'starttime',
                    'require' => false,
                    'type' => 'int'
                ),
                'endtime' => array(
                    'name' => 'endtime',
                    'require' => false,
                    'type' => 'int'
                )
            ),
            'screenAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'starttime' => array(
                    'name' => 'starttime',
                    'require' => false,
                    'type' => 'int'
                ),
                'endtime' => array(
                    'name' => 'endtime',
                    'require' => false,
                    'type' => 'int'
                )
            ),
            'osAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'starttime' => array(
                    'name' => 'starttime',
                    'require' => false,
                    'type' => 'int'
                ),
                'endtime' => array(
                    'name' => 'endtime',
                    'require' => false,
                    'type' => 'int'
                )
            ),
            'browserAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'starttime' => array(
                    'name' => 'starttime',
                    'require' => false,
                    'type' => 'int'
                ),
                'endtime' => array(
                    'name' => 'endtime',
                    'require' => false,
                    'type' => 'int'
                )
            ),
            'ispAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'starttime' => array(
                    'name' => 'starttime',
                    'require' => false,
                    'type' => 'int'
                ),
                'endtime' => array(
                    'name' => 'endtime',
                    'require' => false,
                    'type' => 'int'
                )
            )
        );
    }

    /**
     * 流量分析
     */
    public function indexAction() {
        $splitByDay = true;
        $params = array();
        $today = strtotime(date('Y-m-d'));
        if ($this->starttime !== NULL) {
            $params['starttime'] = intval($this->starttime);
        } else {
            $params['starttime'] = $today;
        }
        if ($params['starttime'] >= $today) {
            $splitByDay = false;
        }
        if ($splitByDay && $this->endtime !== NULL) {
            $endtime = intval($this->endtime);
            if ($endtime >= $params['starttime'] + 86400) {
                $params['endtime'] = $endtime;
            } else {
                $params['endtime'] = $params['starttime'] + 86399;
                $splitByDay = false;
            }
        }
        $signLogModel = new SignLogModel();
        $signs = $signLogModel->signResolve($this->crid, $params, true, $today);
        $loginLogModel = new LoginlogModel();
        $analyze = $loginLogModel->getAnalyzeList($this->crid, $params, true, $today);
        $list = array();
        $keys = array();
        if (!empty($analyze)) {
            $users = array_column($analyze, 'users');
            $keys = array_keys($analyze);
        } else {
            $users = array();
        }

        if (!empty($signs)) {
            $signCounts = array_column($signs, 'signs');
            $sKeys = array_keys($signs);
            $keys = array_merge($keys, $sKeys);
            unset($sKeys);
        } else {
            $signCounts = array();
        }

        if (empty($keys)) {
            return array(
                'signCount' => 0,
                'userCount' => 0,
                'ipCount' => 0,
                'unit' => $splitByDay ? 'day' : 'hour',
                'list' => $list
            );
        }
        $keys = array_unique($keys);
        $sortParams = array_map(function($date) {
            return strtotime($date);
        }, $keys);
        $step = $splitByDay ? 86400 : 3600;
        $max = max($sortParams);
        $min = $params['starttime'];//min($sortParams);
        if (!empty($params['endtime'])) {
            $max = $params['endtime'];
        }

        $format = $splitByDay ? 'Y-m-d' : 'Y-m-d H:00:00';
        while($min <= $max) {
            $dKey = date($format, $min);
            if (!in_array($dKey, $keys)) {
                $keys[] = $dKey;
                $sortParams[] = $min + $step;
            }
            $min += $step;
        }
        array_multisort($sortParams, SORT_ASC, SORT_NUMERIC, $keys);
        foreach ($keys as $key) {
            $item = array(
                'date' => $key,
                'signs' => 0,
                'users' => 0,
                'ips' => 0
            );
            if (isset($signs[$key])) {
                $item['signs'] = $signs[$key]['signs'];
            }
            if (isset($analyze[$key])) {
                $item['users'] = $analyze[$key]['users'];
                $item['ips'] = $analyze[$key]['ips'];
            }
            $list[] = $item;
        }
        $userCount = $ipCount = 0;
        $dayData = $loginLogModel->getDayAnalyze($this->crid, $params['starttime'], isset($params['endtime'])? $params['endtime'] : $params['starttime'] + 86399);
        if (!empty($dayData)) {
            $ipCount = $dayData['ips'];
        }
        if (!$splitByDay) {
            if (!empty($dayData)) {
                $userCount = $dayData['users'];
            }
        } else {
            $userCount = array_sum($users);
        }
        $ret = array(
            'signCount' => array_sum($signCounts),
            'userCount' => $userCount,
            'ipCount' => $ipCount,
            'unit' => $splitByDay ? 'day' : 'hour',
            'list' => $list
        );
        return $ret;
    }

    /**
     * 屏幕分辨率分析
     * @return mixed
     */
    public function screenAction() {
        $params = array();
        $today = strtotime('today');
        if ($this->starttime !== NULL) {
            $params['starttime'] = intval($this->starttime);
        } else {
            $params['starttime'] = $today;
        }
        if ($this->endtime !== NULL) {
            $endtime = intval($this->endtime);
            if ($endtime >= $params['starttime'] + 86400) {
                $params['endtime'] = $endtime;
            } else {
                $params['endtime'] = $params['starttime'] + 86399;
            }
        }
        $model = new LoginlogModel();
        $ret = $model->screen($this->crid, $params, true, $today);
        return $ret;
    }

    /**
     * 网络设备分析
     */
    public function osAction() {
        $params = array();
        $today = strtotime('today');
        if ($this->starttime !== NULL) {
            $params['starttime'] = intval($this->starttime);
        } else {
            $params['starttime'] = $today;
        }
        if ($this->endtime !== NULL) {
            $endtime = intval($this->endtime);
            if ($endtime >= $params['starttime'] + 86400) {
                $params['endtime'] = $endtime;
            } else {
                $params['endtime'] = $params['starttime'] + 86399;
            }
        }
        $model = new LoginlogModel();
        $ret = $model->os($this->crid, $params, $today);
        return $ret;
    }

    /**
     * 浏览器分析
     */
    public function browserAction() {
        $params = array();
        $today = strtotime('today');
        if ($this->starttime !== NULL) {
            $params['starttime'] = intval($this->starttime);
        } else {
            $params['starttime'] = $today;
        }
        if ($this->endtime !== NULL) {
            $endtime = intval($this->endtime);
            if ($endtime >= $params['starttime'] + 86400) {
                $params['endtime'] = $endtime;
            } else {
                $params['endtime'] = $params['starttime'] + 86399;
            }
        }
        $model = new LoginlogModel();
        $ret = $model->browser($this->crid, $params, $today);
        return $ret;
    }

    /**
     * 网络服务商分析
     */
    public function ispAction() {
        $params = array();
        $today = strtotime('today');
        if ($this->starttime !== NULL) {
            $params['starttime'] = intval($this->starttime);
        } else {
            $params['starttime'] = $today;
        }
        if ($this->endtime !== NULL) {
            $endtime = intval($this->endtime);
            if ($endtime >= $params['starttime'] + 86400) {
                $params['endtime'] = $endtime;
            } else {
                $params['endtime'] = $params['starttime'] + 86399;
            }
        }
        $model = new LoginlogModel();
        $ret = $model->ips($this->crid, $params, $today);
        if (!empty($ret)) {
            $ispArr = Ebh()->config->get('isp');
            array_walk($ret, function(&$isp, $k, $ispArr) {
                $isp['isp'] = isset($ispArr[$isp['isp']]) ? $ispArr[$isp['isp']] : '未知';
            }, $ispArr);
        }
        return $ret;
    }
}
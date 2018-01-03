<?php

/**
 * Created by PhpStorm.
 * User: app
 * Date: 2017/3/23
 * Time: 16:49
 */
class AskQuestionController extends Controller {
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules() {
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
                )
            ),
            //设置屏蔽状态
            'setShieldAction' => array(
                'qid' => array(
                    'name' => 'qid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'shield' => array(
                    'name' => 'shield',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //根据qid获取问题详情
            'getAskByQidAction' => array(
                'qid' => array(
                    'name' => 'qid',
                    'type' => 'int',
                    'require' => true
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
        $limits = array();
        if ($this->pagesize !== NULL) {
            $limits['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limits['page'] = $this->pagenum;
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
        return $model->getCount($this->crid, $filters);
    }

    /**
     * 设置屏蔽状态
     */
    public function setShieldAction() {
        $model = new AskQuestionModel();
        $shield = $this->shield == 0 ? 0 : 1;
        return $model->setShield($this->qid, $this->crid, $shield);
    }
    /**
     * 根据qid获取问题详情
     */
    public function getAskByQidAction() {
        $filters = array();
        $model = new AskQuestionModel();
        $filters['qid'] = $this->qid;
        $filters['changestatus'] = 1;   //等于1，获取的问题详情时，去除当前问题shield状态是否为0的判断
        return $model->detail($filters);
    }
}
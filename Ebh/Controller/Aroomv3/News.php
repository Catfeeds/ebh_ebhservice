<?php

/**
 * 新闻资讯
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/20
 * Time: 14:53
 */
class NewsController extends Controller
{
    public function __construct()
    {
        parent::init();
    }

    public function parameterRules()
    {
        return array(
            //列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'require' => true,
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'require' => true,
                    'type' => 'int'
                ),
                'navcode' => array(
                    'name' => 'navcode',
                    'type' => 'string'
                ),
                'early' => array(
                    'name' => 'early',
                    'type' => 'int'
                ),
                'latest' => array(
                    'name' => 'latest',
                    'type' => 'int'
                ),
                'q' => array(
                    'name' => 'q',
                    'type' => 'string'
                )
            ),
            //资讯分类
            'newsCategoryAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //资讯统计
            'countAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'navcode' => array(
                    'name' => 'navcode',
                    'type' => 'string'
                ),
                'early' => array(
                    'name' => 'early',
                    'type' => 'int'
                ),
                'latest' => array(
                    'name' => 'latest',
                    'type' => 'int'
                ),
                'q' => array(
                    'name' => 'q',
                    'type' => 'string'
                )
            ),
            //资讯详情
            'detailAction' => array(
                'itemid' => array(
                    'name' => 'itemid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //发布新闻资讯
            'addAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'require' => true
                ),
                'navcode' => array(
                    'name' => 'navcode',
                    'type' => 'string',
                    'require' => true
                ),
                'subject' => array(
                    'name' => 'subject',
                    'type' => 'string',
                    'require' => true
                ),
                'message' => array(
                    'name' => 'message',
                    'type' => 'string',
                    'requrie' => true
                ),
                'note' => array(
                    'name' => 'note',
                    'type' => 'string',
                    'requrie' => true
                ),
                'thumb' => array(
                    'name' => 'thumb',
                    'type' => 'string'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'viewnum' => array(
                    'name' => 'viewnum',
                    'type' => 'int'
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'int'
                ),
                'displayorder' => array(
                    'name' => 'displayorder',
                    'type' => 'int'
                ),
				'attid' => array(
                    'name' => 'attid',
                    'type' => 'string'
                )
            ),
            //更新资讯
            'updateAction' => array(
                'itemid' => array(
                    'name' => 'itemid',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'ip' => array(
                    'name' => 'ip',
                    'require' => true,
                    'type' => 'string'
                ),
                'navcode' => array(
                    'name' => 'navcode',
                    'type' => 'string'
                ),
                'subject' => array(
                    'name' => 'subject',
                    'type' => 'string'
                ),
                'message' => array(
                    'name' => 'message',
                    'type' => 'string'
                ),
                'note' => array(
                    'name' => 'note',
                    'type' => 'string'
                ),
                'thumb' => array(
                    'name' => 'thumb',
                    'type' => 'string'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int'
                ),
                'viewnum' => array(
                    'name' => 'viewnum',
                    'type' => 'int'
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'int'
                ),
                'displayorder' => array(
                    'name' => 'displayorder',
                    'type' => 'int'
                ),
				'attid' => array(
                    'name' => 'attid',
                    'type' => 'string'
                )
            ),
            //删除资讯
            'removeAction' => array(
                'itemid' => array(
                    'name' => 'itemid',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            'newsCategoryMenuAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //首页资讯列表
            'getNewsListsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'begin' => array(
                    'name' => 'begin',
                    'type' => 'int'
                ),
                'last' => array(
                    'name' => 'last',
                    'type' => 'int'
                ),
                'navcode' => array(
                    'name' => 'navcode',
                    'type' => 'string'
                )
            )
        );
    }

    /**
     * 新闻列表
     * @return mixed
     */
    public function indexAction() {
        $model = new NewsModel();
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        if ($this->navcode !== NULL) {
            $filterParams['navcode'] = trim($this->navcode);
        }
        if ($this->early !== NULL) {
            $filterParams['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filterParams['latest'] = $this->latest;
        }
        if (isset($this->q) && $this->q != '') {
            $filterParams['q'] = $this->q;
        }
        return $model->getList($filterParams, array(
            'pagesize' => $this->pagesize,
            'page' => $this->pagenum
        ), false);
    }

    /**
     * 新闻资讯分类
     * @return array|bool|
     */
    public function newsCategoryAction() {
        $model = new ClassRoomModel();
        $navigator =  $model->getNavigator($this->crid);
        $navigator = unserialize($navigator);
        if ($navigator !== false) {
            $nav = array_filter($navigator['navarr'], function($nitem) {
                return !empty($nitem['available']) && ($nitem['code'] == 'news' || preg_match('/^n\d+$/', $nitem['code']));
            });
            $nav = array_map(function($nitem) {
                $formatItem = array(
                    'code' => $nitem['code'],
                    'name' => $nitem['nickname']
                );
                if (!empty($nitem['subnav'])) {
                    $subitems = array_filter($nitem['subnav'], function($subitem) {
                       return !empty($subitem['subavailable']) && preg_match('/^n\d+s\d+$/', $subitem['subcode']);
                    });
                    $subitems = array_map(function($sitem) {
                        return array(
                            'code' => $sitem['subcode'],
                            'name' => $sitem['subnickname']
                        );
                    }, $subitems);
                    $formatItem['subnav'] = $subitems;
                }
                return $formatItem;
            }, $nav);
            $nav[] = array(
                'code' => 'deleted',
                'name' => '已删除分类'
            );
            $ret = array();
            foreach ($nav as $it) {
                $ret[] = $it;
                if (!empty($it['subnav'])) {
                    foreach ($it['subnav'] as $sitem) {
                        $sitem['name'] = '　　'.$sitem['name'];
                        $ret[] = $sitem;
                    }
                 }
            }
            return $ret;
        }
        return false;
    }

    /**
     * 资讯统计
     */
    public function countAction() {
        $model = new NewsModel();
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        if ($this->navcode !== NULL) {
            $filterParams['navcode'] = trim($this->navcode);
        }
        if ($this->early !== NULL) {
            $filterParams['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filterParams['latest'] = $this->latest;
        }
        if (isset($this->q)) {
            $filterParams['q'] = $this->q;
        }
        return $model->getCount($filterParams);
    }

    /**
     * 资讯详情
     * @return mixed
     */
    public function detailAction() {
        $model = new NewsModel();
        $news = $model->getModel($this->itemid);
		//加入附件信息
		if(!empty($news['attid'])){
			$attrModel = new AttachmentModel();
			$attr = $attrModel->getMultiAttachByAttid($news['attid'],$news['crid']);
			if (!empty($attr)) {
				$news['attr'] = $attr;
			}
		}
		return $news;
    }

    /**
     * 发布资讯
     */
    public function addAction() {
        $model = new NewsModel();
        $params = array(
            'navcode' => $this->navcode,
            'subject' => $this->subject,
            'message' => $this->message,
            'note' => $this->note
        );
        if ($this->thumb !== NULL) {
            $params['thumb'] = $this->thumb;
        }
        if ($this->viewnum !== NULL) {
            $params['viewnum'] = $this->viewnum;
        }
        if ($this->displayorder !== NULL) {
            $params['displayorder'] = $this->displayorder;
        }
        if ($this->status !== NULL) {
            $params['status'] = $this->status;
        }
        if ($this->ip !== NULL) {
            $params['ip'] = $this->ip;
        }
		if ($this->attid !== NULL) {
            $params['attid'] = $this->attid;
        }
        return $model->add($this->crid, $this->uid, $params);
    }

    /**
     * 更新资讯
     */
    public function updateAction() {
        $model = new NewsModel();
        $params = array();
        if ($this->navcode !== NULL) {
            $params['navcode'] = $this->navcode;
        }
        if ($this->subject !== NULL) {
            $params['subject'] = $this->subject;
        }
        if ($this->message !== NULL) {
            $params['message'] = $this->message;
        }
        if ($this->note !== NULL) {
            $params['note'] = $this->note;
        }
        if ($this->thumb !== NULL) {
            $params['thumb'] = $this->thumb;
        }
        if ($this->status !== NULL) {
            $params['status'] = $this->status;
        }
        if ($this->viewnum !== NULL) {
            $params['viewnum'] = $this->viewnum;
        }
        if ($this->displayorder !== NULL) {
            $params['displayorder'] = $this->displayorder;
        }
        if ($this->ip !== NULL) {
            $params['ip'] = $this->ip;
        }
		if ($this->attid !== NULL) {
            $params['attid'] = $this->attid;
        }
        if (empty($params)) {
            return 0;
        }
        return $model->update($this->itemid, $this->crid, $params);
    }

    /**
     * 删除资讯
     */
    public function removeAction() {
        $model = new NewsModel();
        return $model->remove($this->itemid, $this->crid);
    }

    /**
     * 新闻分层类型分类
     * @return array|bool
     */
    public function newsCategoryMenuAction() {
        $model = new ClassRoomModel();
        $navigator =  $model->getNavigator($this->crid);
        $newModel = new NewsModel();
        $newNavcodes = $newModel->newsNavCodeList($this->crid);
        $navigator = unserialize($navigator);
        if ($navigator !== false) {
            $nav = array_filter($navigator['navarr'], function($nitem) {
                return !empty($nitem['available']) && ($nitem['code'] == 'news' || preg_match('/^n\d+$/', $nitem['code']));
            });
            $nav = array_map(function($nitem) {
                $formatItem = array(
                    'code' => $nitem['code'],
                    'name' => $nitem['nickname'],
                    'newsCount' => 0
                );
                if (!empty($nitem['subnav'])) {
                    $subitems = array_filter($nitem['subnav'], function($subitem) {
                        return !empty($subitem['subavailable']) && preg_match('/^n\d+s\d+$/', $subitem['subcode']);
                    });
                    $subitems = array_map(function($sitem) {
                        return array(
                            'code' => $sitem['subcode'],
                            'name' => $sitem['subnickname'],
                            'newsCount' => 0
                        );
                    }, $subitems);
                    $formatItem['subnav'] = $subitems;
                }
                return $formatItem;
            }, $nav);
            $nav[] = array(
                'code' => 'deleted',
                'name' => '已删除分类',
                'newsCount' => 0
            );
        } else {
            $nav = array(
                'code' => 'news',
                'name' => '新闻资讯'
            );
        }

        if (!empty($newNavcodes)) {
            $newNavcodes = implode(',', $newNavcodes);
            array_walk($nav, function(&$category, $k, $newNavcodes) {
                $category['newsCount'] = intval(preg_match_all('/'.$category['code'].'/', $newNavcodes, $mats));
                if (empty($category['subnav'])) {
                    return;
                }
                array_walk($category['subnav'], function(&$sub, $k, $newNavcodes) {
                    $sub['newsCount'] = intval(preg_match_all('/'.$sub['code'].'/', $newNavcodes, $mats));
                }, $newNavcodes);
            }, $newNavcodes);
        }
        return $nav;
    }
    /**
     * 首页资讯列表
     * @return mixed
     */
    public function getNewsListsAction() {
        $model = new NewsModel();
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        if(!empty($this->navcode)){
            $filterParams['navcode'] = trim($this->navcode);
        }
        if(isset($this->begin)){
            $filterParams['begin'] = $this->begin;
        }
        if(!empty($this->last)){
            $filterParams['last'] = $this->last;
        }
        $newslists = $model->getNewsLists($filterParams);
        $newscount = $model->getNewsListsCount($filterParams);
        $newslists = !empty($newslists) ? $newslists : array();
        $newscount = !empty($newscount) ? $newscount : 0;
        return array('newslist'=>$newslists,'count'=>$newscount);
    }
}
<?php
/**
 * ebhservice.
 * Author: tyt
 * Email: 345468755@qq.com
 */
class MyarticleController extends Controller{
    public $article;
    public $review;

    public function init(){
        parent::init();
        $this->article = new NewsModel();
        $this->review = new ReviewModel();
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','default' => 0,'type'=>'int'),
                'status'  =>  array('name'=>'status','type'=>'int'),
                'notdel'  =>  array('name'=>'notdel','default' => -1,'type'=>'int'),
                'reviews'  =>  array('name'=>'reviews','default' => 1,'type'=>'int'),//默认需要评论
                'type'  =>  array('name'=>'type','default' => 1,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'early' => array('name' => 'early','type' => 'int' ),
                'latest' => array('name' => 'latest','type' => 'int'),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>0)
            ),
            'detailAction'   =>  array(
                'status'  =>  array('name'=>'status','type'=>'int','default'=>1),
                'itemid'  =>  array('name'=>'itemid','require'=>true,'type'=>'int')
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'title'  =>  array('name'=>'title','require'=>true),
                'message'  =>  array('name'=>'message','require'=>true)
            ),
            'addReviewAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'articleid'  =>  array('name'=>'articleid','require'=>true,'type'=>'int'),
                'message'  =>  array('name'=>'message','require'=>true)
            ),
             'getReviewsAction'   =>  array(//获取某个人或者某网校下的评论
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'articleid'  =>  array('name'=>'articleid','default'=>0,'type'=>'int'),
                'allreviews'  =>  array('name'=>'allreviews','default'=>0,'type'=>'int'),
                'status'  =>  array('name'=>'status','type'=>'int'),
                'del'  =>  array('name'=>'del','default' => 0,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'early' => array('name' => 'early','type' => 'int' ),
                'latest' => array('name' => 'latest','type' => 'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>0),
                'count'  =>  array('name'=>'count','type'=>'int','default'=>1)//需要统计字段
            ),
            'delAction'   =>  array(
                'itemid'  =>  array('name'=>'itemid','require'=>true,'type'=>'int')
            ),
            'updateReviewAction' => array(
                'itemid' => array(
                    'name' => 'itemid',
                    'require' => true,
                    'type' => 'int'
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'int'
                ),
                'del' => array(
                    'name' => 'del',
                    'type' => 'int'
                )
            ),
            //更新原创文章
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
                )
            ),
            'myarticleCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int')
            ),
            'reviewCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int')
            ),
        );
    }

    /**
     * 添加原创
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['message'] = $this->message;
        $parameters['subject'] = $this->title;
        $parameters['note'] = 1;
        $parameters['type'] = 1;
        return $this->article->add($this->crid,$this->uid,$parameters);
    }

    /**
     * 添加文章评论
     * @return mixed
     */
    public function addReviewAction(){
        $parameters = array();
        $parameters['message'] = $this->message;
        $parameters['articleid'] = $this->articleid;
        return $this->article->addReview($this->crid,$this->uid,$parameters);
    }

     /**
     * 获取某个人或者某网校下的评论
     * @return mixed
     */ 
    public function getReviewsAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        if ($this->articleid) {
            $parameters['articleid'] = $this->articleid;
        }
        if ($this->allreviews) {
            $parameters['allreviews'] = $this->allreviews;//单文章评论不去重
        }
        if (isset($this->status)) {
            $parameters['status'] = $this->status;
        }
        if ($this->early !== NULL) {
            $parameters['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $parameters['latest'] = $this->latest;
        }
        if ($this->uid) {
            $parameters['uid'] = $this->uid;
        }
        if (!empty($this->q)) {
            $parameters['q'] = $this->q;
        }
        if (isset($this->del)) {
            $parameters['del'] = $this->del;
        }
        if ($this->page) {
            $parameters['page'] = $this->page;
        }
        $res = $this->article->getReviews($parameters, array(
            'pagesize' => $this->pagesize,
            'page' => $this->page
        ));
        if ($this->count) {//评论下带有文章的评论总数
            if (!empty($res['list'])) {
                $articleids = '';
                foreach ($res['list'] as $value) {
                    $articleids .= $value['itemid'].',';//文章id
                }
                $article_reviews = $this->article->getReviewsByArticleId(substr($articleids, 0, -1),$this->status);

                if (!empty($article_reviews)) {
                    foreach ($article_reviews as $avalue) {
                        $views[$avalue['itemid']] = $avalue;
                    }
                    foreach ($res['list'] as &$lvalue) {
                        if (isset($views[$lvalue['itemid']])) {
                            $lvalue['review'] = $views[$lvalue['itemid']]['count'];
                        }
                    }
                }
            }
        }
        //把用户的信息追加
        if (!$this->articleid) {
            //说明要显示文章作者的信息
            $this->bulidUserInfo($res,'auid');
        } else {
            $this->bulidUserInfo($res);
        }
        return $res;
    }

    /**
     * 获取总的原创文章列表
     * @return array
     */
    public function listAction(){
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        if ($this->uid) {
            $filterParams['uid'] = $this->uid;
        }
        if (isset($this->status)) {
            $filterParams['status'] = $this->status;
        }
        if (isset($this->notdel)) {
            $filterParams['notdel'] = $this->notdel;
        }
        if ($this->type !== NULL) {
            $filterParams['type'] = $this->type;
        }
        if ($this->early !== NULL) {
            $filterParams['early'] = $this->early;
        }
        if ($this->latest !== NULL) {
            $filterParams['latest'] = $this->latest;
        }
        if (!empty($this->q)) {
            $filterParams['q'] = $this->q;
        }
        $res = $this->article->getListAndTotalPage($filterParams, array(
            'pagesize' => $this->pagesize,
            'page' => $this->page
        ), false);
        if ($this->reviews) {
            //统计评论数量
            if (!empty($res['list'])) {
                $articleids = '';
                foreach ($res['list'] as $value) {
                    $articleids .= $value['itemid'].',';//文章id
                }
                $article_reviews = $this->article->getReviewsByArticleId(substr($articleids, 0, -1),$this->status);
                if (!empty($article_reviews)) {
                    foreach ($article_reviews as $avalue) {
                        $views[$avalue['itemid']] = $avalue;
                    }
                    foreach ($res['list'] as &$lvalue) {
                        if (isset($views[$lvalue['itemid']])) {
                            $lvalue['review'] = $views[$lvalue['itemid']]['count'];
                        }
                    }
                }
                //把用户的信息追加
                $this->bulidUserInfo($res);
            }
        }
        return $res;
        
    }

    /**
     * 资讯详情
     * @return mixed
     */
    public function detailAction() {
        $detail = $this->article->getModel($this->itemid);
        if (!empty($detail)) {
            $this->crid =  $detail['crid'];
            $res['list'] = array($detail);
            $this->bulidUserInfo($res);
            $res['detail'] = $res['list'][0];
            unset($res['list']);
            $article_review = $this->article->getReviewsByArticleId($this->itemid,$this->status);
            $res['detail']['review'] = !empty($article_review[0]['count']) ? $article_review[0]['count'] : 0;
        }
        return $res;
    }

    /**
     * 更新资讯
     */
    public function updateAction() {
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
        if (empty($params)) {
            return 0;
        }
        return $this->article->update($this->itemid, $this->crid, $params);
    }

    /**
     * 更新资讯
     */
    public function updateReviewAction() {
        $params = array();
        if ($this->status !== NULL) {
            $params['status'] = $this->status;
        }
        if ($this->del !== NULL) {
            $params['del'] = $this->del;
        }
        if (empty($params)) {
            return 0;
        }
        return $this->article->updateReview($this->itemid, $params);
    }

    /**
     *构建用户信息的函数，包括班级
     *@param $result,返回list包括uid
     */
    public function bulidUserInfo(&$result,$defaultKey='uid') {
        if (!empty($result['list'])) {
            $uidsArr = array();
            foreach ($result['list'] as $value) {
                $uidsArr[] = $value[$defaultKey];
            }
            //获取用户的信息追加
            $uids = implode(',', array_unique($uidsArr));
            $userModel = new UserModel();
            $classesModel = new ClassesModel();
            $userinfos = $userModel->getUserInfoByUid($uids);
            $classinfos = $classesModel->getClassInfoByCrid($this->crid,array_unique($uidsArr));
            if (!empty($classinfos)) {
                foreach ($classinfos as $cvalue) {
                    $user_classes[$cvalue['uid']] = $cvalue;
                }
            }
            if (!empty($userinfos)) {
                foreach ($userinfos as $uvalue) {
                    $user_infos[$uvalue['uid']] = $uvalue;
                }
                foreach ($result['list'] as &$rvalue) {
                    if (isset($user_infos[$rvalue[$defaultKey]])) {//把用户信息追加
                        $rvalue['username'] = $user_infos[$rvalue[$defaultKey]]['username'];
                        $rvalue['realname'] = $user_infos[$rvalue[$defaultKey]]['realname'];
                        $rvalue['sex'] = $user_infos[$rvalue[$defaultKey]]['sex'];
                        $rvalue['face'] = $user_infos[$rvalue[$defaultKey]]['face'];
                        $rvalue['class'] = empty($user_classes[$rvalue[$defaultKey]]['classname'])?'暂无班级':$user_classes[$rvalue[$defaultKey]]['classname'];
                    }
                }
            }
        }
        return $result;
    }
    /**
     * 获取指定用户发表原创文章的数量
     * @param $crid 网校ID
     * @param $uid 指定用户ID
     * @return $count
     */
    public function myarticleCountAction() {
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $count = $this->article->myarticleCount($param);
        if(empty($count)){
            return 0;
        }else{
            return $count;
        }
    }
    /**
     * 获取指定用户评论的数量（包含视频和非视频课件、原创文章评论）
     * @param $crid 网校ID
     * @param $uid 指定用户ID
     * @return $count
     */
    public function reviewCountAction() {
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $newscount = $this->article->reviewCount($param);   //原创文章评论数量
        $reviewcount = $this->review->getAllReviewCount($param);//视频和非视频课件评论数量
        $newscount['count'] = !empty($newscount['count']) ? $newscount['count'] : 0;
        $reviewcount['count'] = !empty($reviewcount['count']) ? $reviewcount['count'] : 0;
        return $newscount['count'] + $reviewcount['count'];
    }
}
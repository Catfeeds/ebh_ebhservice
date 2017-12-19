<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ForumsSubjectReplyController extends Controller{
    public $forumSubjectReplyModel;
    public function init(){
        parent::init();
        $this->forumSubjectReplyModel = new ForumsSubjectReplyModel();
    }
    public function parameterRules(){
        return array(
            'replyAction'   =>  array(
                'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),//需要回复的帖子ID
                'prid'  =>  array('name'=>'prid','type'=>'int','default'=>0),//需要评论的父级ID
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//发布评论的用户ID
                'touid'  =>  array('name'=>'touid','type'=>'int','default'=>0),//回复的用户ID
                'content'  =>  array('name'=>'content','require'=>true),//评论内容
                'imgs'  =>  array('name'=>'imgs','default'=>'')
            ),
            'listAction'   =>  array(
                'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int')
            ),
            'sonListAction' =>  array(
                'rid'  =>  array('name'=>'rid','require'=>true,'type'=>'int')
            ),
            'delSubjectReplyAction' => array(
                'rid' => array('name'=>'rid','require'=>true,'type'=>'int')
            )
        );
    }

    /**
     * 新增评论
     * @return array
     */
    public function replyAction(){
        $parameters['sid'] = $this->sid;
        $parameters['prid'] = $this->prid;
        $parameters['uid'] = $this->uid;
        $parameters['touid'] = $this->touid;
        $parameters['content'] = $this->content;
        $parameters['imgs'] = $this->imgs;


        $result = $this->forumSubjectReplyModel->addReply($parameters);

        return array(
            'status'    =>  $result ? true : false,
            'id'        =>  $result
        );
    }

    /**
     * 获取指定评论的回复列表
     */
    public function sonListAction(){
        $parameters['where']['r.prid'] = ' r.prid='.$this->rid;
        $total = $this->forumSubjectReplyModel->getCount($parameters);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->forumSubjectReplyModel->getList($parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 获取评论列表
     * @return array
     */
    public function listAction(){
        $parameters = array();
        $parameters['where']['r.sid'] = ' r.sid='.$this->sid;
        $parameters['where']['r.prid'] = ' r.prid=0';
        $total = $this->forumSubjectReplyModel->getCount($parameters);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->forumSubjectReplyModel->getListBySidOnRecUrsion(0,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }
    /**
     * 删除帖子回复   
     */
    public function delSubjectReplyAction(){
        $res = $this->forumSubjectReplyModel->subjectReplyDelete($this->rid);
        return $res ? true : false;
    }
}
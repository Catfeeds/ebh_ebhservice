<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ForumsSubjectController extends Controller{
    public $forumSubjectModel;
    public function init(){
        parent::init();
        $this->forumSubjectModel = new ForumsSubjectModel();
    }

    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'cate_id'  =>  array('name'=>'cate_id','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'title'  =>  array('name'=>'title','require'=>true),
                'imgs'  =>  array('name'=>'imgs','default'=>''),
                'content'  =>  array('name'=>'content','require'=>true)
            ),
            'getSubjectAction'   =>  array(
                'crid'  =>  array('name'=>'crid','type'=>'int','default'=>0),//网校ID
                'fid'  =>  array('name'=>'fid','type'=>'int','default'=>0),//社区ID 不传获取当前网校所有帖子
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),//加载会员ID,不传加载所有会员
                'is_hot'  =>  array('name'=>'is_hot','type'=>'int'),//1：加载热帖 0加载普通帖 不传加载所有
                'is_del'  =>  array('name'=>'is_del','type'=>'int'),//1：加载已删除帖子 0加载未删除帖子 不传加载所有
                'cate_id'  =>  array('name'=>'cate_id','type'=>'int'),//加载分类ID 不传加载所有分类
                'keyword'  =>  array('name'=>'keyword','default'=>''),
                'module'      =>  array('name'=>'module')
            ),
            'getDetailAction'   =>  array(
                'sid'  =>  array('name'=>'sid','type'=>'int','default'=>0),//主题ID
            ),
            'setSubjectHotAction'   =>  array(
                'sid'  =>  array('name'=>'sid','type'=>'int','require'=>true),//主题ID
                'is_hot'  =>  array('name'=>'is_hot','type'=>'int','require'=>true),//1:热帖 0非热帖
            ),
            'setSubjectTopAction'   =>  array(
                'sid'  =>  array('name'=>'sid','type'=>'int','require'=>true),//主题ID
                'is_top'  =>  array('name'=>'is_top','type'=>'int','require'=>true),//1:置顶 0取消置顶
            ),
            'delSubjectAction'   =>  array(
                'sid'  =>  array('name'=>'sid','type'=>'int','require'=>true),//主题ID
            ),
            'searchSubjectAction'  =>  array(
                'fid'   =>  array('name'=>'fid','type'=>'int','require'=>true),//社区ID
                'cate_id'   =>  array('name'=>'cate_id','type'=>'int','require'=>true),
                'keyword'   =>  array('name'=>'keyword','type'=>'string',),
            ),
            'setViewCountAction'  =>  array(
                'sid'   =>  array('name'=>'sid','type'=>'int','require'=>true),
                'view_count'  => array('name'=>'view_count','type'=>'int','require'=>true),
            )
        );
    }

    /**
     * 新增帖子
     */
    public function addAction(){
        $parameters['fid'] = $this->fid;
        $parameters['uid'] = $this->uid;
        $parameters['cate_id'] = $this->cate_id;
        $parameters['crid'] = $this->crid;
        $parameters['title'] = $this->title;
        $parameters['imgs'] = $this->imgs;
        $parameters['content'] = $this->content;


        $result = $this->forumSubjectModel->addSubject($parameters);

        return array(
            'status'    =>  $result ? true : false,
            'id'        =>  $result
        );
    }

    /**
     * 读取最新帖子
     * @return array
     */
    public function getSubjectAction(){
        if($this->crid > 0){
            $parameters['where'][] = ' s.crid='.$this->crid;
        }
        if($this->fid > 0){
            $parameters['where'][] = ' s.fid='.$this->fid;
        }

        if($this->uid > 0){
            $parameters['where'][] = 's.uid='.$this->uid;
        }

        if($this->is_hot !== null){
            $ishot = $this->is_hot ? 1 : 0;
            $parameters['where'][] = 's.is_hot='.$ishot;
        }

        if($this->is_del !== null){
            $is_del = $this->is_del ? 1 : 0;
            $parameters['where'][] = 's.is_del='.$is_del;
        }
        if($this->cate_id !== null){

            $parameters['where'][] = 's.cate_id='.$this->cate_id;
        }
        if($this->keyword != ''){
            $parameters['keyword'] = $this->keyword;
        }
        $total = $this->forumSubjectModel->getListCount($parameters);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        if($this->module == 'back'){
            $parameters['order'] = ' s.dateline desc';
        }else{
            $parameters['order'] = ' s.is_top desc,s.dateline desc';
        }
        
        $list = $this->forumSubjectModel->getList($parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 获取帖子详情信息
     * @return bool
     */
    public function getDetailAction(){
        $result = $this->forumSubjectModel->getDetail($this->sid);
        return $result ? $result : false;
    }


    /**
     * 设置热帖状态
     * @return array
     */
    public function setSubjectHotAction(){
        if($this->is_hot > 0){
            $is_hot = 1;
        }else{
            $is_hot = 0;
        }
        $rs = $this->forumSubjectModel->setSubjectHot($this->sid,$is_hot);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * 设置帖子置顶状态
     * @return array
     */
    public function setSubjectTopAction(){
        if($this->is_top > 0){
            $is_top = 1;
        }else{
            $is_top = 0;
        }
        $rs = $this->forumSubjectModel->setSubjectTop($this->sid,$is_top);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * 删除指定帖子
     * @return array
     */
    public function delSubjectAction(){
        $rs = $this->forumSubjectModel->subjectDelete($this->sid);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * [搜索帖子]
     * @return [array]
     */
    public function searchSubjectAction(){
        if($this->fid > 0){
            $parameters['where'][] = ' s.fid='.$this->fid;
        }
        if($this->cate_id > 0){
            $parameters['where'][] = ' s.cate_id='.$this->cate_id;
        }
        
        $parameters['where'][] = " s.title like '%$this->keyword%'";
        $result = $this->forumSubjectModel->searchSubject($parameters);
        return $result ? $result : false;
    }

    /**
     * [设置帖子浏览量]
     * @return [bool]
     */
    public function setViewCountAction(){
        $result = $this->forumSubjectModel->setViewCount($this->sid,$this->view_count);
        return $result ? true : false;
    }
}
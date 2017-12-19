<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 社区模型API
 */
class ForumsController extends Controller{
    public $forumModel;
    public function init(){
        parent::init();
        $this->forumModel = new ForumsModel();
    }
    //参数规则
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int')
            ),
            'addAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'name'  =>  array('name'=>'name','require'=>true),
                'image' =>  array('name'=>'image','default'=>''),
                'notice'    =>  array('name'=>'notice','default'=>''),
                'manager'   =>  array('name'=>'manager','default'=>''),
                'category'  =>  array('name'=>'category','type'=>'array','default'=>array())
            ),
            'editAction'    =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'name'  =>  array('name'=>'name','require'=>true),
                'image' =>  array('name'=>'image','default'=>''),
                'notice'    =>  array('name'=>'notice','default'=>''),
                'manager'   =>  array('name'=>'manager','default'=>''),
                'category'  =>  array('name'=>'category','type'=>'array','default'=>array()),
                'categorys'  =>  array('name'=>'categorys','type'=>'array','default'=>array())
            ),
            'detailAction'    =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),
            ),
            'sortAction'    =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'sort'   =>  array('name'=>'sort','require'=>true,'type'=>'int')
            ),
            'setChatroomAction' =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'is_open'   =>  array('name'=>'is_open','require'=>true,'type'=>'int')
            ),
            'frontAllForumListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),
                'keyword'  =>  array('name'=>'keyword','default'=>'')
            ),
            'frontMyForumListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),
                'keyword'  =>  array('name'=>'keyword','default'=>'')
            ),
            'getCategoryAction' =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),
            ),
            'joinForumAction'   =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),//要加入社区的ID
                'uid'   =>  array('name'=>'uid','require'=>true,'type'=>'int'),//要加入社区的UID
            ),
            'getJoinForumInfoAction'   =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),//社区的ID
                'uid'   =>  array('name'=>'uid','require'=>true,'type'=>'int'),//UID
            ),
            'cancelJoinForumAction'   =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),//社区的ID
                'uid'   =>  array('name'=>'uid','require'=>true,'type'=>'int'),//UID
            ),
            'setCloseForumAction'  =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),//社区的ID
                'is_close'   =>  array('name'=>'is_close','require'=>true,'type'=>'int'),//关闭状态 0 关闭  1打开
            ),
            'delForumAction'    =>  array(
                'fid'   =>  array('name'=>'fid','require'=>true,'type'=>'int'),//社区的ID
            ),
            'categorySortAction' => array(
                'cate_id'   => array('name'=>'cate_id','require'=>true,'type'=>'int'),//帖子分类ID
                'other_cate_id' => array('name'=>'other_cate_id','require'=>true,'type'=>'int'),//与之交换位置的分类ID
            ),
            'delCategoryAction'  => array(
                'cate_id'   =>  array('name'=>'cate_id','type'=>'int'),
            ),
            'delSelfSubjectAction' => array(
                'sid'   =>  array('name'=>'sid','type'=>'int','require'=>true),
            )
        );
    }

    /**
     * 设置聊天室状态
     * @return array
     */
    public function setChatroomAction(){
        $result = $this->forumModel->forumChatroom($this->fid,$this->is_open);
        return array(
            'status'    =>  $result ? true :false
        );
    }

    /**
     * 社区社区排序
     * @return array
     */
    public function sortAction(){
        $result = $this->forumModel->forumSort($this->fid,$this->sort);
        return array(
            'status' => $result ? true :false
        );
    }

    /**
     * 获取社区详情
     * @return mixed
     */
    public function detailAction(){
        $result = $this->forumModel->getForumByFid($this->fid);
        return $result;
    }

    /**
     * 添加社区
     * @return mixed
     */
    public function addAction(){

        $parameters['crid'] = $this->crid;
        $parameters['name'] = $this->name;
        $parameters['image'] = $this->image;
        $parameters['notice'] = $this->notice;
        $parameters['manager'] = $this->manager;
        $parameters['category'] = $this->category;

        $result = $this->forumModel->addForum($parameters);

        return array(
            'status'    =>  $result ? true : false,
            'id'        =>  $result
        );
    }

    /**
     * 编辑社区
     * @return array
     */
    public function editAction(){



        $parameters['name'] = $this->name;
        $parameters['image'] = $this->image;
        $parameters['notice'] = $this->notice;
        $rs = $this->forumModel->editForum($this->fid,$parameters);
        if($rs === false){
            return array(
                'status'    =>  false
            );
        }
        //获取数据库中的分类
        $nowCategorys = $this->forumModel->getCategoryByFid($this->fid);
        //获取到可能需要修改的分类后
        foreach($this->categorys as $key=>$category){
            if($category != '公告' && $category !='其他'){
                foreach($nowCategorys as $nowCategory){
                    if($nowCategory['category_name'] != $category && $key == $nowCategory['cate_id']){
                        $needModifyCategory[$key] = $category;
                    }
                }
            }
            $dbCategoryKey[] = $key;
        }
        //删除需要删除的分类
        $this->forumModel->delCategory($this->fid,$dbCategoryKey,'not');

        //修改需要修改的分类
        $this->forumModel->editCategoryName($needModifyCategory);

        //添加新增的分类
        $this->forumModel->addCategory($this->fid,$this->category);


        //删除需要删除的版主
        $this->forumModel->delManager($this->fid,explode(',',$this->manager));
        //添加新增的版主

        $manager = explode(',',$this->manager);
        $nowManagers = $this->forumModel->getManagerByFid($this->fid);
        if(!$nowManagers){
            $this->forumModel->addManager($this->fid,$manager);
        }else{
            foreach($nowManagers as $nowManager){
            $dbManagers[] = $nowManager['uid'];
            }
            $this->forumModel->addManager($this->fid,array_diff($manager,$dbManagers));
        }
        return array(
            'status'    =>  true
        );

    }

    /**
     * 获取社区列表
     * @return array
     */
    public function listAction(){
        $total = $this->forumModel->getForumCountByCrid($this->crid);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->forumModel->getForumListByCrid($this->crid,$parameters);
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );

    }





    /***前台数据接口***/

    /**
     * 获取全部社区
     * @return array
     */
    public function frontAllForumListAction(){
        $parameters = array();
        $parameters['uid'] = intval($this->uid);
        if($this->keyword != ''){
            $parameters['keyword'] = $this->keyword;
        }
        $total = $this->forumModel->getAllForumCountByCrid($this->crid,$parameters);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->forumModel->getAllForumListByCrid($this->crid,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 获取我加入的社区
     * @return array
     */
    public function frontMyForumListAction(){
        $parameters = array();
        $parameters['where'][] = ' u.uid='.$this->uid;
        $parameters['where'][] = ' u.is_follow=1';
        if($this->keyword != ''){
            $parameters['keyword'] = $this->keyword;
        }
        $parameters['uid'] = intval($this->uid);
        $total = $this->forumModel->getAllForumCountByCrid($this->crid,$parameters);
        $pageClass  = new Page($total,getConfig('system.page.listRows'));
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;





        $list = $this->forumModel->getAllForumListByCrid($this->crid,$parameters);
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 获取指定社区中所有分类
     * @return mixed
     */
    public function getCategoryAction(){
        $result = $this->forumModel->getCategoryByFid($this->fid);

        return $result;
    }


    /**
     * 用户加入社区
     * @return array
     */
    public function joinForumAction(){
        $rs = $this->forumModel->joinForum($this->fid,$this->uid);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * 取消加入社区状态
     * @return array
     */
    public function cancelJoinForumAction(){
        $rs = $this->forumModel->cancelJoinForum($this->fid,$this->uid);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * 获取用户加入社区的状态
     * @return array
     */
    public function getJoinForumInfoAction(){
        $result = $this->forumModel->getJoinInfo($this->fid,$this->uid);
        return $result ? $result : false;
    }


    /**
     * 设置社区关闭状态
     * @return array
     */
    public function setCloseForumAction(){
        if($this->is_close > 0){
            $is_close = 1;
        }else{
            $is_close = 0;
        }
        $rs = $this->forumModel->forumClose($this->fid,$is_close);
        return array(
            'status'    =>  $rs ? true : false
        );
    }

    /**
     * 删除社区
     * @return array
     */
    public function delForumAction(){
        $rs = $this->forumModel->forumDelete($this->fid);
        return array(
            'status'    =>  $rs ? true : false
        );
    }
    /**
     * 帖子分类上下移动
     * @return [bool] [布尔]
     */
    public function categorySortAction(){
        $result = $this->forumModel->categorySort($this->cate_id,$this->other_cate_id);
        return $result ? true : false;
    }

    /**
     * [删除社区分类]
     * @return [type] [description]
     */
    public function delCategoryAction(){
        $result = $this->forumModel->categoryDel($this->cate_id);
        return $result ? true : false;
    }

    /**
     * 楼主删除自己的帖子
     */
    public function delSelfSubjectAction(){
        return $this->forumModel->delSelfSubject($this->sid);
    }
}
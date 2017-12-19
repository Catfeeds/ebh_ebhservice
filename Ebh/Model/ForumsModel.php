<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ForumsModel{


    /**
     * 获取社区列表
     * @param $crid
     * @param array $parameters
     * @return mixed
     */
    public function getForumListByCrid($crid,$parameters = array()){
        $sql = "select fid,name,sort,open_chatroom,follow_count,subject_count,dateline,is_close,(select group_concat(realname) from ebh_users where uid in (select uid from ebh_forums_managers where fid=ebh_forums.fid)) as manager from ebh_forums where is_del=0 and crid={$crid} order by sort asc,fid asc";
        if(isset($parameters['limit'])){
            $sql .= ' LIMIT '.$parameters['limit'];
        }else{
            $sql .= ' LIMIT 1000 ';
        }
        $list =  Ebh()->db->query($sql)->list_array();
        return $list;
    }


    /**
     * 获取前台社区数
     * @param $crid
     * @param array $parameters
     * @return int
     */
    public function getAllForumCountByCrid($crid,$parameters = array()){
        $parameters['where'][] = ' f.crid='.$crid;
        $parameters['where'][] = ' f.is_del=0';
        $parameters['where'][] = ' f.is_close=0';
        $sql = "select count(f.fid) as count from ebh_forums f left join ebh_forums_follows u on u.fid=f.fid and u.uid = ".$parameters['uid'];
        if (!empty($parameters['keyword'])){
            $parameters['where'][] = ' name like \'%' .Ebh()->db->escape_str($parameters['keyword']) .'%\'';
        }



        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }


        $count = Ebh()->db->query($sql)->row_array();

        return intval($count['count']);

    }

    /**
     * 获取前台社区列表
     * @param $crid
     * @param array $parameters
     * @return mixed
     */
    public function getAllForumListByCrid($crid,$parameters = array()){
        $parameters['where'][] = ' f.crid='.$crid;
        $parameters['where'][] = ' f.is_del=0';
        $parameters['where'][] = ' f.is_close=0';
        // test
        $sql = "select f.fid,f.sid,f.name,f.image,f.sort,f.open_chatroom,f.follow_count,f.subject_count,f.dateline,f.notice,ifnull(u.is_follow,0) as is_follow,(select group_concat(realname) from ebh_users where uid in (select uid from ebh_forums_managers where fid=f.fid)) as manager from ebh_forums f left join ebh_forums_follows u on u.fid=f.fid and u.uid = ".$parameters['uid'];

        if (!empty($parameters['keyword'])){
            $parameters['where'][] = ' name like \'%' .Ebh()->db->escape_str($parameters['keyword']) .'%\'';
        }
        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }
        if (!empty($parameters['order'])){
            $sql .= ' order by '.$parameters['order'];
        }


        if(isset($parameters['limit'])){
            $sql .= ' LIMIT '.$parameters['limit'];
        }else{
            $sql .= ' LIMIT 1000 ';
        }

        $list =  Ebh()->db->query($sql)->list_array();
        if(count($list) > 0){
            foreach($list as $key=>$value){
                //查看缓存中是否有社区所有帖子ID信息
                $sidList = Ebh()->cache->get('forum_sid_list_'.$value['fid']);
                if($sidList === null){
                    $sidList = Ebh()->db->query("select sid from ebh_forums_subjects where fid={$value['fid']} and is_del=0")->list_array();
                    Ebh()->cache->set('forum_sid_list_'.$value['fid'],$sidList,600);
                }
                $list[$key]['sidList'] = $sidList;

                //查看缓存中是否有热帖信息
                $hotSubject = Ebh()->cache->get('forum_hot_subject_'.$value['fid']);
                if($hotSubject === null){
                    $hotSubject = Ebh()->db->query("select sid,title from ebh_forums_subjects where fid={$value['fid']} and is_hot=1 order by dateline desc LIMIT 3")->list_array();
                    Ebh()->cache->set('forum_hot_subject_'.$value['fid'],$hotSubject,600);
                }
                $list[$key]['hot_subject'] = $hotSubject;

                $lastSubject =  Ebh()->cache->get('forum_last_subject_'.$value['sid']);
                if($lastSubject === null){
                    //$lastSubject = Ebh()->db->query("select s.sid,s.title,s.dateline,s.uid,u.realname,u.username from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid where s.fid={$value['fid']} order by sid desc LIMIT 1")->row_array();
                    $sid = intval($value['sid']);
                    $lastSubject = Ebh()->db->query("select s.sid,s.title,s.dateline,u.realname,u.username from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid where s.sid=$sid")->row_array();
                    $lastSubject = $lastSubject ? $lastSubject :array();
                    Ebh()->cache->set('forum_last_subject_'.$value['sid'],$lastSubject,600);
                }
                $list[$key]['last_subject'] = $lastSubject;


                $forumCount = Ebh()->cache->get('forum_count_'.$value['fid']);
                if($forumCount === null){
                    $forumCount = Ebh()->db->query("select ifnull(sum(reply_count),0) as reply_count,ifnull(sum(view_count),0) as view_count from ebh_forums_subjects where fid=".$value['fid'])->row_array();

                    Ebh()->cache->set('forum_count_'.$value['fid'],$forumCount,600);
                }
                $list[$key]['all_reply_count'] = $forumCount['reply_count'];
                $list[$key]['all_view_count'] = $forumCount['view_count'];
            }
        }
        return $list;
    }


    /**
     * 根据Crid获取社区数
     * @param $crid
     * @return mixed
     */
    public function getForumCountByCrid($crid){
        $sql = "SELECT count(fid) as count FROM ebh_forums WHERE is_del=0 AND crid={$crid}";

        $count = Ebh()->db->query($sql)->row_array();

        return intval($count['count']);
    }

    /**
     * 获取指定ID的社区信息
     * @param $fid
     * @return bool
     */
    public function getForumByFid($fid){
        $sql = 'select fid,crid,name,image,notice,sort,open_chatroom,follow_count,subject_count,dateline,is_close,is_del from ebh_forums where is_del=0 and fid='.$fid;
        $rs = Ebh()->db->query($sql)->row_array();
        if(!$rs){
            return false;
        }
        $sql = 'select cate_id,category_name,sort,only_manager from ebh_forums_categorys where fid='.$fid.' order by sort asc,cate_id asc';
        $rs['category'] = Ebh()->db->query($sql)->list_array();

        $sql = 'select uid,realname,username from ebh_users where uid in (select uid from ebh_forums_managers where fid='.$fid.')';
        $rs['manager'] = Ebh()->db->query($sql)->list_array();
        return $rs;
    }

    /**
     * 修改社区
     * @param $fid
     * @param $param
     * @return mixed
     */
    public function editForum($fid,$param){
        if(!empty($param['name'])){
            $data['name'] = $param['name'];
        }
        if(!empty($param['image'])){
            $data['image'] = $param['image'];
        }
        if(!empty($param['notice'])){
            $data['notice'] = $param['notice'];
        }
        if(!empty($param['sort'])){
            $data['sort'] = $param['sort'];
        }
        if(!empty($param['open_chatroom'])){
            $data['open_chatroom'] = $param['open_chatroom'];
        }
        return Ebh()->db->update('ebh_forums',$data,array('fid'=>$fid));
    }

    /**
     * @param $param
     * 添加社区
     */
    public function addForum($param){
        if(!empty($param['crid'])){
            $data['crid'] = $param['crid'];
        }
        if(!empty($param['name'])){
            $data['name'] = $param['name'];
        }
        if(!empty($param['image'])){
            $data['image'] = $param['image'];
        }
        if(!empty($param['notice'])){
            $data['notice'] = $param['notice'];
        }
        if(!empty($param['sort'])){
            $data['sort'] = $param['sort'];
        }
        if(!empty($param['open_chatroom'])){
            $data['open_chatroom'] = $param['open_chatroom'];
        }
        /*if(!empty($param['manager'])){
            $data['follow_count'] = count($param['manager']);
        }*/
        $data['dateline'] = time();
        $id = Ebh()->db->insert('ebh_forums',$data);
        $lastSort = Ebh()->db->query('select max(sort) as sort from ebh_forums_categorys where fid='.$id)->row_array();
        $lastSort = empty($lastSort['sort']) ? 0 : $lastSort['sort'];

        if($id > 0){
            //添加版主
            if(!empty($param['manager'])){
                $managers = explode(',',$param['manager']);
                $sql = 'insert into ebh_forums_managers (fid,uid) values';
                $followSql = 'insert into ebh_forums_follows (uid,fid,gag_end,dateline) values';
                $values = array();
                $followValues = array();
                $time = time();
                foreach($managers as $manager){
                    $values[] = " ({$id},{$manager})";
                    $followValues[] = " ({$manager},$id,0,{$time})";
                }
                $sql .= implode(',',$values);
                $followSql .= implode(',',$followValues);
                Ebh()->db->query($sql);
                //Ebh()->db->query($followSql);
            }
            //添加分类
            $sql = 'insert into ebh_forums_categorys (fid,category_name,sort,dateline,only_manager) values';
            $values = array();
            $time = time();
            if(isset($param['category']) && count($param['category']) > 0){
                foreach($param['category'] as $category){
                    $lastSort++;
                    $values[] = " ({$id},'{$category}',$lastSort,{$time},0)";
                }
            }
            $values[] = " ({$id},'公告',100,{$time},1)";
            $values[] = " ({$id},'其他',100,{$time},0)";
            $sql .= implode(',',$values);
            Ebh()->db->query($sql);




            return $id;
        }else{
            return false;
        }
    }

    /**
     * 设置社区排序
     * @param $fid
     * @param $sort
     * @return mixed
     */
    public function forumSort($fid,$sort){
        $data['sort'] = $sort;
        return Ebh()->db->update('ebh_forums',$data,array('fid'=>$fid));

    }

    /**
     * 设置聊天室状态
     * @param $fid
     * @param $is_open
     * @return mixed
     */
    public function forumChatroom($fid,$is_open){
        $data['open_chatroom'] = $is_open;
        return Ebh()->db->update('ebh_forums',$data,array('fid'=>$fid));
    }

    /**
     * 设置社区关闭状态
     * @param $fid
     * @param $is_close
     * @return mixed
     *
     */
    public function forumClose($fid,$is_close){
        $data['is_close'] = $is_close;
        return Ebh()->db->update('ebh_forums',$data,array('fid'=>$fid));
    }

    /**
     * 删除指定的社区
     * @param $fid
     * @return mixed
     */
    public function forumDelete($fid){
        $data['is_del'] = 1;
        return Ebh()->db->update('ebh_forums',$data,array('fid'=>$fid));
    }

    /**
     * 向指定社区中添加分类
     * @param $fid
     * @param $categorys
     * @return bool
     */
    public function addCategory($fid,$categorys){
        if(is_array($categorys) && count($categorys) > 0){
            $lastSort = Ebh()->db->query('select max(sort) as sort from ebh_forums_categorys where fid='.$fid . ' and sort != 100' )->row_array();
            $lastSort = empty($lastSort['sort']) ? 0 : $lastSort['sort'];
            $sql = 'insert into ebh_forums_categorys (fid,category_name,sort,dateline) values';
            $values = array();
            $time = time();
            foreach($categorys as $category){
                $lastSort++;
                $values[] = " ({$fid},'{$category}',$lastSort,{$time})";
            }
            $sql .= implode(',',$values);
            Ebh()->db->query($sql);
        }


    }

    /**
     * 删除指定的分类
     * @param $ids
     * @param string $type
     * @return mixed
     */
    public function delCategory($fid,$ids,$type = 'in'){
        if(is_array($ids)){
            if($type == 'in'){
                $sql = 'delete from ebh_forums_categorys where cate_id in ('.implode(',',$ids).') and fid='.$fid;
            }else{
                $sql = 'delete from ebh_forums_categorys where cate_id not in ('.implode(',',$ids).') and fid='.$fid;
            }
        }else{
            if($type == 'in'){
                $sql = 'delete from ebh_forums_categorys where cate_id ='.$ids;
            }else{
                $sql = 'delete from ebh_forums_categorys where cate_id <>'.$ids;
            }
        }

        return Ebh()->db->query($sql);
    }

    /**
     * 通过fid获取分类
     * @param $fid
     * @return mixed
     */
    public function getCategoryByFid($fid){
        $sql = 'select cate_id,category_name,sort,subject_count,only_manager from ebh_forums_categorys where fid='.$fid.' order by sort asc,cate_id asc';
        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 修改分类名称
     * @param $categorys
     */
    public function editCategoryName($categorys){
        //获取到可能需要修改的分类后
        foreach($categorys as $key=>$category){
            Ebh()->db->update('ebh_forums_categorys',array('category_name'=>$category),array('cate_id'=>$key));
        }
    }


    /**
     * 删除指定社区的版主
     * @param $fid
     * @param $ids
     * @param string $type
     * @return mixed
     */
    public function delManager($fid,$ids,$type = 'in'){
        $sql = 'select uid from ebh_forums_managers where fid='.$fid;
        $list_uid = Ebh()->db->query($sql)->list_array();
        foreach($list_uid as $uid){
            if(!in_array($uid['uid'],$ids)){
                $del_uid[] = $uid['uid'];
            }
        }
        if($del_uid){
            foreach($del_uid as $uid){
                //$sql = 'delete from ebh_forums_managers where uid in $del_uid';
                Ebh()->db->delete('ebh_forums_managers',array('uid'=>$uid));
            }
        }
    }

    /**
     * 获取指定社区版主
     * @param $fid
     * @return mixed
     */
    public function getManagerByFid($fid){
        $sql = 'select uid from ebh_forums_managers where fid='.$fid;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 添加指定社区的版主
     * @param $fid
     * @param $managers
     * @return mixed
     */
    public function addManager($fid,$managers){
        if (is_array($managers)) {
            $managers = array_map(function($manager) {
                return (int) $manager;
            }, $managers);
        }
        $fid = (int) $fid;
        $sql = 'insert into ebh_forums_managers (fid,uid) values';
        $followSql = 'insert into ebh_forums_follows (uid,fid,gag_end,dateline) values';
        $values = array();
        $followValues = array();
        $time = time();
        foreach($managers as $manager){
            $values[] = " ({$fid},{$manager})";
            $followValues[] = " ({$manager},$fid,0,{$time})";
        }
        $sql .= implode(',',$values);
        $followSql .= implode(',',$followValues);
        //不进行自动加入
        //Ebh()->db->query($followSql);
        return Ebh()->db->query($sql);
    }

    /**
     * 加入社区
     * @param $fid
     * @param $uid
     * @return mixed
     */
    public function joinForum($fid,$uid){
        $sql = 'select uid,fid,gag_end,dateline from ebh_forums_follows where uid='.$uid.' and fid='.$fid;
        if(!Ebh()->db->query($sql)->row_array()){
            $data['uid'] = $uid;
            $data['fid'] = $fid;
            $data['gag_end'] = 0;
            $data['dateline'] = time();
            Ebh()->db->insert('ebh_forums_follows',$data);
        }else{
            Ebh()->db->update('ebh_forums_follows',array('is_follow'=>1),array('uid'=>$uid,'fid'=>$fid));
        }
        //更新计数

        Ebh()->db->query('update ebh_forums set follow_count=follow_count+1 where fid='.$fid);
        return true;
    }

    /**
     * 取消加入社区状态
     * @param $fid
     * @param $uid
     * @return mixed
     */
    public function cancelJoinForum($fid,$uid){
        $rs =  Ebh()->db->update('ebh_forums_follows',array('is_follow'=>0),array('uid'=>$uid,'fid'=>$fid));
        if($rs){
            Ebh()->db->query('update ebh_forums set follow_count=follow_count-1 where fid='.$fid);
        }
        return $rs;
    }

    /**
     * 获取用户加入社区的信息
     * @param $fid
     * @param $uid
     * @return bool
     */
    public function getJoinInfo($fid,$uid){
        $sql = 'select uid,fid,gag_end,dateline from ebh_forums_follows where uid='.$uid.' and fid='.$fid.' and is_follow=1';
        $result = Ebh()->db->query($sql)->row_array();
        return $result ? $result : false;
    }
    /**
     * 帖子分类上下移动
     * @param  [int] $fid          [社区ID]
     * @param  [int] $cate_id      [社区帖子分类ID]
     * @param  [int] $current_sort [当前帖子sort]
     * @param  [int] $other_sort   [上一个或下一个帖子sort]
     * @return [bool]              [布尔]
     */
    public function categorySort($cate_id,$other_cate_id){
        //将当前帖子sort和与之交换位置的帖子的sort值交换 实现重新排序
        $sql = 'select `sort` from ebh_forums_categorys where cate_id='.$cate_id;
        $sql2 = 'select `sort` from ebh_forums_categorys where cate_id='.$other_cate_id;
        $current_sort = Ebh()->db->query($sql)->row_array();
        $other_sort = Ebh()->db->query($sql2)->row_array();
        if($current_sort != $other_sort){
            $res1 = Ebh()->db->update('ebh_forums_categorys',array('sort'=>$other_sort['sort']),array('cate_id'=>$cate_id));
            $res2 = Ebh()->db->update('ebh_forums_categorys',array('sort'=>$current_sort['sort']),array('cate_id'=>$other_cate_id));
            if($res1 && $res2){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
        
    }

    /**
     * [删除社区分类  分类下面有帖子时  社区不允许删除]
     * @param  [int] $cate_id [分类ID]
     * @return [bool]
     */
    public function categoryDel($cate_id){
        //查询该分类下有无帖子
        $sql = 'select sid from ebh_forums_subjects where cate_id='.$cate_id;
        $result = Ebh()->db->query($sql)->row_array();
        if($result){
            return false;
        }else{
            $affect_row = Ebh()->db->delete('ebh_forums_categorys',array('cate_id'=>$cate_id));
            if($affect_row){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * 楼主删除自己的帖子
     * @param  $id [帖子ID]
     */
    public function delSelfSubject($sid){
        $params['is_del'] = 1;
        $where['sid'] = intval($sid);
        $subjectaffect = Ebh()->db->update('ebh_forums_subjects',$params,$where);
        //判断楼主发布的帖子是否存在回帖 存在则删除
        $sql = "select count(1) as count from ebh_forums_subject_replys where sid=$sid";
        $count = Ebh()->db->query($sql)->row_array();
        if($count['count'] > 0){
            $replyaffect = Ebh()->db->delete('ebh_forums_subject_replys',$where);
            if($subjectaffect && $replyaffect){
                return true;
            }else{
                return false;
            }
        }else{
            return $subjectaffect;
        }
        
    }
}
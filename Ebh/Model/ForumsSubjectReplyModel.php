<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ForumsSubjectReplyModel{
    /**
     * 添加评论
     * @param $parameters
     * @return bool
     */
    public function addReply($parameters){
        $data['sid'] = $parameters['sid'];
        $data['uid'] = $parameters['uid'];
        $data['prid'] = $parameters['prid'];
        $data['touid'] = $parameters['touid'];
        $data['content'] = $parameters['content'];
        $data['imgs'] = $parameters['imgs'];
        $data['dateline'] = time();
        $id = Ebh()->db->insert('ebh_forums_subject_replys',$data);
        if($id > 0){
            //更新回帖量
            Ebh()->db->query('update ebh_forums_subjects set reply_count=reply_count+1 where sid='.$data['sid']);
            //更新帖子最后回复人
            $user = Ebh()->db->query('select uid,realname,username from ebh_users where uid='.$parameters['uid'])->row_array();
            if($user){
                $time = time();
                Ebh()->db->update('ebh_forums_subjects',array('last_reply_name'=>$user['realname'],'last_reply_time'=>$time),array('sid'=>$parameters['sid']));
            }

            return $id;
        }else{
            return false;
        }
    }

    /**
     * 获取评论数
     * @param array $parameters
     * @return int
     */
    public function getCount($parameters = array()){
        $sql = "select count(r.rid) as count from ebh_forums_subject_replys r";
        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }
        $count = Ebh()->db->query($sql)->row_array();

        return intval($count['count']);
    }


    public function getList($parameters = array()){
        //$sql = 'select s.sid,s.uid,s.cate_id,s.title,s.imgs,s.reply_count,s.view_count,s.is_hot,s.is_top,s.dateline,s.is_del,s.del_reason,u.username,u.face,u.realname,cate.category_name,con.content,f.name as forum_name from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid left join ebh_forums_categorys cate on cate.cate_id=s.cate_id left join ebh_forums_contents con on con.sid=s.sid left join ebh_forums f on f.fid=s.fid';
        $sql = 'select r.rid,r.sid,r.prid,r.uid,r.touid,r.content,r.imgs,r.dateline,u.username,u.realname,u.face,u.sex,u.groupid,ifnull(tu.uid,0) as to_uid,ifnull(tu.username,\'\') as to_username,ifnull(tu.realname,\'\') as to_realname,(select count(sid) from ebh_forums_subject_replys where prid=r.rid ) as reply_count from ebh_forums_subject_replys r left join ebh_users u on u.uid=r.uid left join ebh_users tu on tu.uid=r.touid';
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
        return $list;

    }

    /**
     * 递归方式获取评论列表以及子列表
     * @param int $parent_id
     * @param $parameters
     * @param array $result
     * @return array
     */
    public function getListBySidOnRecUrsion($parent_id = 0,$parameters,&$result = array()){
        $parameters['where']['r.prid'] = ' r.prid='.$parent_id;
        if($parent_id > 0){
            $parameters['limit'] = '5';
        }
        $arr = $this->getList($parameters);

        if(empty($arr)){
            return array();
        }

        foreach($arr as $reply){
            $thisArr=&$result[];
            $reply['children'] = $this->getListBySidOnRecUrsion($reply['rid'],$parameters,$thisArr);
            $thisArr = $reply;
        }

        return $result;
    }
    /**
     * 删除帖子回复
     * @param  [int] $rid [帖子回复ID]
     */
    public function subjectReplyDelete($rid){
        //判断是否上传图片并将上传的图片删除
        $sql = 'select `prid` from ebh_forums_subject_replys where rid='.$rid;
        $prid = Ebh()->db->query($sql)->row_array();
        if($prid['prid'] === '0'){
            $sql = 'select `imgs` from ebh_forums_subject_replys where rid='.$rid;
            $imgs = Ebh()->db->query($sql)->row_array();
            if(!empty($imgs['imgs'])){
                unlink($imgs['imgs']);
            }

        }

        $parent_where = array('rid'=>$rid);
        $child_where = array('prid'=>$rid);
        $res1 = Ebh()->db->delete('ebh_forums_subject_replys',$parent_where);
        //判断该回复是否有子回复 有就删除
        $sql = 'select `rid` from ebh_forums_subject_replys where prid='.$rid;
        $is_find = Ebh()->db->query($sql)->row_array();
        if(isset($is_find['rid'])){
            $res2 = Ebh()->db->delete('ebh_forums_subject_replys',$child_where);
            if($res1 && $res2){
                return true;
            }else{
                return false;
            }
        }
        
        if($res1){
            return true;
        }else{
            return false;
        }
    }
}
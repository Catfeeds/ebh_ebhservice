<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class ForumsSubjectModel{
    /**
     * 新增帖子
     * @param $parameters
     * @return bool
     */
    public function addSubject($parameters){
        $data['fid'] = $parameters['fid'];
        $data['uid'] = $parameters['uid'];
        $data['cate_id'] = $parameters['cate_id'];
        $data['crid'] = $parameters['crid'];
        $data['title'] = $parameters['title'];
        $data['imgs'] = $parameters['imgs'];
        $data['dateline'] = time();
        $id = Ebh()->db->insert('ebh_forums_subjects',$data);
        if($id > 0){
            //帖子详情内容入库
            Ebh()->db->insert('ebh_forums_contents',array(
                'sid'   =>  $id,
                'content'   =>  $parameters['content']
            ));
            //社区发帖量更新

            Ebh()->db->query('update ebh_forums set subject_count=subject_count+1 where fid='.$data['fid']);
            Ebh()->db->query('update ebh_forums_categorys set subject_count=subject_count+1 where cate_id='.$data['cate_id']);

            //更新社区表ebh_forums最后发帖字段sid
            Ebh()->db->query("update ebh_forums set sid=$id where fid=".$data['fid']);
            return $id;
        }else{
            return false;
        }
    }

    /**
     * 读取帖子列表
     * @param array $parameters
     * @return mixed
     */
    public function getList($parameters = array()){
        $sql = 'select s.sid,s.uid,s.cate_id,s.title,s.imgs,s.reply_count,s.view_count,s.is_hot,s.is_top,s.dateline,s.is_del,s.del_reason,s.last_reply_name,s.last_reply_time,u.username,u.face,u.realname,u.sex,u.groupid,cate.category_name,con.content,f.name as forum_name from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid left join ebh_forums_categorys cate on cate.cate_id=s.cate_id left join ebh_forums_contents con on con.sid=s.sid left join ebh_forums f on f.fid=s.fid';
        if (!empty($parameters['keyword'])){
            $parameters['where'][] = ' s.title like \'%' .Ebh()->db->escape_str($parameters['keyword']) .'%\'';
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
        return $list;

    }

    /**
     * 获取帖子数
     * @param array $parameters
     * @return int
     */
    public function getListCount($parameters = array()){
        $sql = "select count(s.sid) as count from ebh_forums_subjects s";

        if (!empty($parameters['keyword'])){
            $parameters['where'][] = ' s.title like \'%' .Ebh()->db->escape_str($parameters['keyword']) .'%\'';
        }
        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }

        $count = Ebh()->db->query($sql)->row_array();

        return intval($count['count']);
    }

    /**
     * 读取帖子详情信息
     * @param $sid
     * @param array $parameters
     * @return mixed
     */
    public function getDetail($sid,$parameters = array()){
        $parameters['where'][] = ' s.sid='.$sid;
        $sql = 'select s.sid,s.crid,s.fid,s.uid,s.cate_id,s.title,s.imgs,s.reply_count,s.view_count,s.is_hot,s.is_top,s.dateline,s.is_del,con.content,cate.category_name,u.realname,u.username,u.face,u.sex,u.groupid from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid left join ebh_forums_contents con on con.sid=s.sid left join ebh_forums_categorys cate on cate.cate_id=s.cate_id';
        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }

        return Ebh()->db->query($sql)->row_array();

    }

    /**
     * 设置热帖状态
     * @param $sid
     * @param $is_hot
     * @return mixed
     */
    public function setSubjectHot($sid,$is_hot){
        $data['is_hot'] = $is_hot;
        $data['set_hot_time'] = time();
        $rs =  Ebh()->db->update('ebh_forums_subjects',$data,array('sid'=>$sid));
        return $rs;

    }

    /**
     * 设置帖子置顶状态
     * @param $sid
     * @param $is_top
     * @return mixed
     */
    public function setSubjectTop($sid,$is_top){
        $data['is_top'] = $is_top;
        $subject = $this->getDetail($sid);
        $fid = intval($subject['fid']);
        $sql = "select count(is_top) as number from ebh_forums_subjects where fid=$fid and is_top=1 and is_del=0";
        $count = Ebh()->db->query($sql)->row_array();
        if($is_top == 1){
           if($count['number'] < 3){
                $rs = Ebh()->db->update('ebh_forums_subjects',$data,array('sid'=>$sid));
                return $rs;
            }else{
                return false;
            }   
        }else{
            $rs = Ebh()->db->update('ebh_forums_subjects',$data,array('sid'=>$sid));
            return $rs;
        }
             
    }

    /**
     * 删除指定帖子
     * @param $sid
     * @return mixed
     */
    public function subjectDelete($sid){
        $data['is_del'] = 1;
        $data['is_hot'] = 0;
        $rs =  Ebh()->db->update('ebh_forums_subjects',$data,array('sid'=>$sid));
        $subject = $this->getDetail($sid);
        if($rs){
            Ebh()->db->query('update ebh_forums set subject_count=subject_count-1 where fid='.$subject['fid']);
            Ebh()->db->query('update ebh_forums_categorys set subject_count=subject_count-1 where cate_id='.$subject['cate_id']);
        }
        return $rs;
    }

    /**
     * [搜索帖子]
     * @param  [int] $fid        [社区ID]
     * @param  [int] $cate_id    [分类ID]
     * @param  [string] $keyword [搜索关键字]
     * @return [array]           [结果数组]
     */
    public function searchSubject($parameters){
        $sql = 'select s.sid,s.title,s.dateline,s.reply_count,s.view_count,s.is_hot,s.is_top,c.category_name,f.name as forum_name,u.realname,u.username from ebh_forums_subjects s left join ebh_users u on u.uid=s.uid left join ebh_forums_categorys c on s.cate_id=c.cate_id left join ebh_forums f on s.fid=f.fid';
        $parameters['where'][] = 's.is_del=0';
        if (!empty($parameters['where'])){
            $sql.= ' where ' . implode(' and ', $parameters['where']);
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * [设置帖子浏览量]
     * @param [int] $sid [帖子ID]
     */
    public function setViewCount($sid,$view_count){
        $sql = 'select `view_count` from ebh_forums_subjects where sid='.$sid;
        $old_view_count = Ebh()->db->query($sql)->row_array();
        $data['view_count'] = $view_count+intval($old_view_count['view_count']);
        $result = Ebh()->db->update('ebh_forums_subjects',$data,array('sid'=>$sid));
        return $result ? true : false;
    }
}
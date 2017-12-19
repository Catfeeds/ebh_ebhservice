<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:04
 */
class SnsClassroomFeedsModel{

    public function __construct(){
        $this->db = Ebh()->snsdb;
    }

    /**
     * class_feeds添加
     */
    public function addClassFeeds($param){
        $setarr = array();
        if(!empty($param['fid'])){
            $setarr['fid'] = $param['fid'];
        }
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['classid'])){
            $setarr['classid'] = $param['classid'];
        }

        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }

        if(isset($param['status'])){
            $setarr['status'] = $param['status'];
        }

        return  $this->db->insert("ebh_sns_classfeeds",$setarr);
    }
    /**
     * room_feeds添加
     *
     */
    public function addRoomFeeds($param){
        $setarr = array();
        if(!empty($param['fid'])){
            $setarr['fid'] = $param['fid'];
        }
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }

        if(!empty($param['crid'])){
            $setarr['crid'] = $param['crid'];
        }

        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(isset($param['status'])){
            $setarr['status'] = $param['status'];
        }
        return  $this->db->insert("ebh_sns_roomfeeds",$setarr);
    }

    /**
     * 网校/班级动态删除
     */
    public function delroomandclassfeeds($fid,$uid){
        $arr = array('fid'=>$fid,$uid=>$uid);
        $del = array('status'=>1);
        $this->db->update("ebh_sns_roomfeeds",$del,$arr);
        $this->db->update("ebh_sns_classfeeds",$del,$arr);
    }
}
<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:58
 */
class SnsFollowModel{

    public function __construct(){
        $this->db = Ebh()->snsdb;

    }
    /**
     * 新增加一条关注
     */
    public function addone($param){
        $ck = false;
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = intval($param['uid']);
        }
        if(!empty($param['fuid'])){
            $setarr['fuid'] = intval($param['fuid']);
        }
        if(!empty($param['remark'])){
            $setarr['remark'] = $this->db->escape_str($param['remark']);
        }

        if(!empty($setarr['uid']) && !empty($setarr['fuid'])){
            //关注验证
            $sql = "select count(*) count from ebh_sns_follows where uid = ".$setarr['uid']." and fuid=".$setarr['fuid'];
            $chrow = $this->db->query($sql)->row_array();
            if($chrow['count'] == 0){
                $row =  $this->db->insert("ebh_sns_follows",$setarr);
                //添加成功,粉丝数加1,关注数加1
                if($row>0){
                    //关注数加1
                    $incr = array("followsnum"=>"followsnum +1");
                    $this->db->update("ebh_sns_baseinfos",array(),array("uid"=>$param['uid']),$incr);
                    //被关注者粉丝数加1
                    $incr = array("fansnum"=>"fansnum +1");
                    $this->db->update("ebh_sns_baseinfos",array(),array("uid"=>$param['fuid']),$incr);
                }

                $ck = true;
            }
        }

        return $ck;
    }

    /**
     * 删除一条记录 ,粉丝数减1,关注数减1
     */
    public function cancelone($uid,$fuid){
        $this->db->begin_trans();
        $row = $this->db->delete("ebh_sns_follows",array("uid"=>$uid,"fuid"=>$fuid));
        if($row>0){
            //关注数减1
            $reduce = array("followsnum"=>"followsnum -1");
            $ck1 = $this->db->update("ebh_sns_baseinfos",array(),array("uid"=>$uid),$reduce);
            //粉丝数减1
            $reduce = array("fansnum"=>"fansnum -1");
            $ck2 = $this->db->update("ebh_sns_baseinfos",array(),array("uid"=>$fuid),$reduce);
            if($ck1&&$ck2){
                $this->db->commit_trans();
                return true;
            }else{
                $this->db->rollback_trans();
                return false;
            }
        }
    }

    /**
     * 修改一条关注
     */
    public function updateone($param,$uid,$fuid){
        $setarr = array();

        if(!empty($param['remark'])){
            $setarr['remark'] = $this->db->escape_str($param['remark']);
        }
        if(isset($param['disable'])){
            $setarr['disable'] = $param['disable'];
        }
        return $this->db->update("ebh_sns_follows",$setarr,array("uid"=>$uid,"fuid"=>$fuid));
    }

    /**
     * 获取我的关注
     */
    public function getfollowlist($param){
        $where = array();
        $sql = "select uid,fuid,remark,disable from ebh_sns_follows ";
        if(!empty($param['uid'])){
            $where[] = "uid = {$param['uid']}";
        }
        if(!empty($param['fuid'])){
            $where[] = "fuid = {$param['fuid']}";
        }
        if(!empty($param['remark'])){
            $where[] = "remark = ".$this->db->escape_str($param['remark'])."";
        }
        if(!empty($where)){
            $sql.= " WHERE ".implode(" AND ",  $where);
        }
        if(!empty($param['orderby'])){
            $sql.=" order by ".$param['orderby'];
        }else{
            $sql.=" ORDER BY id DESC ";
        }
        if(!empty($param['limit'])){
            $sql.=" LIMIT ".$param['limit'];
        }

        return $this->db->query($sql)->list_array();
    }
    /**
     * 我的关注总数
     */
    public function getfollowcount($param){
        $where = array();
        $sql = "select count(*) count from ebh_sns_follows ";
        if(!empty($param['uid'])){
            $where[] = "uid = {$param['uid']}";
        }
        if(!empty($param['fuid'])){
            $where[] = "fuid = {$param['fuid']}";
        }
        if(!empty($param['remark'])){
            $where[] = "remark = ".$this->db->escape_str($param['remark']);
        }
        if(!empty($where)){
            $sql.= " WHERE ".implode(" AND ",  $where);
        }

        $row =  $this->db->query($sql)->row_array();
        if(empty($row['count'])){
            $row['count'] = 0;
        }
        return $row['count'];
    }

    /**
     * 检测用户是否互相关注
     * keys=fuid  检测我的关注
     * keys = uid 检测我的粉丝
     */
    public function checkfollow($users,$uid,$keys="fuid"){

        $uidarr = array();
        $rkeys = ($keys=="fuid")?"uid":"fuid";
        foreach($users as $user){
            array_push($uidarr,$user[$keys]);
        }

        $sql = "select uid,fuid from ebh_sns_follows where $rkeys in( ". implode(",", $uidarr)." ) and $keys = $uid";
        $rows = $this->db->query($sql)->list_array();
        //我关注的
        //dump($uidarr);
        //dump($rows);
        //dump($users);
        //dump($keys);
        foreach($users as &$user){
            foreach($rows as $row){
                if($user[$keys] == $row[$rkeys]){
                    $user['together'] = true;
                    break;
                }else{
                    $user['together'] = false;
                }
            }
        }

        return $users;
    }

    /**
     * 检测是否已经关注
     * 关注发起人 uid
     * 待检被关注者users
     * @param unknown $users
     * @param unknown $uid
     * @param string $keys
     */
    public function checkfollowed($users,$uid,$keys="fuid"){
        $uidarr = array();
        foreach($users as $user){
            array_push($uidarr,$user[$keys]);
        }
        $sql = "select uid,fuid from ebh_sns_follows where fuid in( ". implode(",", $uidarr)." ) and uid = $uid";
        $rows = $this->db->query($sql)->list_array();

        foreach($users as &$user){
            $user['followed'] = false;
            foreach($rows as $row){
                if($user[$keys] == $row['fuid']){
                    $user['followed'] = true;
                    break;
                }else{
                    $user['followed'] = false;
                }
            }
        }

        return $users;
    }


    /**
     * 获取我的所有关注
     */
    public function getmyfollows($uid){
        $sql = "select fuid as uid from ebh_sns_follows where uid = $uid";
        $rows = $this->db->query($sql)->list_array();
        return $rows;
    }

    /**
     * 获取我的所有粉丝
     */
    public function getmyfans($uid){
        $sql = "select uid from ebh_sns_follows where fuid = $uid";
        $rows = $this->db->query($sql)->list_array();
        return $rows;
    }

    /**
     * 检测是否好友
     */
    public function isfriend($uid,$fuid){
        $sql = "select count(*) count from ebh_sns_follows where (uid = $uid and fuid = $fuid) or (uid = $fuid and fuid = $uid)";
        $row =  $this->db->query($sql)->row_array();
        $bool = empty($row['count']) ? false : true;
        return $bool;
    }
}
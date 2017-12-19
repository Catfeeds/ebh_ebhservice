<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:47
 */
class SnsCommentModel{

    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    /**
     * comment添加
     */
    public function add($param){
        $setarr = array();
        if(!empty($param['pcid'])){
            $setarr['pcid'] = $param['pcid'];
        }
        if(!empty($param['fid'])){
            $setarr['fid'] = $param['fid'];
        }
        if(!empty($param['images'])){
            $setarr['images'] = $param['images'];
        }
        if(!empty($param['message'])){
            $setarr['message'] =$param['message'];
        }
        if(!empty($param['fromuid'])){
            $setarr['fromuid'] = $param['fromuid'];
        }
        if(!empty($param['touid'])){
            $setarr['touid'] = $param['touid'];
        }
        if(!empty($param['category'])){
            $setarr['category'] = $param['category'];
        }
        if(!empty($param['toid'])){
            $setarr['toid'] = $param['toid'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        return  $this->db->insert("ebh_sns_comments",$setarr);
    }


    /**
     * 获取一条评论
     */
    public function getcommentbycid($cid){
        $sql = "select cid,pcid,fid,images,message,fromuid,touid,category,toid,status,dateline from ebh_sns_comments where cid = $cid and status = 0";
        return  $this->db->query($sql)->row_array();
    }
    /**
     * 评论编辑
     */
    public function edit($param,$cid){
        $setarr = array();
        $where = array();
        if(!empty($param['status'])){
            $setarr['status'] = $param['status'];
        }

        return  $this->db->update("ebh_sns_comments",$setarr,array('cid'=>$cid));
    }

    /**
     * 查询所有评论
     */
    public function getcommentlist($param){
        $where = array();
        $sql = "select cid,pcid,fid,images,message,fromuid,touid,category,toid,status,dateline from ebh_sns_comments ";

        if(!empty($param['condition'])){
            $where[] = " ".$param['condition'];
        }
        if(isset($param['fid'])){
            $where[] = " fid = ".$param['fid'];
        }
        if(!empty($param['category'])){
            $where[] = " category =    ". $param['category'];
        }
        if(!empty($param['toid'])){
            $where[] = " toid =    ". $param['toid'];
        }

        $where[] = " status = 0  ";

        if(!empty($where)){
            $sql.=" WHERE ".implode(" AND ", $where);
        }

        if(!empty($param['orderby'])){
            $sql.=" order by ".$param['orderby'];
        }else{
            $sql.=" order by cid ";
        }

        if(!empty($param['limit'])){
            $sql.=" LIMIT  ".$param['limit'];
        }else{
            $sql.=" LIMIT  10";
        }

        //echo  $sql;

        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取评论总数
     */
    public function getcommentcount($param){
        $where = array();
        $sql = "select count(*) as count from ebh_sns_comments ";
        if(isset($param['fid'])){
            $where[] = " fid = ".$param['fid'];
        }

        if(!empty($param['category'])){
            $where[] = " category =    ". $param['category'];
        }
        if(!empty($param['toid'])){
            $where[] = " toid =    ". $param['toid'];
        }

        $where[] = " status = 0  ";

        if(!empty($where)){
            $sql.=" WHERE ".implode(" AND ", $where);
        }
        //echo $sql;

        $row =  $this->db->query($sql)->row_array();

        if(empty($row['count'])){
            $row['count']  = 0;
        }
        return $row['count'];
    }

    /**
     * 获取多个动态的评论总数
     *
     */
    public function getcommentcountlist($fidarr){
        $where = array();
        $sql = "select count(*) as count,fid from ebh_sns_comments ";

        $where[] = " fid in( ".implode(",", $fidarr)." )";
        $where[] = " status = 0  ";

        if(!empty($where)){
            $sql.=" WHERE ".implode(" AND ", $where);
        }
        $sql .= " group by fid";
        $rows =  $this->db->query($sql)->list_array();

        $ret = array();
        //dump($rows);exit;
        foreach($fidarr as $key=>$fid){
            $ret[$key]['fid'] = $fid;

            if(!empty($rows)){
                foreach ($rows as $row){
                    if($row['fid']==$fid){
                        $ret[$key]['count'] = $row['count'];
                        break;
                    }else{
                        $ret[$key]['count'] = 0;
                    }
                }
            }else{
                $ret[$key]['count'] = 0;
            }
        }
        return $ret;
    }

    /**
     * 获取多个动态的评论,每个动态最多取十条评论,默认
     * @param unknown $fidarr
     * @param number $len
     */
    public function getfeedscomments($fidarr,$len=10){
        if(empty($fidarr)) return false;
        foreach($fidarr as $fid){
            $count = $this->getcommentcount(array('fid'=>$fid));
            if($count>0){
                $ret[$fid]['replys'] = $this->getcommentlist(array('fid'=>$fid,'limit'=>$len));
            }else{
                $ret[$fid]['replys'] =array();
            }
            $ret[$fid]['count'] = $count;
        }

        return $ret;
    }
}
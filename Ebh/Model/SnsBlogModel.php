<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:58
 */
class SnsBlogModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    //获取日志
    public function getlist($param){
        $sql = "select b.uid, c.catename, b.bid, b.pbid, b.tbid, b.iszhuan, b.cid, b.title, b.tutor, b.content, b.permission, b.dateline, b.images, b.status, b.upcount, b.cmcount, b.zhcount from ebh_sns_blogs b left join ebh_sns_categorys c on b.cid = c.cid";
        if(!empty($param['bid'])){
            $where[] = 'b.bid ='. $param['bid'];
        }
        if(!empty($param['pbid'])){
            $where[] = 'b.pbid ='. $param['pbid'];
        }
        if(!empty($param['uid'])){
            $where[] = 'b.uid ='. $param['uid'];
        }
        if(!empty($param['cid'])){
            $where[] = 'b.cid='. $param['cid'];
        }
        if(isset($param['permission'])){
            $where[] ='b.permission='. $param['permission'];
        }
        $where[] = 'b.status = 0';
        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }
        if(!empty($param['orderbby'])){
            $sql.=" ORDER BY ".$param['orderbby'];
        }else{
            $sql.=" ORDER BY b.dateline DESC";
        }
        if(!empty($param['limit'])){
            $sql.=" LIMIT ".$param['limit'];
        }else{
            $sql.=" LIMIT 10 ";
        }
        return $this->db->query($sql)->list_array();
    }
    //获取日志总数
    public function getlistcount($param){
        $sql = "select count(*) count from ebh_sns_blogs b left join ebh_sns_categorys c on b.cid = c.cid";
        if(!empty($param['bid'])){
            $where[] = 'b.bid ='. $param['bid'];
        }
        if(!empty($param['uid'])){
            $where[] = 'b.uid ='. $param['uid'];
        }
        if(!empty($param['cid'])){
            $where[] = 'b.cid='. $param['cid'];
        }
        if(isset($param['permission'])){
            $where[] ='b.permission='. $param['permission'];
        }
        $where[] = 'b.status = 0';
        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }
        $row =  $this->db->query($sql)->row_array();
        if(empty($row['count'])){
            $row['count'] = 0;
        }
        return  $row['count'];
    }
    //添加一条
    public function add($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['title'])){
            $setarr['title'] = $param['title'];
        }
        if(!empty($param['content'])){
            $setarr['content'] = $param['content'];
        }
        if(isset($param['cid'])){
            $setarr['cid'] = $param['cid'];
        }
        if(!empty($param['pbid'])){
            $setarr['pbid'] = $param['pbid'];
        }
        if(!empty($param['tbid'])){
            $setarr['tbid'] = $param['tbid'];
        }
        if(!empty($param['iszhuan'])){
            $setarr['iszhuan'] = $param['iszhuan'];
        }
        if(isset($param['tutor'])){
            $setarr['tutor'] = $param['tutor'];
        }
        if(isset($param['permission'])){
            $setarr['permission'] = $param['permission'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['images'])){
            $setarr['images'] = $param['images'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        return $this->db->insert("ebh_sns_blogs",$setarr);
    }
    //删除一条
    public function delete($param){
        $where = array();
        if(!empty($param['bid'])){
            $where['bid'] = $param['bid'];
        }else{
            return false;
        }
        if(!empty($param['uid'])){
            $where['uid'] = $param['uid'];
        }
        return $this->db->delete("ebh_sns_blogs",$where);
    }
    //更新一条
    public function update($param,$where,$sparam=array()){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['title'])){
            $setarr['title'] = $param['title'];
        }
        if(!empty($param['content'])){
            $setarr['content'] = $param['content'];
        }
        if(isset($param['cid'])){
            $setarr['cid'] = $param['cid'];
        }
        if(isset($param['tutor'])){
            $setarr['tutor'] = $param['tutor'];
        }
        if(isset($param['permission'])){
            $setarr['permission'] = $param['permission'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['images'])){
            $setarr['images'] = $param['images'];
        }
        if(isset($param['status'])){
            $setarr['status'] = $param['status'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        if(!empty($where['bid'])){
            $wherearr['bid'] = $where['bid'];
        }
        if(!empty($where['uid'])){
            $wherearr['uid'] = $where['uid'];
        }
        if(!empty($sparam['upcount'])){
            $sparam['upcount'] = $sparam['upcount'];
        }
        if(!empty($sparam['cmcount'])){
            $sparam['cmcount'] = $sparam['cmcount'];
        }
        if(!empty($sparam['zhcount'])){
            $sparam['zhcount'] = $sparam['zhcount'];
        }
        return $this->db->update("ebh_sns_blogs",$setarr,$wherearr,$sparam);
    }
    //添加一个分类
    public function addcate($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['catename'])){
            $setarr['catename'] = $param['catename'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        return $this->db->insert("ebh_sns_categorys",$setarr);
    }
    //用户日志分类
    public function getcate($param){
        $sql = "select cid, catename from ebh_sns_categorys";
        if(!empty($param['uid'])){
            $where[] = " (uid = 0 or uid = ".$param['uid'].")";
        }else{
            $where[] = " uid = 0";
        }
        if(!empty($param['catename'])){
            $where[] = " catename = '".$this->db->escape_str($param['catename'])."'";
        }
        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取博客产生的动态
     * @param unknown $toid
     */
    public function getblogfeed($toid){
        $sql = "select * from ebh_sns_feeds where (category =2 OR  category =4) and toid = $toid order by fid asc limit 1";
        return $this->db->query($sql)->row_array();
    }
}
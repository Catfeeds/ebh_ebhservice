<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 11:37
 */
class SnsImageModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    public function add($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['path'])){
            $setarr['path'] = $param['path'];
        }
        if(!empty($param['sizes'])){
            $setarr['sizes'] = $param['sizes'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        return $this->db->insert("ebh_sns_images",$setarr);
    }
    //根据gid获取图片详情
    public function getimgs($arr){
        if(empty($arr)) return array();
        $where = array();
        $sql = "select gid, path, sizes, status from ebh_sns_images";
        if(!empty($arr)){
            $where['gid'] = 'gid in ('.implode(',',$arr).')';
        }
        if(!empty($where)){
            $sql.= " WHERE ".implode(" AND ",  $where);
        }
        return $this->db->query($sql)->list_array();
    }
    public function delete($param){
        $where = array();
        if(!empty($param['uid'])){
            $where['uid'] = $param['uid'];
        }
        if(!empty($param['gid'])){
            $where['gid'] = $param['gid'];
        }
        if(!empty($param['gids'])){
            $where = ' gid in ('.implode(',', $param['gids']).')';
        }
        return $this->db->delete("ebh_sns_images",$where);
    }
    public function update($param,$gid){
        $setarr = array();
        if(!empty($param['path'])){
            $setarr['path'] = $param['path'];
        }
        if(!empty($param['sizes'])){
            $setarr['sizes'] = $param['sizes'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        $where['gid'] = $gid;
        return $this->db->update("ebh_sns_images",$setarr,$where);
    }
    //提取图片列表
    public function getimglist($param){
        $where = array();
        $sql = "select gid, path, sizes, status from ebh_sns_images";
        if(!empty($param['uid'])){
            $where[] = ' uid = '.$param['uid'];
        }
        if(!empty($param['begindate'])){
            $where[] = ' dateline>='.$param['begindate'];
        }
        if(!empty($param['eenddate'])){
            $where[] = ' dateline<'.$param['enddate'];
        }
        if(isset($param['status'])){
            $where[] = ' status = '.$param['status'];
        }
        if(!empty($where)){
            $sql .= ' WHERE '.implode(' AND ',$where);
        }
        if(!empty($param['orderbby'])){
            $sql .=" ORDER BY ".$param['orderbby'];
        }else{
            $sql .=" ORDER BY dateline DESC";
        }
        if(!empty($param['limit'])){
            $sql .=" LIMIT ".$param['limit'];
        }else{
            $sql .=" LIMIT 7 ";
        }
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取照片数量
     * @param $param
     * @return mixed
     */
    public function getImgCount($param){
        $where = array();
        $sql = "select count(gid) as count from ebh_sns_images";
        if(!empty($param['uid'])){
            $where[] = ' uid = '.$param['uid'];
        }
        if(!empty($param['begindate'])){
            $where[] = ' dateline>='.$param['begindate'];
        }
        if(!empty($param['eenddate'])){
            $where[] = ' dateline<'.$param['enddate'];
        }
        if(isset($param['status'])){
            $where[] = ' status = '.$param['status'];
        }
        if(!empty($where)){
            $sql .= ' WHERE '.implode(' AND ',$where);
        }
        $res = $this->db->query($sql)->row_array();
        return $res['count'];
    }
}
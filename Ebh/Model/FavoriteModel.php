<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:42
 */
class FavoriteModel{
    /**
     * 插入数据
     * @param $param
     * @return mixed
     */
    public function insert($param) {
        $setarr = array ();
        if(!empty($param ['uid'])){
            $setarr['uid'] = intval($param['uid']);
        }
        if(!empty($param ['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        if(!empty($param ['cwid'])){
            $setarr['cwid'] = intval($param['cwid']);
        }
        if(!empty($param ['folderid'])){
            $setarr['folderid'] = intval($param['folderid']);
        }
        $setarr['dateline'] = SYSTIME;

        if(!empty($param ['type'])){
            $setarr['type'] = intval($param['type']);
        }
        if(!empty($param ['url'])){
            $setarr['url'] = $param['url'];
        }
        if(!empty($param ['title'])){
            $setarr['title'] = $param['title'];
        }
        if(!empty($param ['displayorder'])){
            $setarr['displayorder'] = intval($param['displayorder']);
        }
        $fid = Ebh()->db->insert('ebh_favorites',$setarr);
        return $fid;
    }


    /**
     *删除收藏
     */
    public function delete($fid){
        $wherearr = array('fid'=>$fid);
        return Ebh()->db->delete('ebh_favorites',$wherearr);

    }

    /**
     *根据uid和fid删除收藏
     */
    public function deleteByUid($uid,$fid){
        $wherearr = array('uid'=>$uid,'fid'=>$fid);
        return Ebh()->db->delete('ebh_favorites',$wherearr);
    }

    /**
     * 课件是否已经收藏
     * @param $crid
     * @param $uid
     * @param $cwid
     * @return mixed
     */
    public function courseIsExist($crid,$uid,$cwid){
        $sql = 'select fid from ebh_favorites where crid='.$crid.' and cwid='.$cwid.' and uid='.$uid;
        $rs = Ebh()->db->query($sql)->row_array();

        if($rs){
            return  $rs['fid'];
        }else{
            return 0;
        }
    }


    /**
     * 获取学生课件收藏数
     * @param $param
     * @return int
     */
    public function getCourseCount($param){
        if(empty($param['uid'])){
            return 0;
        }

        $sql = 'select count(f.fid) as count from ebh_favorites f';

        $wherearr = array();
        $wherearr[] = 'f.uid='.$param['uid'];
        $wherearr[] = 'f.type=1';
        if(!empty($param['crid']))
            $wherearr[] = 'f.crid='.$param['crid'];
        if(!empty($param['cwid']))
            $wherearr[] = 'f.cwid='.$param['cwid'];
        $sql .= ' where '.implode(' and ',$wherearr);

        $res = Ebh()->db->query($sql)->row_array();

        return $res['count'];


    }
    /**
     *获取学员课件收藏列表
     */
    public function getCourseList($param) {
        if(empty($param['uid']))
            return FALSE;
        $sql = 'SELECT f.fid,f.dateline, c.cwid,c.uid,c.title,c.logo,c.summary,c.cwname,c.cwsource,c.cwurl,c.cwlength,c.submitat,c.truedateline,c.islive from ebh_favorites f '.
            'JOIN ebh_coursewares c on (f.cwid = c.cwid) ';
        $wherearr = array();
        $wherearr[] = 'f.uid='.$param['uid'];
        $wherearr[] = 'f.type=1';
        if(!empty($param['crid']))
            $wherearr[] = 'f.crid='.$param['crid'];
        if(!empty($param['cwid']))
            $wherearr[] = 'f.cwid='.$param['cwid'];
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        $sql.= ' order by f.fid desc';
        if(!empty($param['limit']))
            $sql .= ' limit '.$param['limit'];
        else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return Ebh()->db->query($sql)->list_array();
    }




}
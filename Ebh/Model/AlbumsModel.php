<?php
/**
 * 我的相册(模板库)Model
 * Author: zyp
 */
class AlbumsModel{
    private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }

    /**
     * 获取子相册
     * @param $crid,$uid,$aid
     */
    public function getAlbums($param){
        $return = false;
        $aid = !empty($param['aid']) ? intval($param['aid']) : 0;
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($crid) || empty($uid)){
            return $return;
        }
        $sql = 'SELECT ra.aid,ra.paid,ra.alname,ra.crid,ra.uid,ra.del,ra.nums,ra.delnums,ra.systype,ra.ishide,ra.displayorder,ra.issystem FROM ebh_roomalbums ra ';
        $wherearr[] = 'ra.crid='.$crid.' AND ra.uid='.$uid.' AND ra.del = 0 AND  ra.paid='.$aid;
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'ra.alname LIKE \'%'.$q.'%\'';
        }
        if(!empty($param['systype'])){
            $wherearr[] = 'ra.systype='.intval($param['systype']);
        }
        if(!empty($wherearr)){
            $sql.= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        }else{
            if(!empty($param['issystem'])){
                $sql.= ' ORDER BY ra.displayorder ASC,ra.aid DESC';
            }else{
                $sql.= ' ORDER BY ra.aid DESC';
            }
        }
        $albums = $this->db->query($sql)->list_array();
        if(!empty($albums)){
            $return = $albums;
        }
        return $return;
    }

    /**
     * 保存新建文件夹（模板分类）信息
     * @param aid $param
     * @return aid|boolean
     */
    public function addAlbums($param){
        $setarr = array();
        $return = false;
        $setarr['uid'] = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($setarr['uid'])){
            return $return;
        }
        $setarr['paid'] = !empty($param['aid']) ? $param['aid'] : 0;
        if(!empty($param['alname'])){
            $setarr['alname'] = $this->db->escape_str($param['alname']);
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        if(!empty($param['issystem'])){
            $setarr['issystem'] = intval($param['issystem']);
        }
        if(!empty($param['ishide'])){
            $setarr['ishide'] = intval($param['ishide']);
        }
        if(!empty($param['displayorder'])){
            $setarr['displayorder'] = intval($param['displayorder']);
        }
        if(!empty($param['systype'])){
            $setarr['systype'] = intval($param['systype']);
        }
        if(isset($param['clienttype'])){
            $setarr['clienttype'] = intval($param['clienttype']);//终端类型：0-电脑版,1-手机版
        }
        if(!empty($setarr)){
            $return = $this->db->insert('ebh_roomalbums',$setarr);
        }
        return $return;
    }

    /**
     * 修改相册
     * @param aid,alname $param
     * @param boolean
     */
    public function editAlbums($param){
        $setarr = array();
        $where = array();
        $return = false;
        $where['uid'] = !empty($param['uid']) ? intval($param['uid']) : 0;
        $where['aid'] = !empty($param['aid']) ? intval($param['aid']) : 0;
        $setarr['alname'] = !empty($param['alname']) ? $this->db->escape_str($param['alname']) : '';
        if(!empty($param['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        if(isset($param['paid'])){
            $setarr['paid'] = intval($param['paid']);
        }
        if(!empty($param['systype'])){
            $setarr['systype'] = intval($param['systype']);
        }
        if(isset($param['displayorder'])){
            $setarr['displayorder'] = intval($param['displayorder']);
        }
        if(isset($param['ishide'])){
            $setarr['ishide'] = intval($param['ishide']);
        }
        if(empty($where['uid']) || empty($where['aid']) || empty($setarr['alname'])){
            return $return;
        }
        return $this->db->update('ebh_roomalbums',$setarr,$where);
    }
    /**
     * 删除相册（删除模版库分类）
     * @param aid $param
     * @return boolean
     */
    public function delAlbums($param){
        $setarr = array();
        $return = false;
        $setarr['uid'] = !empty($param['uid']) ? intval($param['uid']) : 0;
        $setarr['aid'] = !empty($param['aid']) ? intval($param['aid']) : 0;
        if(empty($setarr['uid']) || empty($setarr['aid'])){
            return $return;
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        $status['del'] = 1;
        $return = $this->db->update('ebh_roomalbums',$status,$setarr);
        if ($return === false) {
            return false;
        }
        $sql = 'SELECT count(1) count FROM ebh_roomphotos rp WHERE rp.uid='.$setarr['uid'].' AND rp.del = 0 AND  rp.aid='.$setarr['aid'];
        if(!empty($setarr['crid'])){
            $sql .= ' AND rp.crid='.$setarr['crid'];
        }
        $photos = $this->db->query($sql)->row_array();
        //删除相册时判断相册里是否有图片，有则设置图片为删除状态
        if(!empty($photos['count'])){
            $delres = $this->db->update('ebh_roomphotos',$status,$setarr);
            if ($delres === false) {
                return false;
            }
            if(!empty($delres) && !empty($setarr['aid'])){
                $sql = 'UPDATE ebh_roomalbums SET nums=nums-+'.$photos['count'].',delnums=delnums+'.$photos['count'].' WHERE aid = '.$setarr['aid'];
                $udres = $this->db->query($sql,false);
                if ($udres === false) {
                    return false;
                }
            }
        }
        return $return;

    }
    /**
     * 获取相册图片信息
     * @param $crid,$uid,$aid
     */
    public function getAlbumsPhotos($param){
        $return = false;
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($crid) || empty($uid)){
            return $return;
        }
        $aid = !empty($param['aid']) ? intval($param['aid']) : 0;
        $sql = 'SELECT rp.pid,rp.uid,rp.crid,rp.aid,rp.photoname,rp.ext,rp.size,rp.server,rp.path,rp.dateline,width,height FROM ebh_roomphotos rp';
        $wherearr[] = 'rp.crid='.$crid.' AND rp.uid='.$uid.' AND rp.del = 0 AND  rp.aid='.$aid;
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'rp.photoname LIKE \'%'.$q.'%\'';
        }
        if(!empty($wherearr)){
            $sql.= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        }else{
            $sql.= ' ORDER BY rp.aid DESC';
        }
        $photos = $this->db->query($sql)->list_array();
        if(!empty($photos)){
            $return = $photos;
        }
        return $return;
    }
    /**
     * 获取指定用户相册图片信息
     * @param $crid,$uid
     */
    public function getUserPhotos($param){
        $return = false;
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($crid) || empty($uid)){
            return $return;
        }
        $sql = 'SELECT rp.pid,rp.aid,rp.uid,rp.crid,rp.photoname,rp.ext,rp.size,rp.server,rp.path,rp.dateline,width,height FROM ebh_roomphotos rp';
        $wherearr[] = 'rp.crid='.$crid.' AND rp.uid='.$uid.' AND rp.del = 0';
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'rp.photoname LIKE \'%'.$q.'%\'';
        }
        if(!empty($wherearr)){
            $sql.= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        }else{
            $sql.= ' ORDER BY rp.aid DESC';
        }
        $albums = $this->db->query($sql)->list_array();
        if(!empty($albums)){
            $return = $albums;
        }
        return $return;
    }
    /**
     * 保存单个上传图片信息（模版库模板信息）
     * @param aid,photoname,ext,size,server,path $param
     * @return pid|boolean
     */
    public function addOnePhotos($param){
        $return = false;
        if(empty($param) || !is_array($param)){
            return $return;
        }
        $setarr = array();
        $setarr['uid'] = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($setarr['uid'])){
            return $return;
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        $setarr['aid'] = !empty($param['aid']) ? intval($param['aid']) : 0;
        $setarr['photoname'] = $this->db->escape_str($param['photoname']);
        $setarr['ext'] = $this->db->escape_str($param['ext']);
        $setarr['size'] = intval($param['size']);
        $setarr['server'] = $this->db->escape_str($param['server']);
        $setarr['path'] = $this->db->escape_str($param['path']);
        $setarr['dateline'] = SYSTIME;
        $setarr['del'] = 0;
        $setarr['width'] = !empty($param['width']) ? intval($param['width']) : 0;
        $setarr['height'] = !empty($param['height']) ? intval($param['height']) : 0;
        if(!empty($param['issystem'])){
            $setarr['issystem'] = intval($param['issystem']);
        }
        if(isset($param['clienttype'])){
            $setarr['clienttype'] = intval($param['clienttype']);//终端类型：0-电脑版,1-手机版
        }
        if(empty($setarr['photoname']) || empty($setarr['ext']) || empty($setarr['size']) || empty($setarr['server']) || empty($setarr['path'])){
            return $return;
        }

        //当创建的是模板时记录模板did
        if(!empty($setarr['issystem']) && ($setarr['issystem'] == 2)){
            if(empty($param['roomtype']) || !isset($setarr['clienttype'])){
                return $return;
            }
            $setarr['clienttype'] = intval($param['clienttype']);
            $designModel = new DesignModel();
            $designs = array('name' => $setarr['photoname'], 'roomtype' => $this->db->escape_str($param['roomtype']),'client_type' => $setarr['clienttype']);
            $res = $designModel->addDesign($designs);
            if($res === false){
                return $return;
            }
            $setarr['did'] = $res;
        }
        //保存到数据库
        $return = $this->db->insert('ebh_roomphotos',$setarr);
        if ($return === false) {
            return false;
        }
        //图片上传成功后更新该相册的图片数量nums
        if(!empty($return) && !empty($setarr['aid'])){
            $sql = 'UPDATE ebh_roomalbums SET nums=nums+1 WHERE aid = '.$setarr['aid'];
            $result = $this->db->query($sql, false);
            if ($result === false) {
                return false;
            }
        }
        return $return;

    }

    /**
     * 修改相册中图片名称
     * @param pid,photoname $param
     * @param boolean
     */
    public function editPhotos($param){
        $setarr = array();
        $where = array();
        $return = false;
        $where['pid'] = !empty($param['pid']) ? intval($param['pid']) : 0;
        $setarr['photoname'] = !empty($param['photoname']) ? $this->db->escape_str($param['photoname']) : '';
        if(empty($where['pid']) || empty($setarr['photoname'])){
            return $return;
        }
        return $this->db->update('ebh_roomphotos',$setarr,$where);
    }

    /**
     * 删除相册中图片
     * @param pid,aid $param
     * @return boolean
     */
    public function delAlbumsPhotos($param){
        $setarr = array();
        $return = false;
        $setarr['aid'] = !empty($param['aid']) ? intval($param['aid']) : 0;
        $setarr['pid'] = !empty($param['pid']) ? intval($param['pid']) : 0;
        if(empty($setarr['pid'])){
            return $return;
        }
        $status['del'] = 1;
        $return = $this->db->update('ebh_roomphotos',$status,$setarr);
        //图片删除成功后更新该相册的图片数量delnums
        if(!empty($return) && !empty($setarr['aid'])){
            $sql = 'UPDATE ebh_roomalbums SET delnums=delnums+1 WHERE aid = '.$setarr['aid'];
            $this->db->query($sql);
        }
        return $return;
    }

    /**
     * 编辑图片库图片（编辑模板库模板）
     * @param aid,pid,photoname $param
     * @param boolean
     */
    public function editGalleryPhotos($param){
        $setarr = array();
        $where = array();
        $return = false;
        if(empty($param['pid'])){
            return $return;
        }
        $where['pid'] = intval($param['pid']);
        if(!empty($param['oldaid'])){
            $oldaid = intval($param['oldaid']);
        }
        if(!empty($param['newaid'])){
            $newaid = intval($param['newaid']);
        }
        if(isset($param['istop']) && in_array($param['istop'],array(0,1))){
            if($param['istop'] == 1){
                $setarr['toptime'] = SYSTIME;
            }else{
                $setarr['toptime'] = 0;
            }
        }
        if(!empty($param['photoname'])){
            $setarr['photoname'] = $this->db->escape_str($param['photoname']);
        }
        if(!empty($param['ext'])) {
            $setarr['ext'] = $this->db->escape_str($param['ext']);
        }
        if(!empty($param['size'])) {
            $setarr['size'] = intval($param['size']);
        }
        if(!empty($param['server'])) {
            $setarr['server'] = $this->db->escape_str($param['server']);
        }
        if(!empty($param['path'])) {
            $setarr['path'] = $this->db->escape_str($param['path']);
        }
        if(!empty($param['width'])) {
            $setarr['width'] = intval($param['width']);
        }
        if(!empty($param['height'])) {
            $setarr['height'] = intval($param['height']);
        }
        if(isset($param['ishide'])){
            $setarr['ishide'] = intval($param['ishide']);//是否隐藏分类,1表示隐藏
        }
        //获取原模板信息
        if(!empty($param['issystem']) && ($param['issystem'] == 2)){
            $designsql = 'select rp.pid,rp.did,rp.photoname,rd.name from ebh_roomphotos rp join ebh_roomdesigns rd on rp.did=rd.did where rp.pid='.$where['pid'] ;
            $designres = $this->db->query($designsql)->row_array();
        }

        //当前相册图片数量减一，新修改的相册图片加一
        if(!empty($newaid) && !empty($oldaid) &&($newaid != $oldaid)){
            $setarr['aid'] = $newaid;
            $return = $this->db->update('ebh_roomphotos',$setarr,$where);
            //图片库图片迁移成功后更新新旧相册的图片数量
            if(!empty($return) && !empty($newaid)){
                $sql = 'UPDATE ebh_roomalbums SET nums=nums+1 WHERE aid = '.$newaid;
                $upnewres = $this->db->query($sql,false);
                if ($upnewres === false) {
                    return false;
                }
            }
            if(!empty($return) && !empty($oldaid)){
                $sql = 'UPDATE ebh_roomalbums SET nums=nums-1 WHERE aid = '.$oldaid;
                $upoldres = $this->db->query($sql,false);
                if ($upoldres === false) {
                    return false;
                }
            }
        }else{
            if(!empty($newaid)){
                $setarr['aid'] = $newaid;
            }
            $return = $this->db->update('ebh_roomphotos',$setarr,$where);
        }
        //修改模板名称成功后更新对应网校首页装扮配置表的装扮名称
        if(($return !==false) && !empty($designres['did']) && !empty($designres['name']) && !empty($param['photoname']) && ($param['photoname'] !=$designres['name'])){
            $upoldname = $this->db->update('ebh_roomdesigns',array('name'=>$setarr['photoname']),array('did'=>$designres['did']));
            if ($upoldname === false) {
                return false;
            }
        }
        return $return;
    }

    /**
     * 删除图库中图片（删除模板库模板）
     * @param pid,aids $param
     * @return boolean
     */
    public function delGalleryPhotos($param){
        $result = false;
        $upphotos = '';//更新网校图片表sql语句
        $upalbums = '';//更新网校相册表sql语句
        $updesign = '';//更新首页装扮配置表sql语句
        $pids = !empty($param['pids']) ? $param['pids'] : 0;
        if(!empty($pids) && is_array($pids)) {
            foreach ($pids as $pid) {
                if (!is_numeric($pid)) {
                    return false;
                }
            }
        }
        $sql = 'SELECT count(DISTINCT pid) count,GROUP_CONCAT(pid) AS pids,aid FROM ebh_roomphotos WHERE pid IN ('.implode(',',$pids).') GROUP BY aid';
        $photoinfo = $this->db->query($sql)->list_array();
        if($photoinfo === false){
            return false;
        }
        $aids = array_column($photoinfo,'aid');
        //删除对应图片或模板
        $upphotos = 'UPDATE ebh_roomphotos SET del=1 WHERE pid IN ('.implode(',',$pids).')';
        //更新相册数量
        if(!empty($photoinfo) && is_array($photoinfo) && !empty($aids) && is_array($aids)){
            foreach ($photoinfo as $info){
                $nums[] = ' WHEN '.$info['aid'].' THEN nums-'.$info['count'];
                $delnums[] = ' WHEN '.$info['aid'].' THEN delnums+'.$info['count'];
            }
            $upalbums = 'UPDATE ebh_roomalbums  SET nums = CASE aid ';
            $upalbums .= implode(' ',$nums).' END,delnums = CASE aid '.implode(' ',$delnums);
            $upalbums .= ' END WHERE aid IN ('.implode(',',$aids).')';
        }
        //删除模板和首页装扮配置表对应记录
        if(!empty($param['issystem']) && ($param['issystem'] == 2)){//相册类型,0普通相册,1系统相册,2首页装扮模板相册
            $designsql = 'SELECT rp.pid,rp.did,rp.photoname,rd.name FROM ebh_roomphotos rp JOIN ebh_roomdesigns rd ON rp.did=rd.did WHERE rp.pid IN ('.implode(',',$pids).')';
            $designres = $this->db->query($designsql)->list_array();
            if(($designres !==false) && is_array($designres)){
                $dids = array_column($designres,'did');
                if(!empty($dids) && is_array($dids)){
                    $updesign = 'UPDATE ebh_roomdesigns SET status=1 WHERE did IN ('.implode(',',$dids).')';
                }
            }
        }

        if(!empty($upphotos)){
            $result = $this->db->query($upphotos,false);//更新网校图片表sql语句
            if ($result === false) {
                return false;
            }
            $affectedphotos = $this->db->affected_rows();
        }
        if(!empty($upalbums) && !empty($affectedphotos)){
            $result = $this->db->query($upalbums,false);//更新网校相册表sql语句
            if ($result === false) {
                return false;
            }
            $affectedalbums = $this->db->affected_rows();
        }
        if(!empty($updesign) && !empty($affectedphotos) && !empty($affectedalbums)){
            $result = $this->db->query($updesign,false);//更新首页装扮配置表sql语句
            if ($result === false) {
                return false;
            }
        }
        if(empty($affectedphotos) || empty($affectedalbums)){
            return false;
        }
        return TRUE;
    }

    /**
     * 获取图片库图片列表（模板列表）
     * @param $uid,$aid
     */
    public function getGalleryPhotos($param){
        $return = false;
        $sql = 'SELECT rp.pid,rp.uid,rp.crid,rp.aid,rp.photoname,rp.ext,rp.size,rp.server,rp.path,rp.dateline,rp.toptime,rp.width,rp.height,rp.did,rp.clienttype,rp.ishide,rb.alname as topalname,ra.alname,ra.systype,ra.ishide albumsishide,ra.displayorder,ra.issystem From ebh_roomphotos rp';
        $sql .= ' LEFT JOIN  ebh_roomalbums ra ON rp.aid=ra.aid';
        $sql .= ' LEFT JOIN ebh_roomalbums rb ON rb.aid = ra.paid';
        if(!empty($param['aid'])){
            if(!empty($param['toplevel'])){//toplevel等于1表示查询的是主类信息
                $wherearr[] = 'ra.paid = '.intval($param['aid']);   //主类id
            }else{
                $wherearr[] = 'rp.aid = '.intval($param['aid']);    //子类id
            }
        }
        if(!empty($param['systype'])){
            $wherearr[] = 'ra.systype = '.intval($param['systype']);//图片库操作时,结合issystem判断,相册类型,1系统图片,2系统图标
        }
        if(isset($param['q']) && ($param['q'] != null) && ($param['q'] != '')){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'rp.photoname LIKE \'%'.$q.'%\'';
        }
        $wherearr[] = 'rp.del = 0';
        if(isset($param['issystem'])){
            $wherearr[] = 'rp.issystem='.intval($param['issystem']);//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }else{
            $wherearr[] = 'rp.issystem=1';//默认查询1系统相册信息
        }
        $clienttype = isset($param['clienttype']) ? intval($param['clienttype']) : 0;
        $wherearr[] = 'rp.clienttype='.$clienttype;//终端类型：0-电脑版,1-手机版
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        }else{
            $sql.= ' ORDER BY rp.toptime DESC,rp.pid DESC';
        }
        if(empty($param['notpage'])){
            if(!empty($param['limit'])) {
                $sql .= ' LIMIT '.$param['limit'];
            } else {
                if (empty($param['page']) || $param['page'] < 1)
                    $page = 1;
                else
                    $page = $param['page'];
                $pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
                $start = ($page - 1) * $pagesize;
                $sql .= ' LIMIT ' . $start . ',' . $pagesize;
            }
        }
        $photos = $this->db->query($sql)->list_array();
        if(!empty($photos)){
            $return = $photos;
        }
        return $return;
    }
    /**
     * 获取图片库图片列表数量（模板数量）
     * @param $uid,$aid
     */
    public function getGalleryPhotosCount($param){
        $return = false;
        $sql = 'SELECT count(1) as count FROM ebh_roomphotos rp';
        $sql .= ' LEFT JOIN  ebh_roomalbums ra ON rp.aid=ra.aid';
        $sql .= ' LEFT JOIN ebh_roomalbums rb ON rb.aid = ra.paid';
        if(!empty($param['aid'])){
            if(!empty($param['toplevel'])){//toplevel等于1表示查询的是主类信息
                $wherearr[] = 'ra.paid = '.intval($param['aid']);//主类id
            }else{
                $wherearr[] = 'rp.aid = '.intval($param['aid']); //子类id
            }
        }
        if(!empty($param['systype'])){
            $wherearr[] = 'ra.systype = '.intval($param['systype']);//图片库操作时,结合issystem判断,相册类型,1系统图片,2系统图标
        }
        if(isset($param['q']) && ($param['q'] != null) && ($param['q'] != '')){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'rp.photoname LIKE \'%'.$q.'%\'';
        }
        $wherearr[] = 'rp.del = 0';
        if(isset($param['issystem'])){
            $wherearr[] = 'rp.issystem='.intval($param['issystem']);//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }else{
            $wherearr[] = 'rp.issystem=1';//默认查询1系统相册信息
        }
        $clienttype = isset($param['clienttype']) ? intval($param['clienttype']) : 0;
        $wherearr[] = 'rp.clienttype='.$clienttype;//终端类型：0-电脑版,1-手机版
        if(!empty($wherearr)){
            $sql.= ' WHERE '.implode(' AND ',$wherearr);
        }
        $photos = $this->db->query($sql)->row_array();
        if(!empty($photos['count'])){
            $return = $photos['count'];
        }
        return $return;
    }
    /**
     * 获取图片库分类（获取模板库分类）
     * @param $aid
     */
    public function getGallerys($param){
        $return = false;
        $sql = 'SELECT ra.aid,ra.paid,ra.alname,ra.crid,ra.uid,ra.del,ra.nums,ra.delnums,ra.systype,ra.ishide,ra.displayorder,ra.issystem,ra.clienttype FROM ebh_roomalbums ra ';
        $wherearr[] = 'ra.del = 0';
        if(isset($param['issystem'])){
            $wherearr[] = 'ra.issystem='.intval($param['issystem']);//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }else{
            $wherearr[] = 'ra.issystem=1';//默认查询1系统相册信息
        }
        if(isset($param['q'])){
            $q = $this->db->escape_str($param['q']);
            $wherearr[] = 'ra.alname LIKE \'%'.$q.'%\'';
        }
        if(isset($param['paid'])){
            $wherearr[] = 'ra.paid='.intval($param['paid']);        //主类id
        }
        if(!empty($param['systype'])){
            $wherearr[] = 'ra.systype='.intval($param['systype']);  //图片库操作时,结合issystem判断,相册类型,1系统图片,2系统图标
        }
        if(isset($param['ishide'])){
            $wherearr[] = 'ra.ishide='.intval($param['ishide']);    //是否隐藏,1表示隐藏
        }
        $clienttype = isset($param['clienttype']) ? intval($param['clienttype']) : 0;
        $wherearr[] = 'ra.clienttype='.$clienttype;                 //终端类型：0-电脑版,1-手机版
        if(!empty($wherearr)){
            $sql.= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' ORDER BY '.$param['order'];
        }else{
            if(!empty($param['issystem'])){
                $sql.= ' ORDER BY ra.displayorder ASC,ra.aid desc';
            }else{
                $sql.= ' ORDER BY ra.aid DESC';
            }
        }
        $albums = $this->db->query($sql)->list_array();
        if(!empty($albums)){
            $return = $albums;
        }
        return $return;
    }
    /**
     * 根据pid获取图片信息（模板信息）
     * @param $pid
     */
    public function getPhotosByPid($param){
        $return = false;
        $pid = !empty($param['pid']) ? intval($param['pid']) : 0;//图片id
        $issystem = !empty($param['issystem']) ? intval($param['issystem']) : 0;//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        if(empty($pid) || empty($issystem)){
            return $return;
        }
        $sql = 'select rp.pid,rp.uid,rp.crid,rp.aid,rp.photoname,rp.ext,rp.size,rp.server,rp.path,rp.dateline,rp.toptime,rp.width,rp.height,rp.did,rp.clienttype,rp.ishide,ra.alname,ra.paid,ra.systype,ra.ishide albumsishide,ra.displayorder,ra.issystem from ebh_roomphotos rp ';
        $sql .= ' left join ebh_roomalbums ra on rp.aid=ra.aid where rp.del = 0 and rp.pid='.$pid;
        $sql .= ' AND rp.issystem='.$issystem;
        $photos = $this->db->query($sql)->row_array();
        if(!empty($photos)){
            $return = $photos;
        }
        return $return;
    }
    /**
     * 根据aid获取图片库分类信息（模板分类信息）
     * @param $aid
     */
    public function getGalleryByAid($param){
        $return = false;
        $aid = !empty($param['aid']) ? intval($param['aid']) : 0;//相册id
        $issystem = !empty($param['issystem']) ? intval($param['issystem']) : 0;//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        if(empty($aid) || empty($issystem)){
            return $return;
        }
        $sql = 'SELECT ra.aid,ra.paid,ra.alname,ra.crid,ra.uid,ra.del,ra.nums,ra.delnums,ra.systype,ra.ishide,ra.displayorder,ra.issystem,ra.clienttype FROM ebh_roomalbums ra WHERE ra.del = 0 AND ra.aid='.$aid;
        $sql .= ' AND ra.issystem='.$issystem;
        $gallery = $this->db->query($sql)->row_array();
        if(!empty($gallery)){
            $return = $gallery;
        }
        return $return;
    }
}
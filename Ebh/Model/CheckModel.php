<?php

/**
 * ChecModel 审核类夹杂的model
 */
class CheckModel {
    var $db='';
    function __construct() {
        $this->db = Ebh()->db;
    }
      /**
     * 获取课件详情
     * @param int $cwid
     * @return array
     */
    public function getcoursedetail($cwid) {
        $sql = 'select c.cwid,c.uid,c.catid,c.title,c.thumb,c.tag,c.logo,c.images,c.verifyprice,c.edition,c.summary,c.message,c.cwname,c.cwsource,c.cwurl,cwsize,c.dateline,c.ispreview,u.username,u.realname,rc.crid,rc.folderid,rc.sid,rc.isfree,rc.cdisplayorder,f.foldername,c.viewnum,c.ism3u8,c.isrtmp,c.islive,c.liveid,
                ck.admin_status,ck.admin_remark,ck.teach_status,ck.teach_remark,ck.del,ck.admin_dateline,ck.teach_dateline,ck.delline,ck.teach_uid,ck.admin_ip,ck.teach_ip ,ck.admin_uid ' .
                'from ebh_coursewares c ' .
                'join ebh_roomcourses rc on (c.cwid = rc.cwid) ' .
                'join ebh_users u on (u.uid = c.uid) ' .
                'left join ebh_folders f on (f.folderid = rc.folderid) ' .
                'left join ebh_billchecks ck on ck.toid = c.cwid '.
                'where c.cwid=' . $cwid;
        return Ebh()->db->query($sql)->row_array();
    }

    /*
      后台获取课件数量
      @param array $param
      @return int
     */
    public function getcoursewarecount($param) {
        $sql = 'select count(*) count 
                from ebh_coursewares c 
                left join ebh_roomcourses rc on rc.cwid = c.cwid
                left join ebh_billchecks ck on ck.toid = c.cwid and ck.type=1';
        if (isset($param['q'])&&$param['q']!=''){
            $qstr = Ebh()->db->escape_str($param['q']);
            $wherearr[] = ' (c.title like \'%' . $qstr. '%\' )';          
        }
        if(!empty($param['access'])){
            $wherearr[]='rc.crid in ('.Ebh()->db->escape_str($param['access']).')';
        }
       //管理员
        if($param['role']=='admin'){
            if($param['admin_status']>0){
                $wherearr[] = 'ck.admin_status ='.$param['admin_status'];
            }
            if($param['cat']==0){
                $wherearr[] = 'ck.admin_status is null or ck.admin_status = 3';
            }
            if($param['cat']==1){
                $wherearr[] = 'ck.admin_status in(1,2) and ck.del=0';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        //教师    
        }elseif($param['role']=='teach'){
            if($param['teach_status']>0){
                $wherearr[] = '(ck.teach_status ='.$param['teach_status']. ' or ck.admin_status='.$param['teach_status']. ')';
            }
            if($param['cat']==0){
              $wherearr[] = '( ck.teach_status is null or ck.teach_status = 0 ) and (ck.admin_status is null or ck.admin_status = 3 or ck.admin_status = 0)';
            }
            if($param['cat']==1){
                $wherearr[] = '(ck.teach_status>0 and ck.del=0) or (ck.admin_status in(1,2) and ck.del=0)';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        }

        if(!empty($param['crid'])){
            if(is_array($param['crid'])){
                $wherearr[] = 'rc.crid in( '.implode(',', $param['crid']).')';
            }else{
                $wherearr[] = 'rc.crid ='.$param['crid'];
            }
        }
        if (!empty($wherearr))
            $sql.= ' where ' . implode(' AND ', $wherearr);
        //echo $sql;
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

   /*
      后台获取课件列表
      @param array $param
      @return array 列表数组
     */

    public function getcoursewarelist($param) {
        $sql = 'select c.uid,c.cwid,c.cwurl,c.islive,c.cwsource,c.title,c.dateline,c.sub_title,c.cwurl,c.cwsource,c.viewnum,c.status,c.price,ck.teach_uid,ck.admin_dateline,ck.teach_dateline,ck.admin_status,ck.teach_status,ck.del,ck.admin_uid,rc.crid,rc.folderid
             from ebh_coursewares c
            left join ebh_roomcourses rc on rc.cwid = c.cwid 
            left join ebh_billchecks ck on ck.toid = c.cwid and ck.type=1';
        if (isset($param['q'])&&$param['q']!=''){
            $qstr = Ebh()->db->escape_str($param['q']);
            $wherearr[] = ' (c.title like \'%' . $qstr. '%\' )';
        }
        if(!empty($param['access'])){
            $wherearr[]='rc.crid in ('.Ebh()->db->escape_str($param['access']).')';
        }
        //管理员
        if($param['role']=='admin'){
            if($param['admin_status']>0){
                $wherearr[] = 'ck.admin_status ='.$param['admin_status'];
            }
            if($param['cat']==0){
                $wherearr[] = 'ck.admin_status is null or ck.admin_status = 0 or ck.admin_status = 3';
            }
            if($param['cat']==1){
                $wherearr[] = 'ck.admin_status in(1,2) and ck.del=0';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        //教师    
        }elseif($param['role']=='teach'){
            if($param['teach_status']>0){
                $wherearr[] = '(ck.teach_status ='.$param['teach_status']. ' or ck.admin_status='.$param['teach_status']. ')';
            }
            if($param['cat']==0){
                $wherearr[] = '( ck.teach_status is null or ck.teach_status = 0 ) and (ck.admin_status is null or ck.admin_status = 3 or ck.admin_status = 0)';
            }
            if($param['cat']==1){
                $wherearr[] = '(ck.teach_status>0 and ck.del=0) or (ck.admin_status in(1,2) and ck.del=0)';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        }
        if(!empty($param['crid'])){
            if(is_array($param['crid'])){
                $wherearr[] = 'rc.crid in( '.implode(',', $param['crid']).')';
            }else{
                $wherearr[] = 'rc.crid ='.$param['crid'];
            }
        }
        if (!empty($wherearr))
            $sql.= ' where ' . implode(' AND ', $wherearr);
        $sql.=' order by cwid desc';
        if (!empty($param['limit']))
            $sql.= ' limit ' . $param['limit'];
        //log_message($sql);
        $rows =  Ebh()->db->query($sql)->list_array();
        //下面是对应优化代码
        $uidstr = '';
        $cridstr = '';
        $folderidstr = '';
        foreach($rows as $key=>$row){
            if(!empty($row['uid'])){
                $uidstr.=$row['uid'].',';
            }
            if(!empty($row['crid'])){
                $cridstr.= $row['crid'].',';
            }
            if(!empty($row['folderid'])){
                $folderidstr .= $row['folderid'].',';
            }
        }
        $uidstr = rtrim($uidstr, ',');
        $cridstr = rtrim($cridstr, ',');
        $folderidstr = rtrim($folderidstr, ',');
        //用户信息
        /*if($uidstr!=''){
            $usql = 'select uid,username,realname from ebh_users where uid in('.$uidstr.')';
            $uidrows =  Ebh()->db->query($usql)->list_array();
            $uidrows = $this->_arraycoltokey($uidrows,'uid');
        }*/
        //网校信息
        if($cridstr!=''){
            $ssql = 'select crid,crname from ebh_classrooms where crid in('.$cridstr.')';
            $cridrows =  Ebh()->db->query($ssql)->list_array();
            $cridrows = $this->_arraycoltokey($cridrows,'crid');
        }
        //分类名称
        if($folderidstr!=''){
            $fsql =  'select folderid,foldername from ebh_folders where folderid in('.$folderidstr.')';
            $folderidrows =  Ebh()->db->query($fsql)->list_array();
            $folderidrows = $this->_arraycoltokey($folderidrows,'folderid');
        }
        
        foreach($rows as &$row){
            //$row['username'] = $uidrows[$row['uid']]['username'];
            //$row['realname'] = $uidrows[$row['uid']]['realname'];
            $row['crname'] = $cridrows[$row['crid']]['crname'];
            $row['foldername'] = empty($folderidrows[$row['folderid']]['foldername'])?'已被删除的课件':$folderidrows[$row['folderid']]['foldername'];
        } 
        /* echo '<pre>';
        var_dump($rows); */
        return $rows;
    }
    public function getSchoolName($id){
        $sql='select cr.crname from ebh_coursewares c left join ebh_roomcourses rc on rc.cwid = c.cwid left join ebh_classrooms cr on rc.crid=cr.crid where c.cwid='.intval($id);
        $info=Ebh()->db->query($sql)->row_array();
        return $info['crname'];
    }
        /**
     * 二维数组某个列的值作为索引键
     * @param unknown $data
     * @param string $key
     *
     */
    protected  function _arraycoltokey($array, $key = '') {
        if(empty($key)) return ;
        $newarray = array();
        foreach ($array as $row){
            $newarray[$row[$key]] = $row;
        }
        return $newarray;
    }
    /**
     * 获取播放课件时用到的课件详情数据
     * @param int $cwid
     * @return array
     */
    public function getplaycoursedetail($cwid) {
        $sql = 'SELECT cw.cwurl,cw.cwsource,cw.m3u8url,cw.thumb,cw.title,cw.status,cw.ispreview,cw.apppreview,r.isfree,cr.isschool,cr.isshare,cr.ispublic,r.crid,cr.upid,f.fprice,f.folderid FROM ebh_coursewares cw JOIN '
                . 'ebh_roomcourses r ON cw.cwid=r.cwid  left JOIN ebh_folders f ON r.folderid = f.folderid JOIN '
                . 'ebh_classrooms cr ON cr.crid = r.crid where cw.cwid=' . $cwid;
        return Ebh()->db->query($sql)->row_array();
    }
    public function getClassroomId($id){
        $sql='select cr.crid from ebh_coursewares c left join ebh_roomcourses rc on rc.cwid = c.cwid left join ebh_classrooms cr on rc.crid=cr.crid where c.cwid='.intval($id);
        $info=Ebh()->db->query($sql)->row_array();
        return $info['crid'];
    }

        /**
         * 审核处理
         *
         */
        public function check($param){
            $toid = $param['toid'];
            $role = $param['role'];
            $type = $param['type'];
            if(!$toid){return false;}
            //检查是否持存在
            $sql = "select count(*) as count from ebh_billchecks where toid = {$toid} and type = {$type}";
            $row = $this->db->query($sql)->row_array();
            $sql2='select admin_status,teach_status from ebh_billchecks WHERE  toid='.$toid.' and type ='.$type;
            //var_dump($row);exit;
            $domainrow = $this->db->query($sql2)->row_array();
            //var_dump($param);die;
            if(!empty($domainrow['admin_status']) || !empty($domainrow['teach_status'])){
                //更新
                return false;

            }elseif ($row['count']>0) {
                if($role=='admin'){//管理员审核
                    $setArr['admin_uid'] = $param['admin_uid'];
                    $setArr['admin_status'] = $param['admin_status'];
                    $setArr['admin_remark'] = htmlentities($param['admin_remark'],ENT_NOQUOTES,"utf-8");
                    $setArr['admin_ip'] = $param['admin_ip'];
                    $setArr['admin_dateline'] = time();
                }elseif($role=='teach'){//教师审核
                    $setArr['teach_uid'] = $param['teach_uid'];
                    $setArr['teach_status'] = $param['teach_status'];
                    $setArr['teach_remark'] = $param['teach_remark'];
                    $setArr['teach_ip'] = $param['teach_ip'];
                    $setArr['teach_dateline'] = time();
                }
//                var_dump(1);
                $res = $this->db->update("ebh_billchecks",$setArr,array('toid'=>$toid,'type'=>$type));
                //网校对应修改课件等状态               
            }else{
                //添加
                if($role=='admin'){//管理员审核
                    $data = array(
                        'toid'=>$toid,
                        'type'=>$type,
                        'admin_uid'=>$param['admin_uid'],
                        'admin_status'=>$param['admin_status'],
                        'admin_remark'=>htmlentities($param['admin_remark'],ENT_NOQUOTES,"utf-8"),
                                         'teach_remark'=>$param['teach_remark'] ? $param['teach_remark'] : '',
                        'admin_ip'=>$param['admin_ip'],
                        'admin_dateline'=>time(),
                    );
                }elseif($role=='teach'){//教师审核
                    $data = array(
                        'toid'=>$toid,
                        'type'=>$type,
                        'teach_uid'=>$param['teach_uid'],
                        'teach_status'=>$param['teach_status'],
                        'teach_remark'=>$param['teach_remark'],
                        'teach_ip'=>$param['teach_ip'],
                        'teach_dateline'=>time(),
                                            'admin_remark'=>'',
                    );
                }
                    $res = $this->db->insert("ebh_billchecks",$data);

            }
            if($param['type']!=13){
                //更新课件/附件/评论/答疑/回答表
                if($param['teach_status']==2){
                    $this->updatestatus($toid,  $type);
                }
            }
            if($param['type'] == 2){
                if($param['teach_status'] == 1){
                    $this->updatestatusattachment($toid,  $type);
                }
            }
            return  $res;

        }

        /**
         * 批量审核
         */
        public function multcheck($param){
            $idarr = explode(",", $param['ids']);
            if(!is_array($idarr)){
                return false;
            }
            foreach($idarr as $id){
                $param['toid'] = $id;
                $params = $param;
                $ck = $this->check($params);
                if($ck <= 0){
                    break;
                    return false;
                }
            }
            return true;
        }

        /**
         * 撤销审核操作,并还原相关信息
         */
        public function revoke($param){
            $toid = $param['toid'];
            $type = $param['type'];
            $status = $param['status'];
            if($status == 2){
                //获取不通过之前的状态
                $old_status = $this->db->query('select old_status from ebh_billchecks where toid = '.$toid.' and type = '.$type)->row_array();
                $old_status = $old_status['old_status'];
                switch ($type){
                    case 1 : $table = 'ebh_coursewares';
                        $setarr=array('status'=>$old_status);
                        $where= array('cwid'=>$toid);
                        $folderidsql = 'select folderid from `ebh_roomcourses` where cwid ='.intval($toid).' limit 1';
                        $row = $this->db->query($folderidsql)->row_array();
                        $folderid = $row['folderid'];
                        $tableTo = 'ebh_folders';
                        $minus = array('idcolumn'=>'folderid','folderid'=>$folderid,'column'=>'coursewarenum','coursewarenum'=>'coursewarenum+1');
                        break;//课件  status 为-2禁止
                    case 2 : $table = 'ebh_attachments';
                        $setarr=array('status'=>$old_status);
                        $where= array('attid'=>$toid);
                        $attidsql = 'select cwid from `ebh_attachments` where attid ='.intval($toid).' limit 1';
                        $row = $this->db->query($attidsql)->row_array();
                        $cwid = $row['cwid'];
                        $tableTo = 'ebh_coursewares';
                        $minus = array('idcolumn'=>'cwid','cwid'=>$cwid,'column'=>'attachmentnum','attachmentnum'=>'attachmentnum+1');
                        break;//附件 0未审核 1审核通过 -1审核未通过 默认1
                    case 3 : $table = 'ebh_reviews';
                        $setarr=array('shield'=>$old_status);
                        $where= array('logid'=>$toid);
                        break;//评论 shield为1屏蔽
                    case 4 : $table = 'ebh_askquestions';
                        $setarr=array('shield'=>$old_status);
                        $where= array('qid'=>$toid);
                        break;//答疑 shield为1屏蔽
                    case 5 : $table = 'ebh_askanswers';
                        $setarr=array('shield'=>$old_status);
                        $where= array('aid'=>$toid);
                        break;//回答 shield为1屏蔽
                    case 6:  $table = 'portal.ebh_previews';
                        $setarr=array('status'=>$old_status);
                        $where= array('reviewid'=>$toid);
                        break;//主站评论 status为2锁定 1不锁定 默认1
                    case 7:  $table = 'ebh_schexams';
                        $setarr=array('status'=>$old_status);
                        $where= array('eid'=>$toid);
                        break;//作业 status:0表示临时保存，1表示已提交（不能编辑），-1已删除
                }
                $this->db->update($table,$setarr,$where);
                if(isset($tableTo)){
                    $minussql = 'update '.$tableTo.' set '.$minus['column'].' = '.$minus[$minus['column']].' where '.$minus['idcolumn'].' = '.$minus[$minus['idcolumn']];
                    $this->db->query($minussql); 
                }
                //评论加1
                if($type==3){
                    $dsql = " select r.toid,r.logid,cw.cwid from ebh_reviews r left join ebh_coursewares cw on cw.cwid = r.toid where logid = {$toid} ";
                    $drow = $this->db->query($dsql)->row_array();
                    if(!empty($drow)){
                        $upsql = "update ebh_coursewares set reviewnum = reviewnum+1 where cwid = {$drow['cwid']}";
                        $this->db->query($upsql);
                    }
                }

            }
            if($status == 1 && $type == 2){
                $this->db->update('ebh_attachments',array('status'=>0),array('attid'=>$toid));
            }
            $data = array(
                'teach_status' => $param['teach_status'],
                //'admin_status' => $param['teach_status'],
                'teach_uid' => $param['teach_uid'],
            );
            $this->db->update("ebh_billchecks",$data,array('toid'=>$toid,'type'=>$type));
            return $this->db->affected_rows();
        }
        /**
         * 更新网校课件,附件等状态
         *
         */
        public function updatestatus($toid,$type){
            switch ($type){
                case 1 : $table = 'ebh_coursewares';
                         $setarr=array('status'=>-2);
                         $where= array('cwid'=>$toid);
                         $folderidsql = 'select folderid from `ebh_roomcourses` where cwid ='.intval($toid).' limit 1';
                         $row = $this->db->query($folderidsql)->row_array();
                         $folderid = $row['folderid'];
                         $tableTo = 'ebh_folders';
                         $minus = array('idcolumn'=>'folderid','folderid'=>$folderid,'column'=>'coursewarenum','coursewarenum'=>'coursewarenum-1');
                         break;//课件  status 为-2禁止
                case 2 : $table = 'ebh_attachments';
                         $setarr=array('status'=>-1);
                         $where= array('attid'=>$toid);
                         $attidsql = 'select cwid from `ebh_attachments` where attid ='.intval($toid).' limit 1';
                         $row = $this->db->query($attidsql)->row_array();
                         $cwid = $row['cwid'];
                         $tableTo = 'ebh_coursewares';
                         $minus = array('idcolumn'=>'cwid','cwid'=>$cwid,'column'=>'attachmentnum','attachmentnum'=>'attachmentnum-1');
                         break;//附件
                case 3 : $table = 'ebh_reviews';
                         $setarr=array('shield'=>1);
                         $where= array('logid'=>$toid);
                         break;//评论 shield为1屏蔽
                case 4 : $table = 'ebh_askquestions';
                         $setarr=array('shield'=>1);
                         $where= array('qid'=>$toid);
                         break;//答疑 shield为1屏蔽
                case 5 : $table = 'ebh_askanswers';
                         $setarr=array('shield'=>1);
                         $where= array('aid'=>$toid);
                         break;//回答 shield为1屏蔽
                case 6:  $table = 'portal.ebh_previews';
                         $setarr=array('status'=>2);
                         $where= array('reviewid'=>$toid);
                         break;//主站评论 status为2锁定
                case 7:  $table = 'ebh_schexams';
                         $setarr=array('status'=>-1);
                         $where= array('eid'=>$toid);
                         break;//作业 status为-1删除
            }
            //记录审核不通过之前的状态
            if(in_array($type,array(1,2,3,4,5,6,7))){
                $key = array_keys($setarr);
                $wherekey = array_keys($where);
                $sta = $this->db->query('select '.$key[0].' from '.$table.' where '.$wherekey[0].' = '.$toid)->row_array();
                $updatesql = 'update ebh_billchecks set old_status = '.intval($sta[$key[0]]).' where toid ='.$toid.' and type ='.$type;
                $this->db->query($updatesql);
            }
            if(isset($tableTo)){
                $minussql = 'update '.$tableTo.' set '.$minus['column'].' = '.$minus[$minus['column']].' where '.$minus['idcolumn'].' = '.$minus[$minus['idcolumn']];
                $this->db->query($minussql); 
            }
            $this->db->update($table,$setarr,$where);
            $dsql = " select r.toid,r.logid,cw.cwid from ebh_reviews r left join ebh_coursewares cw on cw.cwid = r.toid where logid = {$toid} ";
                $drow = $this->db->query($dsql)->row_array();
            if($type==3){
                
                if(!empty($drow)){
                    $upsql = "update ebh_coursewares set reviewnum = reviewnum-1 where cwid = {$drow['cwid']}";
                    $this->db->query($upsql);
                }
            }
        }
        /**
         * 删除处理
         */
        public function del($param){
            $setArr['del'] = 1;
            $setArr['delline'] = time();
            $whereArr['toid'] = $param['toid'];
            $whereArr['type'] = $param['type'];
            //课件附件表字段删除更新处理(逻辑删除)
            if($param['type']==1){//课件
                $sql = 'SELECT c.uid,rc.crid,f.folderid,f.folderlevel,f.upid FROM ebh_coursewares c LEFT JOIN ebh_roomcourses rc ON c.cwid = rc.cwid LEFT JOIN ebh_folders f ON f.folderid = rc.folderid WHERE c.cwid=' . $param['toid'];
                $course = $this->db->query($sql)->row_array();

                $folder = $course;
                $folderid = $folder['folderid'];
                $folderlevel = $folder['folderlevel'];
                while($folderlevel>1){
                    $folder = $this->db->query('select folderid,folderlevel,upid from ebh_folders where folderid='.$folder['upid'])->row_array();
                    $folderlevel = $folder['folderlevel'];
                    $folderid = $folder['folderid'];
                     $this->db->update(
                        'ebh_folders',
                        array(),
                        'folderid='.$folderid,
                        array('coursewarenum'=>'coursewarenum-1')
                    );//课程对应课件数 
                }

                //教室对应课件数
                $this->db->update(
                    'ebh_classrooms',
                    array(),
                    'crid='.$course['crid'],
                    array('coursenum'=>'coursenum-1')
                );
                //教师课件数
/*              $this->db->update(
                    'ebh_teachers',
                    array(),
                    'teacherid='.$course['uid'],
                    array('cwcount'=>'cwcount-1')
                );*/

                $this->db->update("ebh_coursewares",array('status'=>-3),array('cwid'=>$param['toid']));
            }
            elseif($param['type']==6){
                $this->db->delete('portal.ebh_previews',array('reviewid'=>$param['toid']));
            }
            $this->db->update("ebh_billchecks",$setArr,$whereArr);
        }

        /**
         * 获取问答或者答疑标题与uid
         */
        public function getinforow($table,$where){
            if($table=='ebh_askquestions'){//问题
                $sql = "select uid,title,reward,qid,dateline from ebh_askquestions where ".key($where)." = ".current($where);
            }elseif($table=='ebh_askanswers'){//回答
                $sql = "select uid,message as title,qid,dateline from ebh_askanswers where ".key($where)." = ".current($where);
            }
            //echo $sql;
            $row = $this->db->query($sql)->row_array();
            $row['title'] = shortstr(filterhtml($row['title']),20);
            if(empty($row['reward'])){
                $row['reward'] = 0;
            }
            return $row;
        }

        /**
         * 获取用户积分
         */
        public function getCredit($uid){
            $sql = "select uid,credit from ebh_users where uid = $uid" ;
            $row = $this->db->query($sql)->row_array();
            return $row;
        }
//
//        /**
//         * 敏感字替换
//         * $param:字符串或者一维数组
//         * $array:若是二维数组填true
//         */
//        public function replace($param, $istwo = false ){
//            if(!$istwo){
//                $bad = file(S_ROOT.'bad.txt');//获取敏感字库
//                $text = preg_replace("/(\r\n|\n|\r|\t)/i", '', $bad);//去数组中的空行
//                $m = "<b style='color: #ff0000'>*</b>";
//                return (str_replace($text, $m, $param));//替换敏感字
//            }
//            $array = array();
//            foreach($param as $value){
//                $array[] = $this->replace($value);
//            }
//            return $array;
//        }

        /**
         * @param $param
         * @istwo $istwo 是否为二维数组
         * @replace $replace 设置敏感字替换的符号
         * @return array
         * 修改敏感字的样式
         */
        public function str_change($param, $istwo = false, $replace = '')
        {
//            $bad = file(S_ROOT . 'bad.txt');//获取敏感字库
            $sql = 'select keyword from kf_sensitives';
            $text = $this->db->query($sql)->list_array();//获取敏感词列表
//            $text = preg_replace("/(\r\n|\n|\r|\t)/i", '', $bad);//去数组中的空行
            $array = array();
            if($istwo){
                foreach ($param as $value){
                    foreach ($text as $v) {
                        $bad = $replace ? $replace :"<b style='color: #ff0000'>" . $v['keyword'] . "</b>";
                        $value = (str_replace($v['keyword'], $bad, $value));
                    }
                    $array[] = $value;
                }
                return $array;
            }else{
                foreach($text as $v){
                    $bad = $replace ? $replace : "<b style='color: #ff0000'>" . $v['keyword'] . "</b>";
                    $param = (str_replace($v['keyword'],$bad,$param));
                }
                return $param;
            }
        }



        /**
        *域名审核时把备案信息写进domainchecks表
        */

   public function inserticp($param){
       $toid = $param['toid'];
       $role = $param['role'];
       $icp = $param['icp'];
       empty($icp)?'':$icp;
        if(!$toid){return false;}


       if ($role == 'admin') {//管理员审核
           $setArr['icp'] = $icp;

       } elseif ($role == 'teach') {//教师审核
           $setArr['icp'] = $icp;
       }
                //print_r($setArr);die;
       $res = $this->db->update("ebh_domainchecks", $setArr, array('crid' => $toid));
       return $res;

     }

    public function updatestatusattachment($toid,$type){
        if(empty($toid) || empty($type)){
            return false;
        }
        $this->db->update("ebh_attachments",array('status'=>1),array('attid'=>$toid));
    }

  
}


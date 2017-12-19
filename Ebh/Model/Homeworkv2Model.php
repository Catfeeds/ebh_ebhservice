    <?php 
    	//作业模型
    class Homeworkv2Model {
        function __construct() {
            $dataserver = Ebh()->config->get('dataserver');
            $servers = $dataserver['servers'];
            //随机抽取一台服务器
            $this->target_server = $servers[array_rand($servers,1)];
            $this->ebhdb = Ebh()->db;
        }
        /**
         * 获取作业列表
         * @return [type] [description]
         */
        public function getHomeworkList($param) {
            //模糊查询，去掉了之前的用户名字查询，改为半匹配，保留网校名查询
            $postParam['pagesize'] = $param['pagesize'];
            if (!empty($param['q'])) {
                $postParam['q'] = $param['q'];
            }
            if (!empty($param['access'])) {
                $postParam['crids'] = explode(',', $param['access']);
            }
            if ($param['cat'] == 0) {
                $mem = Ebh()->cache;
                $maxeid = $mem->get('maxeid');//已审核的最大eid
                if (!$maxeid) {
                    $maxeid = $this->ebhdb->query('select max(toid) as maxeid from ebh_billchecks where type=14')->row_array();
                    $maxeid = $maxeid['maxeid'];
                }
                if ($maxeid) {
                    $postParam['eid'] = $maxeid;
                }
            } 
            if ($param['teach_status'] OR 1 == $param['cat']) {//有帅选，而不是全部的作业
                if ($param['role']=='admin') {
                    if ($param['teach_status']>0) {
                        $ckwherearr[] = 'ck.teach_status ='.$param['teach_status'];
                    }
                    if ($param['cat']==0) {
                        $ckwherearr[] = '(ck.teach_status = 0 or ck.teach_status = 3)';
                    }
                    if ($param['cat']==1) {
                        $ckwherearr[] = '(ck.teach_status in(1,2) and ck.del=0)';
                    }
                    if ($param['cat']==2) {
                        $ckwherearr[] = 'ck.del=1';
                    }
                } elseif ($param['role']=='teach') {
                    if ($param['teach_status']>0) {
                        $ckwherearr[] = '(ck.teach_status ='.$param['teach_status']. ' or ck.admin_status='.$param['teach_status']. ')';
                    }
                    if ($param['cat']==0) {
                        $wherearr[] = '( ck.teach_status is null or ck.teach_status = 0 ) and (ck.admin_status is null or ck.admin_status = 3 or ck.admin_status = 0)';
                    }
                    if ($param['cat']==1) {
                        $ckwherearr[] = 'ck.teach_status>0 and ck.del=0';
                    }
                    if ($param['cat']==2) {
                        $ckwherearr[] = 'ck.del=1';
                    }
                }
                $ckwherearr[] = 'ck.type=14';
                if (!empty($param['crid'])) {
                    $ckwherearr[] = 'ek.crid='.$param['crid'];
                }
                if (!empty($param['q'])) {
                    $ckwherearr[] = 'ek.subject like \'%'.$param['q'].'%\'';
                }
                $ckSql = 'select ck.toid as eid from ebh_billchecks ck left join ebh_examchecks ek on (ck.toid=ek.eid)';
                $countSql = 'select count(toid) as count from ebh_billchecks ck left join ebh_examchecks ek on (ck.toid=ek.eid)';
                if(!empty($ckwherearr)) {
                    $ckSql.= ' WHERE '.implode(' AND ',$ckwherearr);
                    $countSql.= ' WHERE '.implode(' AND ',$ckwherearr);
                }
                if(!empty($param['limit'])) {
                    $ckSql.= ' limit ' . $param['limit']; 
                } else {
                    if (empty($param['page']) || $param['page'] < 1)
                        $page = 1;
                    else
                        $page = $param['page'];
                    $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
                    $start = ($page - 1) * $pagesize;
                    $ckSql .= ' limit ' . $start . ',' . $pagesize;
                }
                $count = $this->ebhdb->query($countSql)->row_array();
                $ckResult = $this->ebhdb->query($ckSql)->list_array();//有帅选的分页
                $ret['count'] = $count['count'];
                if (empty($ckResult))
                    return array();
            }
            
            if (!empty($param['crid'])) {
                $postParam['crids'] = array(intval($param['crid']));
            }
            if (!empty($ckResult)) {//帅选选择通过或者不通过,分页自己定
                $postParam['url'] = '/exam/exambyeids';
                foreach ($ckResult as $value) {
                    $postParam['eids'][] = $value['eid'];
                }
                $postRet = $this->_doPost($postParam);
            } else {
                $postParam['url'] = '/exam/allexam';
                if (empty($param['page']) || $param['page'] < 1)
                    $postParam['page'] = 1;
                else
                    $postParam['page'] = $param['page'];
                $postRet = $this->_doPost($postParam);
            }
            if ( 0 == $postRet['errCode']) {
                if (!isset($ret['count'])) {
                    $ret['count'] = $postRet['datas']['pageInfo']['totalElement'];
                }
                $eResult = empty($postRet['datas']['examList'])?array():$postRet['datas']['examList'];
            }

            //作业,调用java接口
            if (empty($eResult))
                return array();
            foreach ($eResult as $key => $value) {
                $eid[] = $value['eid'];
                $uid[] = $value['uid'];
                $crid[] = $value['crid'];
            }
            //学校
            $schSql = 'select c.crname,c.crid from ebh_classrooms c where c.crid in('.implode(',', array_unique($crid)).')';
            $roomResult = $this->ebhdb->query($schSql)->list_array();
            foreach ($roomResult as $key => $value) {
                $rooms[$value['crid']] = $value;
            }
            //用户
            $userSql = 'select u.realname,u.uid from ebh_users u where u.uid in('.implode(',', array_unique($uid)).')';
            $userResult = $this->ebhdb->query($userSql)->list_array();
            foreach ($userResult as $key => $value) {
                $users[$value['uid']] = $value;
            }
            //审核表
            $ckSql = 'select ck.teach_status,ck.admin_status,ck.admin_uid,ck.admin_dateline,ck.teach_dateline,ck.toid,ck.del,ck.teach_uid from ebh_billchecks ck where ck.toid in('.implode(',', array_unique($eid)).') and ck.type=14';
                $ckResult = $this->ebhdb->query($ckSql)->list_array();
            if (empty($param['teach_status'])) {//全部
                if (!empty($ckResult)) {
                    foreach ($ckResult as $key => $value) {
                        $status[$value['toid']] = $value;
                    }
                    foreach ($eResult as &$value) {
                        $value['crname'] = $rooms[$value['crid']]['crname'];
                        $value['realname'] = $users[$value['uid']]['realname'];
                        $value['teach_uid'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['teach_uid'];
                        $value['teach_dateline'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['teach_dateline'];
                        $value['admin_dateline'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_dateline'];
                        $value['admin_uid'] =  empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_uid'];
                        $value['admin_status'] =  empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_status'];
                        $value['teach_status'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['teach_status'];
                    }
                } else {
                    foreach ($eResult as &$value) {
                        $value['crname'] = $rooms[$value['crid']]['crname'];
                        $value['realname'] = $users[$value['uid']]['realname'];
                        $value['teach_uid'] = 0;
                        $value['teach_dateline'] = 0;
                        $value['admin_dateline'] = 0;
                        $value['admin_uid'] = 0;
                        $value['teach_status'] = 0;
                        $value['admin_status'] = 0;
                    }
                }
            } else {
                if (!empty($ckResult)) {
                    foreach ($ckResult as $key => $value) {
                        $status[$value['toid']] = $value;
                    }
                }
                //构造返回数据
                foreach ($eResult as &$value) {
                    $value['teach_uid'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['teach_uid'];
                    $value['crname'] = $rooms[$value['crid']]['crname'];
                    $value['realname'] = $users[$value['uid']]['realname'];
                    $value['teach_status'] = $param['teach_status'];
                    $value['teach_dateline'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['teach_dateline'];
                    $value['admin_dateline'] = empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_dateline'];
                    $value['admin_uid'] =  empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_uid'];
                    $value['admin_status'] =  empty($status[$value['eid']]) ? 0:$status[$value['eid']]['admin_status'];
                }
            }
            $ret['examList'] = $eResult;
            return $ret;
        }

    /**
     *删除作业
     */
    public function deleteHomework($eid,$uid){
        if (!$eid)
            return FALSE;
        $postParam['url'] = '/exam/delete/'.$eid;
        $postParam['eid'] = $eid;
        $postParam['uid'] = intval($uid);
        $postRet = $this->_doPost($postParam);
        if ($postRet)
            return TRUE;
        else
            return FALSE;

    }

   /**
    *更新作业状态，以及审核表
    */
    public function updatestatus($eid,$uid){
        if (!$eid)
            return FALSE;
        //记录审核不通过之前的状态
        $postParam['eids'] = array($eid);
        $postParam['url'] = '/exam/exambyeids';
        $postRet = $this->_doPost($postParam);
        $sta = $postRet['datas']['examList']['0']['dtag'];
        $updatesql = 'update ebh_billchecks set old_status = '.intval($sta['dtag']).' where type=14 and toid ='.$eid;
        $this->ebhdb->query($updatesql);

        //设置新的状态
        return $this->deleteHomework($eid,$uid);
    }

    /**
     *通过作业id查找作业信息
     */
    public function getHomeworkById($id){
        //作业exam/exambyeids
        $postParam['eids'] = array($id);
        $postParam['url'] = '/exam/exambyeids';
        $postRet = $this->_doPost($postParam);
        $eResult = $postRet['datas']['examList']['0'];
        if (empty($eResult))
            return array();
        //权限表
        $check = $this->ebhdb->query('select ck.teach_status,ck.teach_remark,ck.teach_ip,ck.teach_dateline,ck.teach_uid from ebh_billchecks ck where ck.type=14 and ck.toid='.$eResult['eid'])->row_array();
        //学校
        $schSql = 'select c.crname,c.crid from ebh_classrooms c where c.crid ='.$eResult['crid'];
        $roomResult = $this->ebhdb->query($schSql)->row_array();
        //用户
        $userSql = 'select u.realname,u.uid from ebh_users u where u.uid ='.$eResult['uid'];
        $userResult = $this->ebhdb->query($userSql)->row_array();
        $eResult['crname'] = $roomResult['crname'];
        $eResult['title'] = $eResult['esubject'];
        $eResult['realname'] = $userResult['realname'];
        if ($check) {
            $eResult['teach_ip'] = $check['teach_ip'];
            $eResult['teach_dateline'] = $check['teach_dateline'];
            $eResult['teach_uid'] = $check['teach_uid'];
            $eResult['teach_remark'] = $check['teach_remark'];
            $eResult['teach_status'] = $check['teach_status'];
        }
        return $eResult;
    }

    /**
     *审核作业
     */
    public function checkexam($param) {
        $mem = Ebh()->cache;
        $maxeid = intval($mem->get('maxeid'));//已审核的最大eid,只能审核大于上次审核eid的作业,跳着审核就相当于中间断档的都不能审核了，不加入审核表
        $toid = $param['toid'];
        if ($toid < $maxeid) {
            return array($toid);
        }
        $role = $param['role'];
        if(!$toid){return false;}
        $sql = "select count(*) as count from ebh_billchecks where type=14 and toid = {$toid}";
        $row = $this->ebhdb->query($sql)->row_array();
        $sql2='select teach_status,teach_status from ebh_billchecks where type=14 and toid='.$toid;
        $domainrow = $this->ebhdb->query($sql2)->row_array();
        if(!empty($domainrow['teach_status']) || !empty($domainrow['teach_status'])){
            //更新
            return false;

        }elseif ($row['count']>0) {
            if($role=='teach'){//管理员审核
                $setArr['teach_uid'] = $param['teach_uid'];
                $setArr['teach_status'] = $param['teach_status'];
                $setArr['teach_remark'] = htmlentities($param['teach_remark'],ENT_NOQUOTES,"utf-8");
                $setArr['teach_ip'] = $param['teach_ip'];
                $setArr['teach_dateline'] = time();
            }elseif($role=='teach'){//教师审核
                $setArr['teach_uid'] = $param['teach_uid'];
                $setArr['teach_status'] = $param['teach_status'];
                $setArr['teach_remark'] = $param['teach_remark'];
                $setArr['teach_ip'] = $param['teach_ip'];
                $setArr['teach_dateline'] = time();
            }
            $setArr['type'] = 14;
            $res = $this->ebhdb->update("ebh_billchecks",$setArr,array('toid'=>$toid));
            //网校对应修改课件等状态               
        }else{
            //添加
            if($role=='teach'){//管理员审核
                $data = array(
                    'toid'=>$toid,
                    'type'=>14,
                    'teach_uid'=>$param['teach_uid'],
                    'teach_status'=>$param['teach_status'],
                    'teach_remark'=>htmlentities($param['teach_remark'],ENT_NOQUOTES,"utf-8"),
                                     'teach_remark'=>$param['teach_remark'] ? $param['teach_remark'] : '',
                    'teach_ip'=>$param['teach_ip'],
                    'teach_dateline'=>time(),
                );
            }elseif($role=='teach'){//教师审核
                $data = array(
                    'toid'=>$toid,
                    'type'=>14,
                    'teach_uid'=>$param['teach_uid'],
                    'teach_status'=>$param['teach_status'],
                    'teach_remark'=>$param['teach_remark'],
                    'teach_ip'=>$param['teach_ip'],
                    'teach_dateline'=>time(),
                    'teach_remark'=>'',
                );
            }
            $res = $this->ebhdb->insert("ebh_billchecks",$data);
            if ($res) {
                $mem->set('maxeid',$toid,0);
                $insertArr = array(
                    'crid'=>$param['crid'],
                    'subject'=>$param['subject'],
                    'eid'=>$toid
                );
                $this->addExamchecks($insertArr);
            }
        }
        if($param['teach_status']==2 || $param['teach_status']==2){
            $this->updatestatus($toid,$param['uid']);
        }
        return  $res;
    }

    /**
     * 批量审核
     */
    public function multcheckexam($param){
        $idarr = explode(",", $param['ids']);
        if(!is_array($idarr)){
            return false;
        }
        foreach($idarr as $id){
            $param['toid'] = $id;
            $params = $param;
            $ck = $this->checkexam($params);
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
        $status = $param['status'];
        if($status == 2){
            //获取不通过之前的状态
            $old_status = $this->ebhdb->query('select old_status from ebh_billchecks where type=14 and toid = '.$toid)->row_array();
            $old_status = $old_status['old_status'];
            $table = 'ebh_exams';
            $setarr=array('dtag'=>$old_status);
            $where= array('toid'=>$toid,'type'=>14);
            //$this->ebhdb->update($table,$setarr,$where);//java接口还原
            
            //审核未通过 扣除1积分 包含有(答疑审核,回答审核) 写入日志
        }
        $data = array(
            'teach_status' => $param['teach_status'],
            'admin_status' => $param['teach_status'],
            'teach_uid' => $param['teach_uid'],
        );
        $this->ebhdb->update("ebh_billchecks",$data,array('toid'=>$toid,'type'=>14));
        return $this->ebhdb->affected_rows();
    }

    /**
    *添加审核记录
    */
    function addExamchecks($param = array()) {
        if (empty($param['crid']) || empty($param['eid'])) {
            return false;
        }
        $eid = intval($param['eid']);
        $subject = $param['subject'];
        $crid = empty($param['crid']) ? 0 : intval($param['crid']);
        $setarr = array('eid'=>$eid,'crid'=>$crid,'subject'=>$subject);
        $fid = $this->ebhdb->insert('ebh_examchecks', $setarr);
        if($fid){
            return $fid;
        } else{
            return 0;
        }
    }

    /**
     *作业统一请求函数
     */
    private function _doPost($postParam,$isJson=FALSE) {
        if (!empty($postParam['eid'])) {
            $postParam['eid'] = intval($postParam['eid']);
        }
        $url = 'http://'.$this->target_server.$postParam['url'];
        unset($postParam['url']);
        $postParam['size'] = empty($postParam['pagesize']) ? 50 : $postParam['pagesize'];

        if (!empty($postParam['uid'])) {
            $uid = $postParam['uid'];
        } else {
            $uid = 1;
        }
        $postParam['k'] = authcode(json_encode(array('uid'=>$uid,'crid'=>1,'t'=>SYSTIME)),'ENCODE');
        $postParam = json_encode($postParam);
        $res = do_post($url,$postParam,TRUE,TRUE);
        if ($isJson) {
            return $res;
        } else {
             return json_decode($res,1);
        }
    }

}

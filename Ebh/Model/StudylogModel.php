<?php
	/**
	* 学习记录model对应的ebh_studylog表	
	* 主要记录和查询学生听课时间
	*/
	class StudylogModel{
		/**
		*添加听课记录，如果已经存在就更新最大的时间
		*todo:此处可能涉及积分问题，暂留
		*/
		public function addlog($param) {
			if(empty($param['cwid']) || empty($param['uid']) || empty($param['ctime']) || empty($param['ltime']) ) {
				return false;
			}
//			log_message(print_r($param,true));
//			return false;
			$cwid = $param['cwid'];	//课件编号
			$uid = $param['uid'];	//用户编号

			$ctime = $this->_ensureCtime($cwid,$param['ctime']); //课件时长

			$ltime = $param['ltime'];	//学习持续时间
			$finished = $param['finished']; //是否听完
			$curtime = empty($param['curtime'])?0:$param['curtime']; //当前进度条时间
			$cache = Ebh::app()->getCache();
			$keyparam = array('uid'=>$uid,'cwid'=>$cwid);
			$id1 = $cache->getcachekey('playlogs_total',$keyparam);
			$id2 = $cache->getcachekey('playlogs_each',$keyparam);
			if(!empty($param['logid'])){
				$row = $cache->get($id1);
				// log_message('第一次请求之后数据走缓存');
			}else{
				$cache->remove($id1);
				$cache->remove($id2);
				// log_message('第一次清除缓存，数据走数据库');
			}
			if(empty($row)){
				$existssql = 'SELECT p.logid,p.ctime,p.ltime FROM ebh_playlogs p WHERE p.cwid='.$cwid.' and p.uid='.$uid .' and totalflag=1';
				$row = Ebh()->db->query($existssql)->row_array();
			}
			if(!empty($row)) {	//记录存在则更新记录(总记录)
				$logid = $row['logid'];
				$wherearr = array('logid'=>$logid);
				$setarr = array('lastdate'=>SYSTIME);
				if($row['ctime'] != $ctime){
					$setarr['ctime'] = $ctime;
					$row['ctime'] = $ctime;
				}
				if($row['ltime'] < $ltime){
					$setarr['ltime'] = $ltime;
					$row['ltime'] = $ltime;
				}
				if($finished == 1)
					$setarr['finished'] = 1;
				$setarr['curtime'] = $curtime;
				$result = Ebh()->db->update('ebh_playlogs',$setarr,$wherearr);
				$cache->set($id1,$row,86400);

			} else {	//不存在则生成新纪录(总记录)
				$setarr = array('cwid'=>$cwid,'uid'=>$uid,'ctime'=>$ctime,'ltime'=>$ltime,'startdate'=>(SYSTIME-$ltime),'lastdate'=>SYSTIME,'totalflag'=>1,'curtime'=>$curtime);
				if($finished == 1)
					$setarr['finished'] = 1;
				$result = Ebh()->db->insert('ebh_playlogs',$setarr);
			}
			if(empty($param['logid'])){
				$logid = 0;
			}else{
				$logid = $param['logid'];
			}
			if(!empty($logid)){
				$row2 = $cache->get($id2);
				if(empty($row2)){
					$existssql_one = 'SELECT p.logid,p.ctime,p.ltime FROM ebh_playlogs p WHERE p.cwid='.$cwid.' and p.uid='.$uid .' and totalflag=0 and p.logid='.$logid;
					$row2 = Ebh()->db->query($existssql_one)->row_array();
				}
			}
			if(!empty($row2)) {	//记录存在则更新记录(每次听课单条记录)
				$logid = $row2['logid'];
				$wherearr = array('logid'=>$logid);
				$setarr2 = array('lastdate'=>SYSTIME);

				if($row2['ctime'] != $ctime){
					$setarr2['ctime'] = $ctime;
					$row2['ctime'] = $ctime;
				}
				if($row2['ltime'] < $ltime){
					$setarr2['ltime'] = $ltime;
					$row2['ltime'] = $ltime;
				}
				if($finished == 1)
					$setarr2['finished'] = 1;
				$setarr2['curtime'] = $curtime;
				$result2 = Ebh()->db->update('ebh_playlogs',$setarr2,$wherearr);
				// if($result2){
					$cache->set($id2,$row2,86400);
				// }
			} else {	//不存在则生成新纪录(每次听课单条记录)
				$setarr2 = array('cwid'=>$cwid,'uid'=>$uid,'ctime'=>$ctime,'ltime'=>$ltime,'startdate'=>(SYSTIME-$ltime),'lastdate'=>SYSTIME,'totalflag'=>0,'curtime'=>$curtime);
				if($finished == 1)
					$setarr2['finished'] = 1;
				$logid = Ebh()->db->insert('ebh_playlogs',$setarr2);

				//同步SNS数据,只在单次听课记录生成时同步
				Ebh::app()->lib('Sns')->do_sync($uid, 2);
			}
			return $logid;
		}
		/**
		 * 根据参数获取对应的学习记录列表
		 * @param array $param
		 * @return array
		 */
		public function getList($param=array()){
			$sql = 'SELECT s.logid,u.username,cw.title,s.price,s.credit,s.fromip,s.dateline FROM ebh_studylogs s left join ebh_coursewares cw on s.cwid = cw.cwid left join ebh_users u on s.uid = u.uid ';
			$whereArr = array();
			if(!empty($param['begintime'])){
				$whereArr[] = ' s.dateline >= '.strtotime($param['begintime']);
			}
			if(!empty($param['endtime'])){
				$whereArr[] = ' s.dateline < '.strtotime($param['endtime']);
			}
			if(!empty($param['searchkey'])){
				$whereArr[] = ' cw.title like \'%'.$param['searchkey'].'%\' or u.username = '.intval($param['searchkey']);
			}
			if(!empty($param['uid'])){
				$whereArr[] = ' s.uid ='.$param['uid'];
			}
			if(!empty($whereArr)){
				$sql.=' WHERE '.implode(' AND ',$whereArr);
			}
			if(!empty($param['order'])){
				$sql.=' order by '.$param['order'];
			}else{
				$sql.=' order by s.dateline desc ';
			}
			if(!empty($param['limit'])){
				$sql.= 'limit '.$param['limit'];
			}else{
				$sql.= 'limit 0,20'; 
			}
			return Ebh()->db->query($sql)->list_array();
		}
		/**
		 * 根据参数获取对应的学习记录条数
		 * @param array $param
		 * @return int
		 */
		public function getListCount($param){
			$sql = 'SELECT count(s.logid) count FROM ebh_studylogs s left join ebh_coursewares cw on s.cwid = cw.cwid left join ebh_users u on s.uid = u.uid ';
			$whereArr = array();
			if(!empty($param['begintime'])){
				$whereArr[] = ' s.dateline > '.strtotime($param['begintime']);
			}
			if(!empty($param['endtime'])){
				$whereArr[] = ' s.dateline < '.strtotime($param['endtime']);
			}
			if(!empty($param['searchkey'])){
				$whereArr[] = ' cw.title like \'%'.$param['searchkey'].'%\' or u.username = '.intval($param['searchkey']);
			}
			if(!empty($whereArr)){
				$sql.=' WHERE '.implode(' AND ',$whereArr);
			}
			$res = Ebh()->db->query($sql)->row_array();
			if($res!=false){
				return $res['count'];
			}else{
				return 0;
			}
		}
		/**
		 * 根据参数获取对应的学习记录条数
		 * @param array $param
		 * @return int
		 */
		public function getStudyCount($param){
			$count = 0;
			$sql = 'select count(*) count from ebh_playlogs p '.
					'join ebh_coursewares c on (p.cwid = c.cwid) '.
					'join ebh_roomcourses rc on (rc.cwid = p.cwid) ';
			$wherearr = array();
			if(!empty($param['uid']))
				$wherearr[] = 'p.uid='.$param['uid'];
			if(!empty($param['crid']))
				$wherearr[] = 'rc.crid='.$param['crid'];
			if(!empty($param['startDate']))
				$wherearr[] = 'p.lastdate>='.$param['startDate'];
			if(!empty($param['endDate']))
				$wherearr[] = 'p.lastdate<'.$param['endDate'];
			if(!empty($param['q'])){
				$wherearr[] = ' c.title like \'%'.$param['q'].'%\'';
			}
			if(isset($param['totalflag'])){
				$wherearr[] = 'p.totalflag in ('.$param['totalflag'].')';
			}else{
				$wherearr[] = 'p.totalflag=1';
			}
			if(!empty($param['folderid'])){
				$wherearr[] = 'rc.folderid = '.$param['folderid'];
			}
			if(!empty($wherearr)){
				$sql.=' WHERE '.implode(' AND ',$wherearr);
			}
			$row = Ebh()->db->query($sql)->row_array();
			if(!empty($row))
				$count = $row['count'];
			return $count;
		}
		
		

		//确保ctime正确性
		private function _ensureCtime($cwid = 0,$ctime = 0) {
			$sql = 'select cwlength from ebh_coursewares  where cwid = '.$cwid.' limit 1';
			$course = Ebh()->db->query($sql)->row_array();
			if(empty($course)) {
				log_message('ensureCtim出错 cwid:' .$cwid.' 对应课件不存在');
			}
			return $course['cwlength'] > 0 ? $course['cwlength'] : $ctime;
		}
		
		/*
		获取课件的最后一次学习情况，上次播放到哪里
		cwid,uid
		*/
		public function getlastlogbyuid($param){
			if(empty($param['uid']) || empty($param['cwid']))
				return false;
			$sql = 'select curtime from ebh_playlogs ';
			$wherearr[] = 'uid='.$param['uid'];
			$wherearr[] = 'cwid='.$param['cwid'];
			$sql.= ' where '.implode(' and ',$wherearr);
			$sql.=' order by logid desc limit 1';
			$studylog = Ebh()->db->query($sql)->row_array();
			return $studylog;
		}
		
		/*
		获取课程的学习人数，学习次数，学习总时长
		*/
		public function getCourseStudyCount($param){
			$sql = 'select count(1) count,p.folderid,count(DISTINCT p.uid) usernum,sum(p.ltime) ltimetotal  from ebh_playlogs p ';
			$wherearr[] = 'p.folderid in ('.Ebh()->db->escape_str($param['folderids']).')';
			if(!empty($param['starttime'])){
				$wherearr[] = 'p.lastdate>='.intval($param['starttime']);
			}
			if(!empty($param['endtime'])){
				$wherearr[] = 'p.lastdate<='.intval($param['endtime']);
			}
			if(!empty($param['crid'])){
				$wherearr[] = 'p.crid='.intval($param['crid']);
			}
			$wherearr[] = 'p.totalflag=0';
			$sql.= ' where '.implode(' AND ',$wherearr);
			$sql.= ' group by p.folderid';
			// return $sql;
			return Ebh()->db->query($sql)->list_array('folderid');
		}
		
		
		/*
		课件的学习情况
		*/
		public function getStudyDetailByCwid($param){
			if(empty($param['cwid'])){
				return FALSE;
			}
			$sql = 'select uid,ctime,sum(ltime) ltime,count(1) count,min(startdate) startdate,max(lastdate) lastdate
					from ebh_playlogs';
			if(!empty($param['crid'])){
				$wherearr[] = 'crid='.$param['crid'];
			}
			$wherearr[] = 'cwid='.$param['cwid'];
			$wherearr[] = 'totalflag=0';
			$sql.= ' where '.implode(' AND ',$wherearr);
			$sql.= ' group by uid';
			if(!empty($param['limit'])) {
				$sql .= ' limit '.$param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
			return Ebh()->db->query($sql)->list_array('uid');
			
		}

		/*
		课程的学习情况
		*/
		public function getStudyDetailByFolder($param){
			//数据库测试语句，测试100000条数据导出，花费4秒
			/*$sql = 'select * from ebh_roomusers where crid=10194';
			$uidlist = Ebh()->db->query($sql)->list_array('uid');
			$uidArr = array_keys($uidlist);

			$testsql = 'INSERT INTO `testplaylogs` (`uid`, `cwid`, `ctime`, `ltime`, `startdate`, `lastdate`, `totalflag`, `finished`, `crid`, `folderid`, `curtime`, `ip`) VALUES';
			for ($i=0; $i <150000 ; $i++) { 
				$testsql .= '('.$uidArr[array_rand($uidArr,1)].', 125153, 29, 1, 1505874094, 1505874094, 0, 0, 10194, 29774, 0, \'192.168.0.62\'),';
			}
			$testsql = substr($testsql, 0,-1);
			Ebh()->db->query($testsql);exit;*/
			if(empty($param['folderid'])){
				return FALSE;
			}
			$sql = 'select uid,cwid,ctime,sum(ltime) ltime,count(1) count,min(startdate) startdate,max(lastdate) lastdate
					from ebh_playlogs';
			if(!empty($param['crid'])){
				$wherearr[] = 'crid='.$param['crid'];
			}
			$wherearr[] = 'folderid='.$param['folderid'];
			$wherearr[] = 'totalflag=0';
			$sql.= ' where '.implode(' AND ',$wherearr);
			$sql.= ' group by uid,cwid';
			if(!empty($param['limit'])) {
				$sql .= ' limit '.$param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
			return Ebh()->db->query($sql)->list_array();
			
		}
		
		/*
		课件的学习情况,学生数量
		*/
		public function getStudyCountByCwid($param){
			if(empty($param['cwid'])){
				return FALSE;
			}
			$sql = 'select count(DISTINCT uid) count
					from ebh_playlogs';
			$wherearr[] = 'cwid='.$param['cwid'];
			$wherearr[] = 'totalflag=0';
			$sql.= ' where '.implode(' AND ',$wherearr);
			$count = Ebh()->db->query($sql)->row_array();
			return $count['count'];
		}
	}
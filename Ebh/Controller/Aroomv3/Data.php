<?php
/**
 * 数据审核控制器
 */
class DataController extends Controller {

	 public function __construct()
    {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //课程评论列表
            'coursewareListAction' => array(
            	'crid' => array(
                	'name' => 'crid',
                	'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'default'=>20
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'role' => array(
                    'name' => 'role',
                    'type' => 'string',
                    'default'=>'teach'
                ),
                'q'  =>  array(
                	'name'=>'q',
                	'default'=>''
                ),
                'authkey'  =>  array(
                	'name'=>'authkey',
                	'default'=>''
                ),
                'teach_status' => array(
                    'name' => 'teach_status',
                    'type' => 'int',
                    'default'=>0
                ),
                'cat' => array(
                    'name' => 'cat',
                    'type' => 'int',
                    'default'=>0
                )
            ),
            //课程评论列表
            'attachmentAction' => array(
            	'crid' => array(
                	'name' => 'crid',
                	'type' => 'int',
                	//'require' => true
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'default'=>20
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'role' => array(
                    'name' => 'role',
                    'type' => 'string',
                    'default'=>'teach'
                ),
                'q'  =>  array(
                	'name'=>'q',
                	'default'=>''
                ),
                'authkey'  =>  array(
                	'name'=>'authkey',
                	'default'=>''
                ),
                'teach_status' => array(
                    'name' => 'teach_status',
                    'type' => 'int',
                    'default'=>0
                ),
                'cat' => array(
                    'name' => 'cat',
                    'type' => 'int',
                    'default'=>-1
                )
            ),
            'homeworkv2Action' => array(
            	'crid' => array(
                	'name' => 'crid',
                	'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'default'=>20
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'role' => array(
                    'name' => 'role',
                    'type' => 'string',
                    'default'=>'teach'
                ),
                'q'  =>  array(
                	'name'=>'q',
                	'default'=>''
                ),
                'teach_status' => array(
                    'name' => 'teach_status',
                    'type' => 'int',
                    'default'=>0
                ),
                'cat' => array(
                    'name' => 'cat',
                    'type' => 'int',
                    'default'=>-1
                )
            ),
            'panListAction' => array(
            	'crid' => array(
                	'name' => 'crid',
                	'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'default'=>20
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'role' => array(
                    'name' => 'role',
                    'type' => 'string',
                    'default'=>'teach'
                ),
                'q'  =>  array(
                	'name'=>'q',
                	'default'=>''
                ),
                'pankey'  =>  array(
                	'name'=>'pankey',
                	'default'=>''
                ),
                'teach_status' => array(
                    'name' => 'teach_status',
                    'type' => 'int',
                    'default'=>0
                ),
                'cat' => array(
                    'name' => 'cat',
                    'type' => 'int',
                    'default'=>-1
                )
            ),
            'viewAction' => array(
                'cwid'  =>  array(
                	'name'=>'cwid',
                	'default'=>0,
                	'type' => 'int'
                ),
               'attid'  =>  array(
                	'name'=>'attid',
                	'default'=>0,
                	'type' => 'int'
                ),
                'eid'  =>  array(
                	'name'=>'eid',
                	'default'=>0,
                	'type' => 'int'
                ),
                'fileid'  =>  array(
                	'name'=>'fileid',
                	'default'=>0,
                	'type' => 'int'
                )
            ),
            'checkprocessAction' => array(
                'teach_status'  =>  array(
                	'name'=>'teach_status',
                	'default'=>0,
                	'type' => 'int'
                ),
               'type'  =>  array(
                	'name'=>'type',
                	'default'=>0,
                	'type' => 'int'
                ),
               'ip'  =>  array(
                	'name'=>'ip',
                	'default'=>'',
                	'type' => 'string'
                ),
               'toid'  =>  array(
                	'name'=>'toid',
                	'default'=>0,
                	'type' => 'int'
                ),
               'teach_remark'  =>  array(
                	'name'=>'teach_remark',
                	'default'=>''
                ),
               'uid'  =>  array(
                	'name'=>'uid',
                	'default'=>0,
                	'type' => 'int'
                )
            ),
            'delprocessAction' => array(
               'type'  =>  array(
                	'name'=>'type',
                	'default'=>0,
                	'type' => 'int'
                ),
               'toid'  =>  array(
                	'name'=>'toid',
                	'default'=>0,
                	'type' => 'int'
                )
            ),
            'multcheckprocessAction' => array(
                'teach_status'  =>  array(
                	'name'=>'teach_status',
                	'default'=>0,
                	'type' => 'int'
                ),
               'type'  =>  array(
                	'name'=>'type',
                	'default'=>0,
                	'type' => 'int'
                ),
               'ids'  =>  array(
                	'name'=>'ids',
                	'default'=>'',
                	'type' => 'string'
                ),
               'ip'  =>  array(
                	'name'=>'ip',
                	'default'=>'',
                	'type' => 'string'
                ),
               'teach_remark'  =>  array(
                	'name'=>'teach_remark',
                	'default'=>''
                ),
               'uid'  =>  array(
                	'name'=>'uid',
                	'default'=>0,
                	'type' => 'int'
                )
            ),
            'revokeAction' => array(
                'teach_status'  =>  array(
                	'name'=>'teach_status',
                	'default'=>0,
                	'type' => 'int'
                ),
               'type'  =>  array(
                	'name'=>'type',
                	'default'=>0,
                	'type' => 'int'
                ),
               'toid'  =>  array(
                	'name'=>'toid',
                	'default'=>0,
                	'type' => 'int'
                ),
               'status'  =>  array(
                	'name'=>'status',
                	'default'=>0,
                	'type' => 'int'
                ),
               'teach_remark'  =>  array(
                	'name'=>'teach_remark',
                	'default'=>''
                ),
               'uid'  =>  array(
                	'name'=>'uid',
                	'default'=>0,
                	'type' => 'int'
                )
            ),
            'checkexamAction' => array(
            	'crid' => array(
                	'name' => 'crid',
                	'type' => 'int'
                ),
                'teach_status'  =>  array(
                	'name'=>'teach_status',
                	'default'=>0,
                	'type' => 'int'
                ),
               'type'  =>  array(
                	'name'=>'type',
                	'default'=>0,
                	'type' => 'int'
                ),
               'toid'  =>  array(
                	'name'=>'toid',
                	'default'=>0,
                	'type' => 'int'
                ),
               'teach_remark'  =>  array(
                	'name'=>'teach_remark',
                	'default'=>''
                ),
               'ip'  =>  array(
                	'name'=>'ip',
                	'default'=>'',
                	'type' => 'string'
                ),
               'subject'  =>  array(
                	'name'=>'subject',
                	'default'=>''
                ),
               'uid'  =>  array(
                	'name'=>'uid',
                	'default'=>0,
                	'type' => 'int'
                ),
                'examuid'  =>  array(
                    'name'=>'examuid',
                    'default'=>0,
                    'type' => 'int'
                )
            )
        );
    }

	/**
	 * 课件列表
	 * 
	 */
	public function coursewareListAction(){
		$param = $this->buildParams(1);
		$CModel = new CheckModel();
		$count = $CModel->getcoursewarecount($param);
		$coursewares = $CModel->getcoursewarelist($param);//生成课件和附件所在服务器地址
		$source = $this->getCourseSource();
		foreach($coursewares as &$ware){
			if (preg_match("/.*(\.ebh|\.ebhp)$/", $ware['cwurl'])){
				$ware['play'] = 1;
			} elseif (in_array(strtolower(trim(substr(strrchr($ware['cwurl'], '.'), 1))), array('swf', 'flv', 'mp4', 'avi', 'rmvb', 'mov', 'mpg','mp3'))) {
				$ware['play'] = 1;
			} elseif(!empty($ware['cwurl'])) {
				$ware['play'] = 2;
			} else {
				$ware['play'] = 0;
			}
			if(!empty($source)){
				$ware['cwsource'] = $source;
			}
			$ware['k'] = $this->authkey;

			$uidsArr[] = $ware['uid'];
			if (empty($ware['admin_dateline']) && empty($ware['teach_dateline'])) {
				$ware['checkname'] = $ware['admin_uid']?'e板会kf':'';
				$ware['tstatus'] = 0;
			} else {
				if ((intval($ware['admin_dateline']) >= intval($ware['teach_dateline'])) || $ware['admin_uid']) {
					$ware['checkname'] = 'e板会kf';
					$ware['tstatus'] = empty($ware['admin_status'])?0:$ware['admin_status'];//客服的状态
				} else {
					$uidsArr[] = $ware['teach_uid'];
				}
			}
		}
		if (!emptY($uidsArr)) {
			$coursewares = $this->buildUserinfo($uidsArr,$coursewares);
		}
		$ret['list'] = $coursewares;
		$ret['count'] = $count;
		return $ret;
	}

	/**
	 * 附件审核
	 * 
	 */
	public function attachmentAction() {
		$param = $this->buildParams(2);
		$AModel = new AttachmentModel();
		$count = $AModel->getattachmentcount($param);
		$attachments = $AModel->getattachmentlist($param);
		$source = $this->getCourseSource();
		foreach($attachments as &$attach){
			if (preg_match("/.*(\.ebh|\.ebhp)$/", $attach['url'])){
				$attach['play'] = 1;
			} elseif (in_array($attach['suffix'], array('swf', 'flv', 'mp4', 'avi', 'rmvb', 'mov', 'mpg','mp3'))) {
				$attach['play'] = 1;
			} elseif(!empty($attach['url'])) {
				$attach['play'] = 2;
			} else {
				$attach['play'] = 0;
			}
			if(!empty($source)){
				$attach['source'] = $source;
			}
			$attach['k'] = $this->authkey;

			$uidsArr[] = $attach['uid'];
			if (empty($attach['admin_dateline']) && empty($attach['teach_dateline'])) {
				$attach['checkname'] = $attach['admin_uid']?'e板会kf':'';
				$attach['tstatus'] = 0;//未审核
			} else {
				if ((intval($attach['admin_dateline']) >= intval($attach['teach_dateline'])) || $attach['admin_uid']) {
					$attach['checkname'] = 'e板会kf';
					$attach['tstatus'] = empty($attach['admin_status'])?0:$attach['admin_status'];//客服的状态
				} else {
					$uidsArr[] = $attach['teach_uid'];
				}
			}
		}

		if (!emptY($uidsArr)) {
			$attachments = $this->buildUserinfo($uidsArr,$attachments);
		}
		$ret['list'] = $attachments;
		$ret['count'] = $count;
		return $ret;
	}

	/**
	 * 评论内容动态图片替换
	 */
	protected function _exchangeimg($subject){
		$emotionarr = array('微笑','害羞','调皮','偷笑','送花','大笑','跳舞','飞吻','安慰','抱抱','加油','胜利','强','亲亲','花痴','露齿笑','查找','呼叫','算账','财迷','好主意','鬼脸','天使','再见','流口水','享受');
		$matstr = '/\[emo(\S{1,2})\]/is';
		$emotioncount = count($emotionarr);
		preg_match_all($matstr,$subject,$mat);
		if(!empty($mat[0])){
			foreach($mat[0] as $l=>$m){
				$imgnumber = intval($mat[1][$l]);
				if($imgnumber<$emotioncount){
					$subject=str_replace($m,'<img src="http://static.ebanhui.com/ebh/tpl/default/images/'.$imgnumber.'.gif">',$subject);
				}			
			}
		}

		return $subject;
	}

	/**
	 * 作业2.0审核
	 * 
	 */
	public function homeworkv2Action(){
		$param = $this->buildParams(14);
		$HModel = new Homeworkv2Model();
		$examInfo = $HModel->getHomeworkList($param);
		$homeworks = empty($examInfo['examList'])?array():$examInfo['examList'];
		$count = empty($examInfo['count'])?0:$examInfo['count'];
		if ($homeworks) {
			 foreach($homeworks as $k=>&$v){
	            $uidsArr[] = $v['uid'];
				if (empty($v['admin_dateline']) && empty($v['teach_dateline'])) {
					$v['checkname'] = $v['admin_uid']?'e板会kf':'';
					$v['tstatus'] = 0;//未审核
				} else {
					if ((intval($v['admin_dateline']) >= intval($v['teach_dateline'])) || $v['admin_uid']) {
						$v['checkname'] = 'e板会kf';
						$v['tstatus'] = empty($v['admin_status'])?0:$v['admin_status'];//客服的状态;//客服的状态
					} else {
						$uidsArr[] = $v['teach_uid'];
					}
				}
	        }
		} else {
			$homeworks = array();
		}
		if (!emptY($uidsArr)) {
			$homeworks = $this->buildUserinfo($uidsArr,$homeworks);
		}
		$ret['list'] = $homeworks;
		$ret['count'] = $count;
		return $ret;
	}

	/**
	 * 云盘审核
	 */
	public function panListAction(){
		$param = $this->buildParams(11);
		$filemodel = new FileModel();
		$count = $filemodel->getFileCount($param);
		$panfiles = $filemodel->getFileList($param);
		if(!empty($panfiles)){
		    $uidsArr = array();
		    $users = array();
		    $crid_array = array();
		    $classrooms = array();
		    foreach($panfiles as &$value)
		    {
		        $uidsArr[] = $value['uid'];
		        $crid_array[] = $value['crid'];

				if (empty($value['admin_dateline']) && empty($value['teach_dateline'])) {
					$value['checkname'] = $value['admin_uid']?'e板会kf':'';
					$value['tstatus'] = 0;
				} else {
					if ((intval($value['admin_dateline']) >= intval($value['teach_dateline'])) || $value['admin_uid']) {
						$value['checkname'] = 'e板会kf';
						$value['tstatus'] = empty($value['admin_status'])?0:$value['admin_status'];//客服的状态;//客服的状态
					} else {
						$uidsArr[] = $value['teach_uid'];
					}
				}
		    }
		    $ebhuserModel = new UserModel();
		    $classroomModel = new ClassRoomModel();
		    $users = $ebhuserModel->getuserarray($uidsArr);
		    $classrooms = $classroomModel->getClassRoomArray($crid_array);
			//log_message(print_r($panfiles,1));
		    foreach($panfiles as $key => $pvalue)
		    {
		        $panfiles[$key]['username'] = empty($users[$pvalue['uid']]['username'])?'此人已删除':$users[$pvalue['uid']]['username'];
		        $panfiles[$key]['sex'] = !isset($users[$pvalue['uid']]['sex'])?0:$users[$pvalue['uid']]['sex'];
		        $panfiles[$key]['face'] = !isset($users[$pvalue['uid']]['face'])?'':$users[$pvalue['uid']]['face'];;
		        $panfiles[$key]['realname'] = empty($users[$pvalue['uid']]['realname'])?'此人已删除':$users[$pvalue['uid']]['realname'];
		        $panfiles[$key]['crname'] = $classrooms[$pvalue['crid']];
		        $panfiles[$key]['size'] = $this->_format_bytes($pvalue['size']);
		        if (!empty($users[$pvalue['teach_uid']]) && !isset($pvalue['checkname'])) {
					$panfiles[$key]['checkname'] = $users[$pvalue['teach_uid']]['username'];
					$panfiles[$key]['tstatus'] = $panfiles[$key]['teach_status'];
				} 
		    }  
		}else{
		    $panfiles = array();
		}
		//log_message(print_r($panfiles,1));
		$ret['list'] = $panfiles;
		$ret['count'] = $count;
		return $ret;
	}

	/**
	 * 查看
	 *
	 */
	public function viewAction(){
		$cwid = $this->cwid;
		$attid= $this->attid;
		$eid = $this->eid;//作业
		$fileid = $this->fileid;

		if($cwid>0){//课件查看
			$CModel = new CheckModel();
			$ware= $CModel->getcoursedetail($cwid);
			$liveModle = new LiveinfoModel();
            $live = $liveModle->getLiveInfoByCwid($ware['cwid']);
            if (!empty($live)) {
            	$ware['teacher_board_rtmp'] = str_replace('[liveid]', $live['liveid'].'s', $live['rtmppullurl']);
            	$ware['teacher_camera_rtmp'] = str_replace('[liveid]', $live['liveid'].'c', $live['rtmppullurl']);
            	$ware['teacher_board_http'] = str_replace('[liveid]', $live['liveid'].'s', $live['hlspullurl']);
            } else {
            	$ware['teacher_board_rtmp'] = '';
            	$ware['teacher_camera_rtmp'] = '';
            	$ware['teacher_board_http'] = '';
            }
            
            if (empty($ware['teach_dateline'])) {
				$ware['teach_dateline'] = $ware['admin_dateline'];
			}
            return $ware;
		}elseif($attid>0){//附件查看
			$AModel = new AttachmentModel();
			$attach = $AModel->getAttachById($attid);
			if (empty($attach['teach_dateline'])) {
				$attach['teach_dateline'] = $attach['admin_dateline'];
			}
			return $attach;
		} elseif($eid>0){
			$HModel = new Homeworkv2Model();
			$info = $HModel->getHomeworkById($eid);
			if (empty($info['teach_dateline'])) {
				$info['teach_dateline'] = $info['admin_dateline'];
			}
			return $info;
		}elseif($fileid>0){
			$filemodel = new FileModel();
			$info=$filemodel->getFileById($fileid);
			if (empty($info['teach_dateline'])) {
				$info['teach_dateline'] = $info['admin_dateline'];
			}
			return $info;
		}else{
			exit;
		}
	}
	/**
	 * 审核处理
	 */
	public function checkprocessAction(){
    	$stat = $this->teach_status;
		$type= $this->type;
		if($type<=0){
			exit(0);
		}
		if($type == 11){
			$ckmodel = new PanbillchecksModel();
		}
		else {
			$ckmodel = new CheckModel();
		}

		$param = array(
			'role'=>'teach',
			'teach_uid'=>$this->uid,
			'teach_status'=>$stat,
			'teach_remark'=>$this->teach_remark,
			'toid'=>	$this->toid,
			'teach_ip'=>$this->ip,
			'type'=>$type
			);

		$ret = $ckmodel->check($param);
		if(!empty($ret)){
			$temp=$this->getDataInfo($param['toid'],$type);
			$name=$temp['name'];
			$school=$temp['school'];
			$alltype=array(1=>'课件',2=>'附件',3=>'评论',4=>'答疑',5=>'回答',6=>'主站评论',7=>'作业',11=>'云盘',13=>'域名');
			$allresult=array('','通过','不通过');

			$remark=' 审核结果为'.$allresult[$stat];
			if($school){
				$remark.=' 所属学校为'.$school;
			}

			//log_message('数据审核:'.$alltype[$type].'审核  name: '.$name.' id :'.$param['toid'].' remark: '.$remark);//添加日志

			if(!empty($stat) && intval($stat) == 2){
                return array('code'=>2,'msg'=>'处理成功','checker'=>'');
            }else{
                return array('code'=>0,'msg'=>'处理成功','checker'=>'');
            }

		}else{
			return array('code'=>1,'msg'=>'处理失败','checker'=>'');
		}

	}

	/**
	 *新版作业审核
	 */
	public function checkexamAction(){
    	$stat = $this->teach_status;
		$ckmodel = new Homeworkv2Model();
		$param = array(
			'role'=>'teach',
			'teach_uid'=>$this->uid,
			'teach_status'=>$stat,
			'teach_remark'=>$this->teach_remark,
			'toid'=>$this->toid,
			'teach_ip'=>$this->ip,
			'crid'=>$this->crid,
			'subject'=>$this->subject,
			'uid'=>$this->examuid
		);

		$ret = $ckmodel->checkexam($param);
		if (is_array($ret)) {//只能向上处理
			return array('code'=>1,'msg'=>'只能处理，最新审核以后的数据，不能跳着审核','checker'=>'');
		}
		if(!empty($ret)){
			$temp=$this->getDataInfo($param['toid'],14);
			$name=$temp['name'];
			$school=$temp['school'];
			$allresult=array('','通过','不通过');
			$remark=' 审核结果为'.$allresult[$stat];
			if($school){
				$remark.=' 所属学校为'.$school;
			}
			//log_message('数据审核: 新作业审核  name: '.$name.' id :'.$param['toid'].' remark: '.$remark);//添加日志
			if ($stat == 2) {
				//同步SNS数据(当删除问题时，教师作业数减1)
				//Ebh::app()->lib('Sns')->do_sync($temp['uid'], -3);
			}

			if(!empty($stat) && intval($stat) == 2){
                return array('code'=>2,'msg'=>'处理成功','checker'=>'');
            }else{
                return array('code'=>0,'msg'=>'处理成功','checker'=>'');
            }
		}else{
			return array('code'=>1,'msg'=>'处理失败','checker'=>'');
		}

	}

    /**
     * 撤销审核
     */
    public function revokeAction(){
        $stat = $this->status;
        $type= $this->type;
        if($type<=0){
            exit(0);
        }
        if($type == 11){
            $ckmodel = new PanbillchecksModel();
        }
        else {
            $ckmodel = new CheckModel();
        }
        $param = array(
            'teach_status'=>0,
            'teach_uid' => $this->uid,
            'toid'=>	$this->toid,
            'type'=>$this->type,
            'status' => $this->status,
        );
        $ret = $ckmodel->revoke($param);

        if($ret>0){
            $temp=$this->getDataInfo($param['toid'],$type);
            $name=$temp['name'];
            $school=$temp['school'];
            $alltype=array(1=>'课件',2=>'附件',3=>'评论',4=>'答疑',5=>'回答',6=>'主站评论',7=>'作业',11=>'云盘',13=>'域名',14=>'新作业审核');
            
            $remark='撤销审核';
            if($school){
                $remark.=' 所属学校为'.$school;
            }
            //log_message('数据审核:'.$alltype[$type].'审核  name: '.$name.' id :'.$param['toid'].' remark: '.$remark);//添加日志
            return array('code'=>0,'msg'=>'处理成功','checker'=>'');
        }else{
            return array('code'=>1,'msg'=>'处理失败','checker'=>'');
        }

    }


	/**
	 * 批量审核处理
	 */
	public function multcheckprocessAction(){
		if (empty($this->ids)) {
			return array('status'=>-1);
		}
		$stat = $this->teach_status;
		$type= $this->type;
		if($type<=0){
			exit(0);
		}
		if($type == 11){
            $ckmodel = new PanbillchecksModel();
        }
        else {
            $ckmodel = new CheckModel();
        }
		$param = array(
				'role'=> 'teach',
				'teach_uid'=>$this->uid,
				'teach_status'=>$stat,
				'teach_remark'=>$this->teach_remark,
				'ids'=>	$this->ids,
				'teach_ip'=>$this->ip,
				'type'=>$type
		);
		if($ckmodel->multcheck($param)){
			$id_array = explode(',', $this->ids);
			foreach ($id_array as $toid)
			{
				$temp=$this->getDataInfo($toid,$type);
				$name=$temp['name'];
				$school=$temp['school'];
				$alltype=array(1=>'课件',2=>'附件',3=>'评论',4=>'答疑',5=>'回答',6=>'主站评论',7=>'作业',11=>'云盘',);
				$allresult=array('','通过','不通过');
				$remark=' 审核结果为'.$allresult[$stat];
				if($school){
					$remark.=' 所属学校为'.$school;
				}

				//log_message('数据审核:'.$alltype[$type].'审核  name: '.$name.' id :'.$param['toid'].' remark: '.$remark);//添加日志
			}

			return array('code'=>0,'msg'=>'处理成功');
		}else{
			return array('code'=>1,'msg'=>'处理失败');
		}
	}

	/**
	 * 删除处理
	 *
	 */
	public function delprocessAction(){
		$toid = $this->toid;
		$type = $this->type;
		if($toid<=0||$type<=0){
			exit(0);
		}
		$param = array(
			'toid'=>$toid,
			'type'=>$type
		);
		$ckmodel = new CheckModel();
		$ckmodel->del($param);
		//生成备注
		$alltype=array(1=>'课件',2=>'附件',3=>'评论',4=>'答疑',5=>'回答',6=>'主站评论',7=>'作业',11=>'云盘');
		$info=$this->getDataInfo($toid,$type);
		$name=$info['name'];
		$school=$info['school'];
		if($name){
			$remark.=' 名称为'.$name;
		}
		if($school){
			$remark.=' 所属学校为'.$school.' ';
		}
		//log_message('数据审核:'.$alltype[$type].'删除  name: '.$name.' id :'.$param['toid'].' remark: '.$remark);//添加日志
		return array('code'=>0,'msg'=>'处理成功');
	}

	/**
	 *获取详情信息
	 */
	private function getDataInfo($id,$type){
		$info=array();
		if($type==1){//课件审核
			$coursewareModel = new CheckModel();
			$temp = $coursewareModel->getcoursedetail($id);
			$info['name']=$temp['title'];
			$info['school']=$coursewareModel->getSchoolName($id);
			return $info;
		}elseif($type==2){//附件审核
			$AttachmentModel = new AttachmentModel();
			$temp= $AttachmentModel->getAttachById($id);
			$info['name']=$temp['title'];
			$info['school']=$temp['crname'];
			return $info;
		} elseif($type==11){//云盘审核
			$FileModel = new FileModel();
			$classroomModel = new ClassRoomModel();
			$temp = $FileModel->getFileById($id);
			$info['name']=$temp['title'];
			$classroom = $classroomModel->getRoomByCrid($temp['crid']);
			$info['school']=$classroom['crname'];
			$info['uid']=$temp['uid'];
			return $info;
		}elseif($type==14){//新作业
			$Homeworkv2Model = new Homeworkv2Model();
			$temp=$Homeworkv2Model->getHomeworkById($id);
			$info['name']=$temp['title'];
			$info['school']=$temp['crname'];
			$info['uid']=$temp['uid'];
			return $info;
		}else{
			return array('name'=>'审核','school'=>'未知');
		}
	}

	//计算文件大小，转换成B,KB,MB,GB,TB格式
	function _format_bytes($size) {
		if ($size == 0) return 0;
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2) . $units[$i];
	}

	/**
	 *构建查询参数
	 */
	function buildParams($type=14) {
		$page = $this->pagenum;//当前页
		$pagesize = $this->pagesize;
		$astatus = $this->teach_status;
		$q = $this->q;
		$crid = $this->crid;
		$cat = $this->cat;
		$role = $this->role;

		$param = array(
			'role'=>$role,
			'pagesize'=>$pagesize,
			'limit'=>(max(0,($page-1)*$pagesize)).", {$pagesize}",
			'cat'=>$cat,
			'page'=>$page,
			'teach_status'=>$astatus,
			'crid'=>$crid,
			'q'=>$q,
			'type'=>$type
		);
		return $param;
	}

	/**
	 *获取播放的地址
	 */
	public function getCourseSource() {
		$serverlist = Ebh()->config->get('servers.servers');
        $source = '';
		if(empty($serverlist))
			return $source;
		$scount = count($serverlist);
		if($scount == 1) {
			$source = $serverlist[0];
		} else {
			$spos = rand(0, $scount - 1);
			$source = $serverlist[$spos];
		}
		$source = 'http://'.$source.'/';
		return $source;
	}

	/**
	 *获取播放的地址
	 */
	public function buildUserinfo($uidsArr,$list) {
		$uidStr = implode(',', array_unique($uidsArr));
		$userModel = new UserModel();
		$userinfos = $userModel->getUserInfoByUid($uidStr);
		if (!empty($userinfos)) {
			foreach ($userinfos as $value) {
				$user_info[$value['uid']] = $value;
			}
			foreach ($list as &$value) {
				if (!empty($user_info[$value['teach_uid']]) && !isset($value['checkname'])) {
					$value['checkname'] = $user_info[$value['teach_uid']]['username'];
					$value['tstatus'] = $value['teach_status'];//教师的审核状态
				}
				$value['username'] = empty($user_info[$value['uid']])?'':$user_info[$value['uid']]['username'];
				$value['sex'] = empty($user_info[$value['uid']])?'':$user_info[$value['uid']]['sex'];
				$value['face'] = empty($user_info[$value['uid']])?'':$user_info[$value['uid']]['face'];
				$value['realname'] = empty($user_info[$value['uid']])?'':$user_info[$value['uid']]['realname'];
			}
		}
		return $list;
	}

}

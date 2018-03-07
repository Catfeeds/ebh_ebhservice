<?php

/**
 * 网校
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/15
 * Time: 14:47
 */
class ClassRoomModel
{
    private $db;
    public function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * 网校基本信息
     * @param $crid
     * @return mixed
     */
    public function getModel($crid)
    {
        $crid = (int) $crid;
        $sql = "SELECT `uid`,`crid`,`crname`,`cface`,`domain`,`fulldomain`,`catid`,`summary`,`message`,`craddress`,`crphone`,`kefu`,`kefuqq`,`stunum`,`teanum`,`coursenum`,`examcount`,`asknum`,`crlabel`,`begindate`,`enddate`,`isschool`,`wechatimg`,`icp`,`grade`,`lng`,`lat` ,`property`,`isdesign`
                FROM `ebh_classrooms` WHERE `crid`=$crid AND `status`=1";
        return $this->db->query($sql)->row_array();
    }


    /**
     *根据教室编号获取教室对应的信息
     */
    public function getRoomByCrid($crid) {
        $sql = "select crid,domain,isschool,good,useful,bad,score,viewnum,crname,property,`isdesign` from ebh_classrooms where crid=$crid";
        return $this->db->query($sql)->row_array();
    }

    /**
     * 获取网校类型
     */
    public function getRoomType($crid){
        $roomtype  = 'edu';
        $room = $this->getRoomByCrid($crid);
        if(isset($room['property']) && ($room['property']==3) &&
            ( $room['isschool']==7)){
                $roomtype= 'com';
        }else{
            $roomtype = 'edu';
        }
        return $roomtype;
    }
    
    /**
     * 更新网校信息
     * @param $crid
     * @param $update_params
     * @return mixed
     */
    public function update($crid, $update_params)
    {
        $params = array();
        $crid = (int) $crid;
        if (isset($update_params['crname'])) {
            $params['crname'] = $update_params['crname'];
        }
        if (isset($update_params['cface'])) {
            $params['cface'] = $update_params['cface'];
        }
        if (isset($update_params['summary'])) {
            $params['summary'] = $update_params['summary'];
        }
        if (isset($update_params['message'])) {
            $params['message'] = $update_params['message'];
        }
        if (isset($update_params['crphone'])) {
            $params['crphone'] = $update_params['crphone'];
        }
        if (isset($update_params['craddress'])) {
            $params['craddress'] = $update_params['craddress'];
        }
        if (isset($update_params['kefu'])) {
            $params['kefu'] = $update_params['kefu'];
        }
        if (isset($update_params['kefuqq'])) {
            $params['kefuqq'] = $update_params['kefuqq'];
        }
        if (isset($update_params['crlabel'])) {
            $params['crlabel'] = $update_params['crlabel'];
        }
        if (isset($update_params['icp'])) {
            $params['icp'] = $update_params['icp'];
        }
        if (isset($update_params['wechatimg'])) {
            $params['wechatimg'] = $update_params['wechatimg'];
        }
        if (isset($update_params['lng'])) {
            $params['lng'] = floatval($update_params['lng']);
        }
        if (isset($update_params['lat'])) {
            $params['lat'] = floatval($update_params['lat']);
        }

		if (isset($update_params['navigator'])) {
            $params['navigator'] = $update_params['navigator'];
        }
        if (isset($update_params['isdesign'])) {
            $params['isdesign'] = $update_params['isdesign'];
        }
        if (empty($params)) {
            return 0;
        }
        return $this->db->update('ebh_classrooms', $params, "`crid`=$crid");
    }

    /**
     * 网校icp信息
     * @param $crid
     * @return string
     */
    public function getInfoForSeo($crid)
    {
        $crid = (int) $crid;
        $sql = "SELECT `icp`,`crname` FROM `ebh_classrooms` WHERE `crid`=$crid";
        $ret = $this->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret;
        }
        return false;
    }
	
	
	/**
	 * 根据crid获取教室详细信息
	 * @param type $crid
	 * @return type
	 */
	public function getDetailClassroom($crid) {
		$sql = "select cr.crid,cr.uid,cr.crname,cr.domain,cr.template,cr.isschool,cr.summary,cr.crlabel,cr.cface,cr.crqq,cr.craddress,cr.crphone,cr.cremail,cr.modulepower,cr.stumodulepower,cr.bankcard,"
				. "cr.dateline,cr.banner,cr.displayorder,cr.viewnum,cr.score,cr.onlinecount,cr.lng,cr.lat,cr.weibosina,cr.stunum,cr.teanum,cr.coursenum,cr.message,cr.good,cr.bad,cr.useful,cr.districts,cr.examcount,cr.asknum,cr.profitratio,cr.kefu,cr.kefuqq,cr.fulldomain  from ebh_classrooms cr where cr.crid=$crid";
		return Ebh()->db->query($sql)->row_array();
	}
	/**
     * 网校导航
     * @param $crid 网校ID
     * @return bool
     */
    public function getNavigator($crid) {
        $crid = (int) $crid;
        $ret = Ebh()->db->query('SELECT `navigator` FROM `ebh_classrooms` WHERE `crid`='.$crid)->row_array();
        if (!empty($ret)) {
            return $ret['navigator'];
        }
        return false;
    }
	
	
	/*
	修改自定义富文本
	@param $param crid,index,custommessage
	*/
	public function saveCustomMessage($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$param['crid'] = intval($param['crid']);
		$wherearr[] = 'crid='.$param['crid'];
		if(isset($param['index']))
			$wherearr[] = '`index`='.$param['index'];
		$sql = 'select 1 from ebh_custommessages';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$res = Ebh()->db->query($sql)->list_array();
		// var_dump($res);
		
		if(empty($res)){
			$iarr['crid'] = $param['crid'];
			if(isset($param['index']))
				$iarr['index'] = trim($param['index'],'\'');
			if(isset($param['custommessage']))
				$iarr['custommessage'] = $param['custommessage'];
			// if(isset($param['appstr']))
				$iarr['appstr'] = isset($param['appstr'])?$param['appstr']:'';
			return Ebh()->db->insert('ebh_custommessages',$iarr);
		}else{
			if(isset($param['custommessage']))
				$setarr['custommessage'] = $param['custommessage'];
			if(isset($param['appstr']))
				$setarr['appstr'] = $param['appstr'];
			$wherearr = array('crid'=>$param['crid']);
			if(isset($param['index']))
				$wherearr['`index`'] = trim($param['index'],'\'');
			// var_dump($wherearr);
			$row = Ebh()->db->update('ebh_custommessages',$setarr,$wherearr);
			return $row;
		}
	}
	
	
	/*
	获取自定义富文本
	@param $param crid,index
	@return array
	*/
	public function getCustomMessage($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$param['crid'] = intval($param['crid']);
		$sql = 'select crid,custommessage,appstr,`index` from ebh_custommessages';
		$wherearr[] = 'crid='.$param['crid'];
		if(isset($param['index']))
			$wherearr[] = '`index` in ('.$param['index'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array();
	}


    /**
     * 通过UID获取学生的网校列表
     * @param $uid
     * @return mixed
     */
	public function getStudentClassRoomListByUid($uid){
	    $time = time();
	    $sql = 'select cr.crid,cr.crname,cr.summary,cr.cface,cr.template,cr.isschool,cr.property from ebh_classrooms cr join ebh_roomusers ru on ru.crid=cr.crid where ru.uid='.$uid.' and  cr.status=1 and cr.enddate >='.$time;
        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 通过教师ID获取教师的网校列表
     * @param $uid
     * @return mixed
     */
    public function getTeacherClassRoomListByUid($uid){
        $time = time();
        $sql = 'select cr.crid,cr.crname,cr.summary,cr.cface,cr.template,cr.isschool,cr.property from ebh_classrooms cr join ebh_roomteachers rt on rt.crid=cr.crid where rt.tid='.$uid.' and  rt.status=1 and  cr.status=1 and cr.enddate >='.$time;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取系统配置信息
     * @param $crid
     * @return array
     */
    public function getSystemSetting($crid) {
        if (isset($this->_systemsetting))
            return $this->_systemsetting;



        $redis_key = 'room_systemsetting_' . $crid;

        $_systemsetting = Ebh()->cache->get($redis_key);

        if (empty($_systemsetting)){
            $systemsetting = new SystemSettingModel();
            $_systemsetting = $systemsetting->getSetting($crid);

            Ebh()->cache->set($redis_key,$_systemsetting);
        }

        return $_systemsetting;
    }
    /**
     * 添加教室对应的学生数
     * @param int $crid 教室编号
     * @param int $num 如为正数则添加，负数则为减少
     */
    public function addstunum($crid,$num = 1) {
        $where = 'crid='.$crid;
        $setarr = array('stunum'=>'stunum+'.$num);
        Ebh()->db->update('ebh_classrooms',array(),$where,$setarr);
    }
	/**
     * 更新网校课件数
     * @param $crid 网校ID
     */
	public function statsCourseware($crid) {
        $crid = intval($crid);
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_roomcourses` `a` LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE `a`.`crid`='.
            $crid.' AND `b`.`status`=1';
        $c = Ebh()->db->query($sql)->row_array();
        $this->update($crid, array('coursenum' => $c['c']),'`crid`='.$crid);
    }

    /**
     * 获取包含多个网校的数组
     * @param  array $crid_array crid数组
     * @return array            网校数组
     */
    public function getClassRoomArray($crid_array) {
        $classroom_array = array();
        if (!empty($crid_array) && is_array($crid_array))
        {
            $crid_array = array_unique($crid_array);
            $sql = 'SELECT crid,crname from ebh_classrooms WHERE crid IN(' . implode(',', $crid_array) . ')';
            $row = Ebh()->db->query($sql)->list_array();
            foreach ($row as $v)
            {
                $classroom_array[$v['crid']] = $v['crname'];
            }
        }
        return $classroom_array;
    }

    /**
     * 获取所有网校列表
     * @return 所有网校列表
     */
    public function getClassRoomListAll(){
        $sql = 'select c.crid,c.domain,c.crname,c.cface from ebh_classrooms c';
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 网校管理员帐号
     * @param mixed $crid 网校ID
     * @return mixed
     */
    public function getAdministrator($crid) {
        if (is_array($crid)) {
            $crids = array_map('intval', $crid);
            $crids = array_filter($crids, function($crid) {
               return $crid > 0;
            });
            if (empty($crids)) {
                return array();
            }
            $crids = array_unique($crids);
            $sql = 'SELECT `a`.`crid`,`b`.`uid`,`b`.`password` FROM `ebh_classrooms` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` WHERE `a`.`crid` IN('.implode(',', $crids).')';
            return Ebh()->db->query($sql)->list_array('crid');
        }
        $sql = 'SELECT `a`.`crid`,`b`.`uid`,`b`.`password` FROM `ebh_classrooms` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` WHERE `a`.`crid`='.intval($crid);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 添加教室对应的答疑数
     * @param int $crid 教室编号
     * @param int $num 如为正数则添加，负数则为减少
     */
    public function addasknum($crid,$num=1) {
        $where = 'crid='.$crid;
        $setarr = array('asknum'=>'asknum+'.$num);
        Ebh()->db->update('ebh_classrooms',array(),$where,$setarr);
    }

    /**
     * 根据学生编号获取学生有权限的平台 TV版
     * @param int $uid学生编号
     * @return array 平台列表
     */
    function getRoomlistByUidForTv($uid,$hastv = 0,$q = '') {
        $sql = 'select c.crid as rid,c.crname as rname,c.summary,c.isschool,c.tvlogo as face,rc.enddate from ebh_roomusers rc ' .
            'join ebh_classrooms c on (rc.crid = c.crid) ' .
            'where rc.uid = ' . $uid . ' and rc.cstatus = 1';
        if(!empty($hastv)){
            $sql .= ' and c.hastv = 1 ';
        }
        if(!empty($q)){
            $sql .= ' and ( c.domain like \''.$this->db->escape_str($q).'%\' or  c.crname like \'%'.$this->db->escape_str($q).'%\' or c.jp like \'%'.$this->db->escape_str($q).'%\' or c.qp like \'%'.$this->db->escape_str($q).'%\')';
        }
        $list = $this->db->query($sql)->list_array();
        $roomlist = array();
        foreach($list as $row) {
            /*
            if($row['isschool'] == 3) {
                $row['status'] = 1;
                $row['enddate'] = '无限制';
            } else {
                if($row['enddate'] < SYSTIME) {
                    $row['status'] = 0;
                } else {
                    $row['status'] = 1;
                }
                $row['enddate'] = empty($row['enddate']) ? '' : date('Y-m-d',$row['enddate']);
            }*/
            $row['status'] = 1;
            $row['enddate'] = '无限制';
            $face = $row['face'];
            if (empty($face))
                $face = 'http://static.ebanhui.com/ebh/tpl/default/images/dtvlog.jpg';
            else if (strpos( $face,'ebanhui.com') === FALSE) {
                $face = 'http://www.ebanhui.com'.$face;
            }
            $row['face'] = $face;
            $roomlist[] = $row;
        }
        return $roomlist;
    }

    //获取所有有封面的网校
    public function getTvRoomList($param = array()){
        $sql = 'select c.crid as rid,c.crname as rname,c.summary,c.isschool,c.tvlogo as face from ebh_classrooms c';
        $wherearr = array();
        //$wherearr[] = ' c.hastv = 1';
        if( !empty($param['crid_not_in']) && is_array($param['crid_not_in']) ){
            $wherearr[] = ' c.crid not in ('.implode(',', $param['crid_not_in']).')';
        }
        if(!empty($param['q'])){
            $wherearr[] = ' ( c.domain like \''.$this->db->escape_str($param['q']).'%\' or  c.crname like \'%'.$this->db->escape_str($param['q']).'%\' or c.jp like \'%'.$this->db->escape_str($param['q']).'%\' or c.qp like \'%'.$this->db->escape_str($param['q']).'%\')';
        }
        if(!empty($param['lastcrid'])){
            $wherearr[] = ' c.crid < '.$param['lastcrid'];
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ', $wherearr);
        }
        if(!empty($param['order'])){
            $sql .= ' order by '.$this->db->escape_str($param['order']);
        }else{
            $sql .= ' order by c.crid desc ';
        }
        if(!empty($param['limit'])){
            $sql .= $param['limit'];
        }
        $list = $this->db->query($sql)->list_array();
        $roomlist = array();
        foreach($list as $row) {
            $row['status'] = 0;
            $row['enddate'] = '';
            $face = $row['face'];
            if (empty($face))
                $face = 'http://static.ebanhui.com/ebh/tpl/default/images/dtvlog.jpg';
            else if (strpos( $face,'ebanhui.com') === FALSE) {
                $face = 'http://www.ebanhui.com'.$face;
            }
            $row['face'] = $face;
            $roomlist[] = $row;
        }
        return $roomlist;
    }
    //获取用户有封面的网校(用户购买了的靠前放)
    public function getUserTvRoomList($uid=0,$q=''){
        $userTvRoomList = $this->getRoomlistByUidForTv($uid,0,$q);
        $param = array(
            'crid_not_in'=>array(),
            'q'=>$q,
            'limit' =>  'limit 20'
        );
        if(!empty($userTvRoomList)){
            foreach ($userTvRoomList as $room) {
                $param['crid_not_in'][] = $room['rid'];
            }
        }
        $otherTvRoomList = $this->getTvRoomList($param);
        $roomlist =  array_merge($userTvRoomList,$otherTvRoomList);
        $haspower = array();
        $nothaspower = array();
        foreach ($roomlist as $room) {
            if(!empty($room['status'])){
                $haspower[] = $room;
            }else{
                $nothaspower[] = $room;
            }
        }
        $ret = array_merge($haspower,$nothaspower);
        return $ret;
    }

    /*
	详情
	@param int $crid
	@return array
	*/
    public function getclassroomdetail($crid){
        $sql = 'select c.catid,c.crid,c.stunum,c.teanum,c.crname,c.begindate,c.banner,c.upid,c.enddate,c.dateline,c.maxnum,c.domain,c.status,c.citycode,c.cface,c.craddress,c.crqq,c.crphone,c.cremail,c.crlabel,c.summary,c.ispublic,c.isshare,c.modulepower,c.stumodulepower,c.isschool,c.grade,c.template,c.profitratio,c.crprice,c.displayorder,c.property,u.username,u.uid,u.realname,u.face,u.sex,c.floatadimg,c.floatadurl,c.showusername,c.defaultpass,c.hastv,c.tvlogo,c.custommodule,c.iscollege,c.wechatimg,c.message,c.lng,c.lat,c.isdesign from ebh_classrooms c join ebh_users u on u.uid = c.uid where c.crid='.$crid;
        return $this->db->query($sql)->row_array();
    }

    /**
     *获取网校的分享信息
     */
    public function getShareInfo($crid=0) {
        $crid = intval($crid);
        if (empty($crid)) {
            return false;
        }
        $sql = 'select isshare,sharepercent from ebh_systemsettings where crid='.$crid;
        return $this->db->query($sql)->row_array();
    }
}
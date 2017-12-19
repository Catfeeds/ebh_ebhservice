<?php
/**
 * 黑名单模型
 */
class BlackModel{
	private $db;

    function __construct(){
        $this->db = Ebh()->db;
    }

    /**
     * 获取黑名单首页
     */
    public function index(){
        $sql = "select b.*,c.crname from ebh_blacklists b left join ebh_classrooms c on b.crid=c.crid order by dateline desc limit 0,20";
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取总记录数
     */
    public function blackCount(){
        $sql = 'select count(*) as count from ebh_blacklists';
        return $this->db->query($sql)->row_array();
    }

    /**
     * 获取黑名单列表
     */
    public function getBlackList($offset,$pagesize){
    	$sql = "select b.*,c.crname from ebh_blacklists b left join ebh_classrooms c on b.crid=c.crid order by dateline desc limit $offset,$pagesize";
    	return $this->db->query($sql)->list_array();
    }

    /**
     * 删除黑名单
     */
    public function delBlack($bid){
        $sql = "delete from ebh_blacklists where bid in ($bid)";
        return $this->db->query($sql);
    }

    /**
     * 校验登录用户名是否已经存在于黑名单中
     */
    public function checkUser($username,$deny){
        $sql = "select count(1) as count from ebh_blacklists where username='$username' and ";
        if($deny == 'LOGIN'){
            $sql .= "(deny='LOGIN' or deny='ALL')";
        }
        if($deny == 'REGISTER'){
            $sql .= "(deny='REGISTER' or deny='ALL')";
        }
        if($deny == 'CREATEROOM'){
            $sql .= "(deny='CREATEROOM' or deny='ALL')";
        }
        if($deny == 'ALL'){
            $sql .= "deny='ALL'";
        }
        return $this->db->query($sql)->row_array();
    }

    /**
     * 添加用户名黑名单
     */
    public function addUserBlack($crids,$uid,$operid,$username,$remark){
        if($crids === 0){
            //如果限制范围为所有网校
            $params['crid'] = 0;
            $params['uid'] = intval($uid);
            $params['operid'] = intval($operid);
            $params['username'] = $username;
            $params['deny'] = 'LOGIN';
            $params['type'] = 2;
            $params['dateline'] = SYSTIME;
            $params['remark'] = $remark;
            $params['state'] = 1;
            $bid = $this->db->insert('ebh_blacklists',$params);
            return $bid;
        }else{
            //限制范围为所属网校
            foreach($crids as $crid){
                $params['crid'] = intval($crid['crid']);
                $params['uid'] = intval($uid);
                $params['operid'] = intval($operid);
                $params['username'] = $username;
                $params['deny'] = 'LOGIN';
                $params['type'] = 2;
                $params['dateline'] = SYSTIME;
                $params['remark'] = $remark;
                $params['state'] = 1;
                $bid = $this->db->insert('ebh_blacklists',$params);
                if(!$bid){
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * 校验登录IP是否已经存在于黑名单中
     */
    public function checkIp($ip,$deny){
        $ip = ip2long($ip);
        $sql = "select count(1) as count from ebh_blacklists where ip=$ip and ";
        if($deny == 'LOGIN'){
            $sql .= "(deny='LOGIN' or deny='ALL')";
        }
        if($deny == 'REGISTER'){
            $sql .= "(deny='REGISTER' or deny='ALL')";
        }
        if($deny == 'CREATEROOM'){
            $sql .= "(deny='CREATEROOM' or deny='ALL')";
        }
        if($deny == 'ALL'){
            $sql .= "deny='ALL'";
        }
        return $this->db->query($sql)->row_array();
    }

    /**
     * 添加IP黑名单
     */
    public function addIpBlack($operid,$ip,$addr,$remark){
        $params['crid'] = 0;//ip限制范围为所有网校
        $params['operid'] = intval($operid);
        $params['ip'] = ip2long($ip);
        $params['addr'] = $addr;
        $params['deny'] = 'LOGIN';
        $params['type'] = 3;
        $params['dateline'] = SYSTIME;
        $params['remark'] = $remark;
        $params['state'] = 1;
        $bid = $this->db->insert('ebh_blacklists',$params);
        return $bid;
    }

    /**
     * 根据用户的uid获取所属网校的crid
     * @param  $user [用户信息数组]
     */
    public function getCridByUid($user){
        //判断是老师还是学生
        if($user['groupid'] == 5){
            $sql = "select crid from ebh_roomteachers where tid={$user['uid']}";
        }else{
            $sql = "select crid from ebh_roomusers where uid={$user['uid']}";
        }
        $crid = $this->db->query($sql)->list_array();
        return $crid;
    }

    /**
     * 校验手机号是否存在于黑名单中
     */
    public function checkMobile($mobile,$deny){
        $sql = "select count(1) as count from ebh_blacklists where mobile='$mobile' and ";
        if($deny == 'REGISTER'){
            $sql .= "(deny='REGISTER' or deny='ALL')";
        }
        if($deny == 'CREATEROOM'){
            $sql .= "(deny='CREATEROOM' or deny='ALL')";
        }
        if($deny == 'ALL'){
            $sql .= "deny='ALL'";
        }
        return $this->db->query($sql)->row_array();
    }

    /**
     * 添加注册黑名单
     */
    public function addRegister($operid,$username,$ip,$addr,$mobile,$deny,$remark){
        $params['crid'] = 0;//ip限制范围为所有网校
        $params['operid'] = intval($operid);
        $params['username'] = $username;
        $params['ip'] = !empty($ip) ? ip2long($ip) : 0;
        $params['addr'] = !empty($addr) ? $addr : '';
        $params['mobile'] = $mobile;
        $params['deny'] = $deny;
        $params['type'] = 0;
        $params['dateline'] = SYSTIME;
        $params['remark'] = $remark;
        $params['state'] = 1;
        $bid = $this->db->insert('ebh_blacklists',$params);
        return $bid;
    }

    /**
     * 获取创建网校黑名单
     */
    public function getCreateroomBlack(){
        $sql = "select mobile from ebh_blacklists where mobile!='' and (deny='ALL' or deny='CREATEROOM')";
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取注册用户黑名单
     */
    public function getRegisterBlack(){
        $usql = "select username from ebh_blacklists where username!='' and (deny='ALL' or deny='REGISTER')";
        //获取用户名 注册用户黑名单
        $ublack = $this->db->query($usql)->list_array();
        $msql = "select mobile from ebh_blacklists where mobile!='' and (deny='ALL' or deny='REGISTER')";
        //获取手机 注册用户黑名单
        $mblack = $this->db->query($msql)->list_array();
        
        return array_merge($ublack,$mblack);
    }
}
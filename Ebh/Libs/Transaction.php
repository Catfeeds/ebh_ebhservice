<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:48
 * 交易处理类
 * 用于调度支付类
 */
class Transaction{

    public $payConfig = null;
    public $payClass = '';

    private $paymentPath = '';

    private $payObj = null;
    private static $sync_crlist = array();
    private static $sync_classlist = array();
    private static $rsync_data = array();
    /**
     *
     * Transaction constructor.
     * @param $type 支付方式
     * @throws Exception_BadRequest
     */
    public function __construct($type){
        //设置支付类库所在目录



        $paymentConfig = Ebh()->config->get('payment');

        if(!isset($paymentConfig[$type])){
            throw new Exception_BadRequest('支付方式不存在');
        }

        $this->payConfig = $paymentConfig[$type];
        $this->payClass = ucfirst($this->payConfig['class']);
        $this->paymentPath = APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'payment' . DIRECTORY_SEPARATOR . $this->payConfig['class'];
        $payClassFilePath = $this->paymentPath . DIRECTORY_SEPARATOR . $this->payClass . '.php';
        if(!file_exists($payClassFilePath)){
            throw new Exception_BadRequest('支付方式不存在');
        }

        require_once $payClassFilePath;

        $this->payObj = new $this->payClass($this->payConfig['config']);

    }



    /**
     * 获取支付方式实例
     * @return null
     */
    public function getObj(){
        return $this->payObj;
    }


    /**
     * 回调处理订单
     * @param $orderid 订单ID
     * @param $ordernumber 支付外部单号
     */
    public static function notifyOrder($orderid,$ordernumber,$param = array()){
        Ebh()->db->set_con(0);
        $payOrderModel = new PayorderModel();
        $order = $payOrderModel->getOrderById($orderid);
        if(empty($order)) {//订单不存在
            return false;
        }
        if($order['status'] == 1){//订单已经处理 不重复处理
            return $order;
        }
        //订单中详情内容不存在
        if(empty($order['detaillist'])) {
            return false;
        }

        $providercrids = array();	//订单下内容提供商的crid列表，如果大于1，需要拆分订单
        foreach($order['detaillist'] as $detail) {
            $detail['uid'] = $order['uid'];
            self::doOrderItem($detail);
            $detailprovidercrid = $detail['providercrid'];
            if(!isset($providercrids[$detailprovidercrid])){
                $providercrids[$detailprovidercrid] = $detailprovidercrid;
            }
        }
        //更新订单信息
        $buyer_id = empty($param['buyer_id'])?'':$param['buyer_id'];
        $buyer_info = empty($param['buyer_info'])?'':$param['buyer_info'];
        $order['status'] = 1;
        if(isset($param['payip'])){
            $order['payip'] = $param['payip'];
        }
        $order['paytime'] = SYSTIME;
        $order['ordernumber'] = $ordernumber;
        $order['buyer_id'] = $buyer_id;
        $order['buyer_info'] = $buyer_info;

        //拆分订单处理，当订单明细的提供商crid不同时，则将订单改成每个订单明细对应一个订单。
        $providercount = count($providercrids);
        if($providercount > 1) {
            for ($i = 0; $i < count($order['detaillist']); $i ++) {
                if($i == 0) {
                    $order['providercrid'] = $order['detaillist'][$i]['providercrid'];
                    $order['totalfee'] = $order['detaillist'][$i]['fee'];
                    $order['comfee'] = $order['detaillist'][$i]['comfee'];
                    $order['roomfee'] = $order['detaillist'][$i]['roomfee'];
                    $order['providerfee'] = $order['detaillist'][$i]['providerfee'];
                    $order['ordername'] = '开通 '.$order['detaillist'][$i]['oname'].' 服务';
                    $order['remark'] = $order['detaillist'][$i]['oname'].'_'.(empty($order['detaillist'][$i]['omonth']) ? $order['detaillist'][$i]['oday'].' 天 _':$order['detaillist'][$i]['omonth'].' 月 _').$order['detaillist'][$i]['fee'].' 元';
                }else{
                    $neworder = $order;
                    $neworder['providercrid'] = $order['detaillist'][$i]['providercrid'];
                    $neworder['totalfee'] = $order['detaillist'][$i]['fee'];
                    $neworder['comfee'] = $order['detaillist'][$i]['comfee'];
                    $neworder['roomfee'] = $order['detaillist'][$i]['roomfee'];
                    $neworder['providerfee'] = $order['detaillist'][$i]['providerfee'];
                    $neworder['ordername'] = '开通 '.$order['detaillist'][$i]['oname'].' 服务';
                    $neworder['remark'] = $order['detaillist'][$i]['oname'].'_'.(empty($order['detaillist'][$i]['omonth']) ? $order['detaillist'][$i]['oday'].' 天 _':$order['detaillist'][$i]['omonth'].' 月 _').$order['detaillist'][$i]['fee'].' 元';
                    $neworderid = $payOrderModel->addOrder($neworder,TRUE);
                    $order['detaillist'][$i]['orderid'] = $neworderid;
                }
            }
        }

        $order['itemlist'] = $order['detaillist'];
        $payOrderModel->updateOrder($order);


        //更新学校学生缓存和同步SNS数据
        $snsLib = new Sns();
        if (!empty(self::$sync_crlist))
        {
            foreach (self::$sync_crlist as $crid) {
                //更新学校学生缓存
                $snsLib->updateRoomUserCache(array('crid'=>$crid,'uid'=>$order['uid']));
                //同步SNS数据(网校操作)
                $snsLib->do_sync($order['uid'], 4);
            }
        }
        //更新班级学生缓存
        if (!empty(self::$sync_classlist))
        {
            foreach (self::$sync_classlist as $classid)
            {
                //更新班级学生缓存
                $snsLib->updateClassUserCache(array('classid'=>$classid,'uid'=>$order['uid']));
            }
        }
        //使用优惠券后返利处理
        $userModel = new UserModel();
        $tmpuser = $userModel->getUserInfoByUid($order['uid']);

        if(empty($tmpuser)){
            log_message('订单关联用户uid:'.$order['uid'].'获取失败');
        }

        $user = $tmpuser[0];

        $couponModel = new CouponsModel();
        if(!empty($order['couponcode'])){
            $reward = 0;
            $ip = getip();
            $cashbackModel = new CashbackModel();
            $coupon = $couponModel->getOne(array('code'=>$order['couponcode']));
            if(!empty($coupon) && $coupon['uid'] != $user['uid']){
                foreach ($order['itemlist'] as $item){
                    $reward = $item['fee'] - $item['comfee'] - $item['roomfee'] - $item['providerfee'];
                    if($reward<=0){
                        continue;
                    }

                    $cparam['uid'] = $coupon['uid'];
                    $cparam['fromcrid'] = $item['crid'];
                    $cparam['crname'] = $item['rname'];
                    $cparam['fromuid'] = $user['uid'];
                    $cparam['fromname'] = !empty($user['realname']) ? $user['realname'] : $user['username'];
                    $cparam['servicestxt'] = '开通&nbsp;'.$item['oname'];
                    $cparam['reward'] = $reward;
                    $cparam['fromip'] = $ip;
                    $cparam['time'] = SYSTIME;
                    //依次加入记录至返现记录表
                    $ret = $cashbackModel->add($cparam);

                    if(!$ret){
                        log_message('开通&nbsp;'.$item['oname'].'&nbsp;&nbsp;返利失败,关联uid:'.$cparam['uid']);
                    }
                }
            }
        }

        //生成属于自己的优惠码
        $mycoupon = $couponModel->getOne(array('uid'=>$user['uid']));
        if(empty($mycoupon)){
            $couponarr['uid'] = $user['uid'];
            $couponarr['code'] = $couponModel->getcouponcode();
            $couponarr['createtime'] = SYSTIME;
            $couponarr['orderid'] = $orderid;
            $couponarr['crid'] = $order['crid'];
            $myret = $couponModel->add($couponarr);
            if(!$myret){
                log_message('生成优惠码失败,关联uid:'.$couponarr['uid']);
            }
        }

        /*//通知第三方
        if(!empty(self::$rsync_data)){
            foreach (self::$rsync_data as $data) {
                rsapi_call($data['crid'],'folder_buyed',$data);
            }
        }*/

        //处理分销返利情况
        if (!empty($order['isshare']) && !empty($order['sharefee']) && !empty($order['shareuid'])) {
            $order['sharedetail'] = empty($user['realname'])?$user['username']:$user['realname'].' '.$order['ordername'].'  价格: <em>'.$order['totalfee'].'</em>';
            $shareModel = new ShareModel();
            $res =  $shareModel->addCharge($order);
            if (empty($res)) {
                log_message('分销失败,关联uid:'.$order['shareuid']);
            }
        }

        return $order;
    }

    /**
     *支付成功后处理订单详情（主要为生成权限）
     */
    public static function doOrderItem($orderdetail){
        self::$sync_crlist = array();
        self::$sync_classlist = array();
        self::$rsync_data = array();
        $crid = $orderdetail['crid'];
        $folderid = $orderdetail['folderid'];
        $uid = $orderdetail['uid'];
        $omonth= $orderdetail['omonth'];
        $oday= $orderdetail['oday'];
        $cwid = empty($orderdetail['cwid'])?0:$orderdetail['cwid'];
        $classroomModel = new ClassRoomModel();

        $roominfo = $classroomModel->getRoomByCrid($crid);
        if(empty($roominfo)){
            return false;
        }
        $userModel = new UserModel();
        $user = $userModel->getuserbyuid($uid);
        if(empty($user)){
            return false;
        }

        $roomuserModel = new RoomUserModel();

        $ruser = $roomuserModel->getroomuserdetail($crid,$uid);
        $type = 0;
        if(empty($ruser)) {//不存在网校中
            $enddate = 0;
            if(!empty($crid)) {
                if(!empty($omonth)) {
                    $enddate = strtotime("+$omonth month");
                }else{
                    $enddate = strtotime("+$oday day");
                }
            }

            $param = array('crid'=>$crid,'uid'=>$user['uid'],'begindate'=>SYSTIME,'enddate'=>$enddate,'cnname'=>$user['realname'],'sex'=>$user['sex']);
            $result = $roomuserModel->insert($param);
            $type = 1;
            if($result !== false) {
                if($roominfo['isschool'] == 6 || $roominfo['isschool'] == 7) {    //如果是收费学校，则会将账号默认添加到学校的第一个班级中
                    self::setmyclass($crid,$user['uid'],$folderid);
                }else{
                    $classroomModel->addstunum($crid);
                }

                //记录需要更新缓存和SNS同步操作的学校项目
                self::$sync_crlist[] = $crid;
            }

        }else{
            //已存在
            if($roominfo['isschool'] == 6 || $roominfo['isschool'] == 7){
                self::setmyclass($roominfo['crid'],$user['uid'],$folderid);//防止中途改变学校类型,导致学生在学校里面但是不在班级里面(网校改成学校)
            }
            $enddate=$ruser['enddate'];
            $newenddate=0;
            if(!empty($crid)) {
                if(!empty($omonth)) {
                    if(SYSTIME>$enddate){//已过期的处理
                        $newenddate=strtotime("+$omonth month");
                    }else{	//未过期，则直接在结束时间后加上此时间
                        $newenddate=strtotime( date('Y-m-d H:i:s',$enddate)." +$omonth month");
                    }
                }else {
                    if(SYSTIME>$enddate){//已过期的处理
                        $newenddate=strtotime("+$oday day");
                    }else{	//未过期，则直接在结束时间后加上此时间
                        $newenddate=strtotime( date('Y-m-d H:i:s',$enddate)." +$oday day");
                    }
                }
            }

            $param = array('crid'=>$crid,'uid'=>$user['uid'],'enddate'=>$newenddate,'cstatus'=>1);
            $result = $roomuserModel->update($param);
            $type = 2;
        }

        //处理用户权限

        $userpermisionModel = new UserpermisionsModel();
        if(!empty($orderdetail['cwid'])) {//单课收费
            $myperm = $userpermisionModel->getPermissionByCwId($orderdetail['cwid'],$uid);
        }elseif(empty($orderdetail['folderid'])) {
            $myperm = $userpermisionModel->getPermissionByItemId($orderdetail['itemid'],$uid);
        } else {
            $myperm = $userpermisionModel->getPermissionByFolderId($orderdetail['folderid'],$uid,$crid);
        }
        $startdate = 0;
        $enddate = 0;

        if(empty($myperm)) {	//不存在则添加权限，否则更新
            $startdate = SYSTIME;
            if(!empty($omonth)) {
                $enddate = strtotime("+$omonth month");
            } else {
                $enddate = strtotime("+$oday day");
            }
            $ptype = 0;
            if(!empty($folderid) || !empty($crid)) {
                $ptype = 1;
            }
            $perparam = array('itemid'=>$orderdetail['itemid'],'type'=>$ptype,'uid'=>$uid,'crid'=>$crid,'folderid'=>$folderid,'cwid'=>$cwid,'startdate'=>$startdate,'enddate'=>$enddate);
            $result = $userpermisionModel->addPermission($perparam);
        } else {
            $enddate=$myperm['enddate'];
            $newenddate=0;
            if(!empty($omonth)) {
                if(SYSTIME>$enddate){//已过期的处理
                    $newenddate=strtotime("+$omonth month");
                }else{	//未过期，则直接在结束时间后加上此时间
                    $newenddate=strtotime( date('Y-m-d H:i:s',$enddate)." +$omonth month");
                }
            }else {
                if(SYSTIME>$enddate){//已过期的处理
                    $newenddate=strtotime("+$oday day");
                }else{	//未过期，则直接在结束时间后加上此时间
                    $newenddate=strtotime( date('Y-m-d H:i:s',$enddate)." +$oday day");
                }
            }
            $enddate = $newenddate;
            $myperm['enddate'] = $enddate;
            if(!empty($orderdetail['itemid'])) {
                $myperm['itemid'] = $orderdetail['itemid'];
            }
            $result = $userpermisionModel->updatePermission($myperm);
        }

        self::$rsync_data[] = array('crid'=>$crid,'uid'=>$uid,'fid'=>$folderid);
        //用户平台信息更新成功则生成记录并更新年卡信息

        //删除订单收藏
        $collectModel = new CollectModel();
        $collectModel->del($uid,$crid,$folderid);
        return $result;
    }


    public static function setmyclass($crid,$uid,$folderid){
        $classesModel = new ClassesModel();
        //先判断是否已经加入班级，已经加入则无需重新加入
        $myclass = $classesModel->getClassByUid($crid,$uid);
        $folderModel = new FolderModel();
        if(empty($myclass)) {
            //获取课程对应的年级和地区信息
            $grade = 0;
            $district = 0;

            $folderInfo = $folderModel->getfolderbyid($folderid);
            $classname = "默认班级";
            if(!empty($folderInfo)){
                $grade = $folderInfo['grade'];
                $district = $folderInfo['district'];
                $grademap = Ebh()->config->get('grade');
                if(array_key_exists($grade, $grademap)){
                    $classname = $grademap[$grade].'默认班级';
                }
            }

            $classid = 0;
            $defaultclass = $classesModel->getDefaultClass($crid,$grade,$district);
            if(empty($defaultclass)) {//不存在默认班级，则创建默认班级
                $param = array('crid'=>$crid,'classname'=>$classname,'grade'=>$grade,'district'=>$district);
                $classid = $classesModel->addclass($param);
            }else{
                $classid = $defaultclass['classid'];
            }
            $param = array('crid'=>$crid,'classid'=>$classid,'uid'=>$uid);

            $classesModel->addclassstudent($param);

            //记录需要更新缓存的班级项目
            self::$sync_classlist[] = $classid;
        }

    }



}
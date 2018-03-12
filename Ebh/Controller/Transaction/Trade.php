<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:58
 * 交易处理控制器
 */
class TradeController extends Controller{

    //支付方式映射
    private $payFrom = array(
        'alipay'    =>  3,
        'alipayqrcode'    =>  3,
        'abcpay'    =>  6,
        'balance'   =>  8,
        'wxpayqrcode'   =>  9,
        'wxapppay'  =>  9,
        'wxpublicpay'   =>  9,
        'wxh5pay'   =>  9,
        'redeemcode'   =>  10,
    );
    public function parameterRules(){
        return array(
            'orderAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),//网校ID
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),//用户ID
                'itemid'  =>  array('name'=>'itemid','require'=>true,'type'=>'array'),//产品ID
                'couponcode'  =>  array('name'=>'couponcode','require'=>false,'default'=>''),//优惠券
                'ip'  =>  array('name'=>'ip','require'=>true),//产品ID
                'paytype'  =>  array('name'=>'paytype','require'=>true,'type'=>'enum','range'=>Ebh()->config->get('payment.paytype')),//支付方式 wxapppay=小程序
                'parameters'    =>  array('name'=>'parameters','default'=>array(),'type'=>'array')//附加数据
            ),
            'buildOrderAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),//网校ID
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),//用户ID
                'itemid'  =>  array('name'=>'itemid','require'=>false,'type'=>'array'),//产品ID
                'cwid'  =>  array('name'=>'cwid','require'=>false,'type'=>'int'),//课件ID 单课购买
                'bid'  =>  array('name'=>'bid','require'=>false,'type'=>'int'),//打包课程购买
                'sid'  =>  array('name'=>'sid','require'=>false,'type'=>'int'),//sid分类课程购买
                'couponcode'  =>  array('name'=>'couponcode','require'=>false,'default'=>''),//优惠券
                'ip'  =>  array('name'=>'ip','require'=>true),//产品ID
                'paytype'  =>  array('name'=>'paytype','require'=>true,'type'=>'enum','range'=>Ebh()->config->get('payment.paytype')),//支付方式 wxapppay=小程序
                'parameters'    =>  array('name'=>'parameters','default'=>array(),'type'=>'array'),//附加数据
                'redeemprice'  =>  array('name'=>'redeemprice','require'=>false,'type'=>'int','default'=>0),//兑换码单价，用户通过兑换码购买时，数据库读出的单价，仅当paytype=redeemcode,有效
                'sharekey'  =>  array('name'=>'sharekey','require'=>false,'default'=>''),//分销信息 分享key
                'onlyorder'  =>  array('name'=>'onlyorder','require'=>false,'type'=>'int','default'=>0),//只创建订单
            ),
            'notifyAction'  =>  array(
                'paytype'  =>  array('name'=>'paytype','require'=>true,'type'=>'enum','range'=>Ebh()->config->get('payment.paytype')),//支付方式 wxapppay=小程序
            ),
            'getPaymentAction'  =>  array(
                'scope'  =>  array('name'=>'scope','require'=>true,'type'=>'int'),//作用域
            )
        );
    }

    /**
     * 获取可用支付方式
     */
    public function getPaymentAction(){
        $paymentConfig = Ebh()->config->get('payment');
        $paytypes = $paymentConfig['paytype'];

        $payment = array();

        foreach ($paytypes as $paytype){
            if(in_array($this->scope,$paymentConfig[$paytype]['scope'])){
                $payment[] = array(
                    'class'  =>  $paymentConfig[$paytype]['class'],
                    'name'  =>  $paymentConfig[$paytype]['name'],
                    'runtype'  =>  $paymentConfig[$paytype]['runtype']
                );
            }
        }

        return $payment;
    }

    /**
     * 创建一个订单入口
     * 用于创建订单 发起支付
     * @return array
     */
    public function buildOrderAction(){
        $userModel = new UserModel();
        $userInfo = $userModel->getUserByUid($this->uid);

        if(!$userInfo){
            return returnData(0,'读取用户信息错误');
        }

        $classroomModel = new ClassRoomModel();
        $roominfo = $classroomModel->getModel($this->crid);

        if(empty($roominfo)){
            return returnData(0,'读取网校信息错误');
        }

        //如果提交了优惠券
        $iscoupon = false;
        if($this->couponcode != ''){
            $couponsModel = new CouponsModel();
            $coupon = $couponsModel->getOne(array('code'=>$this->couponcode));
            if(!empty($coupon) && ($coupon['uid'] != $this->uid)){
                $iscoupon = true;
            }
        }
        $couponcode = $iscoupon ? $this->couponcode : '';
        //优惠券判断结束

        if($this->cwid > 0){
            //单课购买处理
            return $this->buildOrderCw($userInfo,$this->cwid);
        }

        if($this->bid > 0){
            //打包课程
            return $this->buildOrderBundle($userInfo,$this->bid,$roominfo);
        }

        $payitemModel = new PayitemModel();
        if($this->sid > 0){
            $paysortModel = new PaysortModel();
            $sort = $paysortModel->getSortdetail($this->sid);
            if (!empty($sort['showbysort'])) {
                $crid = empty($roominfo) ? 0 : $roominfo['crid'];
                $items = $payitemModel->getSortCourseList($sort['sid'], $crid);
                if (empty($items)) {
                    return returnData(0,'获取不到课程信息');
                }
                $itemidlist = array_keys($items);
            }
        }
        if (empty($itemidlist)) {
            $itemidlist = $this->itemid;
        }

        if(empty($itemidlist)) {
            return returnData(0,'获取服务套餐失败');
        }

        foreach($itemidlist as $itemid) {	//详情编号必须都为正整数
            if(!is_numeric($itemid) || $itemid <= 0){
                return returnData(0,'获取服务套餐失败');
            }
        }
        $itemidstr = implode(',',$itemidlist);
        $itemparam = array('itemidlist'=>$itemidstr);
        $itemlist = $payitemModel->getItemList($itemparam);

        //todo
        //兑换码订单价格单独处理
        $redeemprice = $this->redeemprice;
        if ($redeemprice && $this->payFrom[$this->paytype] == 10) {
            foreach ($itemlist as &$ivalue) {
                $ivalue['comfee'] = ($ivalue['comfee']/$ivalue['iprice'])*$redeemprice;
                $ivalue['roomfee'] = ($ivalue['roomfee']/$ivalue['iprice'])*$redeemprice;
                $ivalue['providerfee'] = ($ivalue['providerfee']/$ivalue['iprice'])*$redeemprice;;
                $ivalue['iprice'] = $redeemprice;
            }
        }

        if(empty($itemlist)) {
            return returnData(0,'获取服务套餐失败');
        }

        $folder_arr = array();
        if (!empty($roominfo) && $roominfo['crid'] == $itemlist[0]['crid']) {
            //当本校免费课程时用户是本校学生，价格置0
            $roomuserModel = new RoomUserModel();
            $is_alumni = $roomuserModel->isAlumni($roominfo['crid'], $this->uid);
            if ($is_alumni) {
                $folderid_arr = array_column($itemlist, 'folderid');
                $folderid_arr = array_unique($folderid_arr);
                $folderModel = new FolderModel();
                $folder_arr = $folderModel->getSchoolFreeFolderidList($folderid_arr);
            }
        }



        $orderparam = array();
        $orderparam['dateline'] = SYSTIME;
        $orderparam['ip'] = $this->ip;
        $orderparam['uid'] = $userInfo['uid'];
        $orderparam['payfrom'] = $this->payFrom[$this->paytype];
        $orderparam['couponcode'] = !empty($couponcode) ? $couponcode : ''; //优惠码
        $ordername = '';	//订单名称
        $remark = '';		//订单备注
        $totalfee = 0;	//订单总额
        $comfee = 0;	//公司分到总额
        $roomfee = 0;	//平台分到总额
        $providerfee = 0;	//内容提供商分到总额
        $flodernum = count($itemlist);                        //获取订单课程数量

        if ($redeemprice && $this->payFrom[$this->paytype] == 10) {//兑换码的不能打折
            $discount = 1;
        } else {
            $discount = $this->getDiscountByFolderNum($flodernum);//根据课程数量获取折扣//
        }

        for($i = 0; $i < count($itemlist); $i ++) {
            if (!empty($folder_arr) && in_array($itemlist[$i]['folderid'], $folder_arr)) {
                $itemlist[$i]['iprice'] = 0;
                $itemlist[$i]['comfee'] = 0;
                $itemlist[$i]['roomfee'] = 0;
                $itemlist[$i]['providerfee'] = 0;
                $itemlist[$i]['iprice_yh'] = 0;
                $itemlist[$i]['comfee_yh'] = 0;
                $itemlist[$i]['roomfee_yh'] = 0;
                $itemlist[$i]['providerfee_yh'] = 0;
            }
            $itemlist[$i]['fee'] = $itemlist[$i]['iprice']*$discount;
            $itemlist[$i]['oname'] = $itemlist[$i]['iname'];
            $itemlist[$i]['omonth'] = $itemlist[$i]['imonth'];
            $itemlist[$i]['oday'] = $itemlist[$i]['iday'];
            $itemlist[$i]['osummary'] = $itemlist[$i]['isummary'];
            $itemlist[$i]['uid'] = $userInfo['uid'];
            $itemlist[$i]['pid'] = $itemlist[$i]['pid'];
            $pid = $itemlist[$i]['pid'];
            $itemlist[$i]['rname'] = $itemlist[$i]['crname'];
            //如果该课程参加了优惠并且使用优惠券处理
            if($itemlist[$i]['isyouhui'] && !empty($couponcode)){
                $itemlist[$i]['fee'] = $itemlist[$i]['iprice_yh'];
                $itemlist[$i]['comfee'] = $itemlist[$i]['comfee_yh'];
                $itemlist[$i]['roomfee'] = $itemlist[$i]['roomfee_yh'];
                $itemlist[$i]['providerfee'] = $itemlist[$i]['providerfee_yh'];
                $totalfee += $itemlist[$i]['iprice_yh'];
            }else{
                $itemlist[$i]['comfee'] = $itemlist[$i]['comfee']*$discount;
                $itemlist[$i]['roomfee'] = $itemlist[$i]['roomfee']*$discount;
                $itemlist[$i]['providerfee'] = $itemlist[$i]['providerfee']*$discount;//*****订单详情，每个商品都变成折后价*******
                $totalfee += $itemlist[$i]['iprice'];
            }
            $comfee += $itemlist[$i]['comfee'];
            $roomfee += $itemlist[$i]['roomfee'];
            $providerfee += $itemlist[$i]['providerfee'];
            if(empty($ordername)) {
                $ordername = $itemlist[$i]['oname'];
            } else {
                $ordername .= ','.$itemlist[$i]['oname'];
            }
            $theremark = $itemlist[$i]['iname'].'_'.(empty($itemlist[$i]['omonth']) ? $itemlist[$i]['oday'].' 天 _':$itemlist[$i]['omonth'].' 月 _').$itemlist[$i]['fee'].' 元';
            if(empty($remark)) {
                $remark = $theremark;
            } else {
                $remark .= '/'.$theremark;
            }
            $providercrid = $itemlist[$i]['providercrid'];
        }
        $orderparam['crid'] = $itemlist[0]['crid'];
        $orderparam['providercrid'] = $itemlist[0]['providercrid'];	//来源平台crid
        $orderparam['pid'] = $pid;
        $orderparam['itemlist'] = $itemlist;
        $orderparam['totalfee'] = $totalfee*$discount;//**折后价**
        $orderparam['comfee'] = $comfee;
        $orderparam['roomfee'] = $roomfee;
        $orderparam['providerfee'] = $providerfee;
        $orderparam['ordername'] = '开通 '.$ordername.' 服务';
        $orderparam['remark'] = $remark;

        if(!empty($roominfo) && !empty($itemid)) {//查看企业选课,是则替换价格和期限
            $schsourceModel = new SchsourceModel();
            $schsourceitem = $schsourceModel->getSelectedItems(array('crid'=>$roominfo['crid'],'itemid'=>$itemid));
            if(!empty($schsourceitem)){
                if (empty($schsourceitem[$itemid]['compercent']) && empty($schsourceitem[$itemid]['roompercent']) && empty($schsourceitem[$itemid]['providerpercent'])) {
                    if (empty($schsourceitem[$itemid]['scompercent']) && empty($schsourceitem[$itemid]['sroompercent']) && empty($schsourceitem[$itemid]['sproviderpercent'])) {
                        $roomdetail = $classroomModel->getclassroomdetail($roominfo['crid']);
                        $profitratio = unserialize($roomdetail['profitratio']);
                    } else {
                        $profitratio['company'] = $schsourceitem[$itemid]['scompercent'];
                        $profitratio['teacher'] = $schsourceitem[$itemid]['sroompercent'];
                        $profitratio['agent'] = $schsourceitem[$itemid]['sproviderpercent'];
                    }


                } else {
                    $profitratio['company'] = $schsourceitem[$itemid]['compercent'];
                    $profitratio['teacher'] = $schsourceitem[$itemid]['roompercent'];
                    $profitratio['agent'] = $schsourceitem[$itemid]['providerpercent'];
                }

                $pre = $profitratio['company'] + $profitratio['agent'] + $profitratio['teacher'];
                foreach($schsourceitem as $si){
                    $orderparam['totalfee'] = $si['price']*$discount;
                    $orderparam['omonth'] = $si['month'];
                    $orderparam['crid'] = $si['crid'];
                    $orderparam['providercrid'] = $si['sourcecrid'];
                    $orderparam['isschsource'] = 1;
                    $orderparam['comfee'] = sprintf('%.2f', $si['price'] * $profitratio['company'] / $pre*$discount);
                    $orderparam['roomfee'] = sprintf('%.2f', $si['price'] * $profitratio['teacher'] / $pre*$discount);
                    $orderparam['providerfee'] = $orderparam['totalfee'] - $orderparam['comfee'] - $orderparam['roomfee'];
                    $orderparam['itemlist'][0]['comfee'] = $orderparam['comfee'];
                    $orderparam['itemlist'][0]['roomfee'] = $orderparam['roomfee'];
                    $orderparam['itemlist'][0]['providerfee'] = $orderparam['providerfee'];
                    $orderparam['itemlist'][0]['omonth'] = $si['month'];
                    $orderparam['itemlist'][0]['crid'] = $si['crid'];
                    $orderparam['remark'] = $si['name'].'_'.($si['month'].' 月 _').$si['price'].' 元';
                }
            }else{
                //非企业选课的，查看开通限制
                if(count($itemlist) == 1 && !empty($itemlist[0]['islimit']) && $itemlist[0]['limitnum']>0){
                    $openlimit = new OpenLimit();
                    $openstatus = $openlimit->checkStatus($itemlist[0],$this->uid);
                    if(!$openstatus){//状态设置为无法报名
                        return returnData(0,'你无法报名');
                    }
                }
            }
        }

        //分销信息,目前只支持不捆绑销售的课程
        $sharekey = $this->sharekey;
        if (!empty($sharekey)) {
            $shareInfo = $this->parse_sharekey($sharekey);
        }


        //判断是否分销，获取分销比例
        if (!empty($shareInfo[6]) && !empty($shareInfo[3]) && count($itemlist)==1 && $shareInfo[6]!=$userInfo['uid']) {
            if ($shareInfo[1] != $itemlist[0]['itemid'] && $shareInfo[0]!='school') {
                exit;
            }
            $schoolShareInfo = $classroomModel->getShareInfo($shareInfo[3]);
            if (!empty($schoolShareInfo['isshare'])) {//开启的逻辑
                $shareModel = new ShareModel();
                $userShare = $shareModel->getUserSharePre($shareInfo[6],$shareInfo[3]);//比例
                $shareuid = $shareInfo[6];
                if (empty($userShare)) {//没有，用默认
                    $sharepre = $schoolShareInfo['sharepercent'];
                } else {
                    $sharepre = $userShare['percent'];
                }
            }

        }

        if (!empty($shareuid) && !empty($sharepre)) {
            $orderparam['isshare'] = 1;
            $orderparam['shareuid'] = $shareuid;
            $orderparam['sharefee'] = sprintf('%.2f',$orderparam['roomfee']*$sharepre/100);
            $orderparam['roomfee'] = sprintf('%.2f',$orderparam['roomfee']*(100-$sharepre)/100);
            foreach ($orderparam['itemlist'] as &$lvalue) {
                $lvalue['isshare'] = 1;
                $lvalue['shareuid'] = $shareuid;
                $lvalue['sharefee'] = sprintf('%.2f',$lvalue['roomfee']*$sharepre/100);
                $lvalue['roomfee'] = sprintf('%.2f',$lvalue['roomfee']*(100-$sharepre)/100);
            }
        }

        $payOrderModel = new PayorderModel();
        $orderId = $payOrderModel->addOrder($orderparam);
        if(!$orderId || $orderId <= 0){
            return returnData(0,'创建订单失败');
        }

        $orderparam['orderid'] = $orderId;
        if($this->onlyorder == 1){
            return $orderparam;
        }
        $result = $this->payment($orderparam,$this->parameters);
        //判断是否存在开通服务后问卷，验证问卷有效性,并设置订单缓存用于支付成功后获取
        if($result['status'] == 1){
            $surveyparam = array();
            $surveyparam['crid'] = $this->crid;
            $surveyparam['uid'] = $this->uid;
            $surveyparam['count'] = $flodernum;
            $surveyparam['orderid'] = $orderparam['orderid'];
            $surveyparam['folderid'] = $orderparam['itemlist'][0]['folderid'];
            $this->checkSurveySid($surveyparam);
        }
        return $result;
    }

    /**
     * 单课收费信息(必须由buildOrderAction调用)
     * @param $user
     * @param $cwid
     * @return array
     */
    private function buildOrderCw($user,$cwid){
        $courseModel = new CoursewareModel();
        $cwdetail = $courseModel->getcwpay($cwid);
        if(empty($cwdetail)){
            return returnData(0,'获取课件信息失败');
        }
        $orderparam = array();

        $orderparam['dateline'] = SYSTIME;
        $orderparam['ip'] = $this->ip;
        $orderparam['uid'] = $user['uid'];
        $orderparam['payfrom'] = $this->payFrom[$this->paytype];
        $orderparam['couponcode'] = ''; //优惠码
        $ordername = '';	//订单名称
        $remark = '';		//订单备注
        $totalfee = 0;	//订单总额
        $comfee = 0;	//公司分到总额
        $roomfee = 0;	//平台分到总额
        $providerfee = 0;	//内容提供商分到总额

        $cw['fee'] = $cwdetail['cprice'];
        $cw['comfee'] = $cwdetail['comfee'];
        $cw['roomfee'] = $cwdetail['roomfee'];
        $cw['oname'] = $cwdetail['title'];
        $cw['omonth'] = $cwdetail['cmonth'];
        $cw['oday'] = $cwdetail['cday'];
        $cw['osummary'] = $cwdetail['summary'];
        $cw['uid'] = $user['uid'];
        $cw['rname'] = $cwdetail['crname'];
        $cw['folderid'] = $cwdetail['folderid'];
        $cw['crid'] = $cwdetail['crid'];
        $cw['cwid'] = $cwdetail['cwid'];
        $cw['domain'] = $cwdetail['domain'];
        $remark = $cw['oname'].'_'.(empty($cw['omonth']) ? $cw['oday'].' 天 _':$cw['omonth'].' 月 _').$cw['fee'].' 元';

        $itemlist = array($cw);
        $orderparam['crid'] = $cw['crid'];
        $orderparam['cwid'] = $cw['cwid'];
        // $orderparam['providercrid'] = $itemlist[0]['providercrid'];	//来源平台crid
        // $orderparam['pid'] = $pid;
        $orderparam['itemlist'] = $itemlist;
        $orderparam['totalfee'] = $cw['fee'];	//订单总额
        $orderparam['comfee'] = $cw['comfee'];	//公司分到总额
        $orderparam['roomfee'] = $cw['roomfee'];	//平台分到总额
        // $orderparam['providerfee'] = $providerfee;
        $orderparam['ordername'] = '开通 '.$cw['oname'].' 服务';
        $orderparam['remark'] = $remark;
        $payOrderModel = new PayorderModel();
        $orderId = $payOrderModel->addOrder($orderparam);

        if(!$orderId || $orderId <= 0){
            return returnData(0,'创建订单失败');
        }

        $orderparam['orderid'] = $orderId;
        return $this->payment($orderparam,$this->parameters);
    }

    /**
     * 课程包订单  (必须由buildOrderAction调用)
     * @param $user
     * @param $bid
     * @param $roominfo
     * @return array
     */
    public function buildOrderBundle($user,$bid,$roominfo){
        $bundle = runAction('CourseService/Bundle/detail',array('bid'=>$bid,'uid'=>$user['uid']));
        //课程包设置了限制报名时,查询开通人数
        if(!empty($bundle['islimit']) && $bundle['limitnum']>0){
            $openlimit = new OpenLimit();
            $openstatus = $openlimit->checkStatus($bundle,$user['uid']);

            if(!$openstatus){//状态设置为无法报名
                return returnData(0,'你不能报名该课程');
            }
        }
        if (empty($bundle)  || !empty($bundle['cannotpay']) || empty($roominfo) || $bundle['crid'] != $roominfo['crid']) {
            //只能生成本网校的课程包订单
            return returnData(0,'只能生成本网校的课程包订单');
        }
        $classroomModel = new ClassRoomModel();
        $roominfo = $classroomModel->getclassroomdetail($bundle['crid']);
        $profitratio = unserialize($roominfo['profitratio']);
        $orderparam = array();
        $orderparam['bid'] = $bid;
        $orderparam['dateline'] = SYSTIME;
        $orderparam['ip'] = $this->ip;
        $orderparam['uid'] = $user['uid'];
        $orderparam['payfrom'] = $this->payFrom[$this->paytype];
        $orderparam['couponcode'] = ''; //优惠码
        $orderparam['ordername'] = '开通'.$bundle['name'].'服务';	//订单名称
        $orderparam['remark'] = $bundle['name'].'课程包，价格：'.$bundle['bprice'].'元';		//订单备注
        $orderparam['totalfee'] = $bundle['bprice'];	//订单总额
        if (!empty($profitratio)) {
            $profitratio['baseTotal'] = $baseTotal = array_sum($profitratio);
            $orderparam['comfee'] = round($bundle['bprice'] * $profitratio['company'] / $baseTotal, 2);
            $orderparam['providerfee'] = round($bundle['bprice'] * $profitratio['agent'] / $baseTotal, 2);
            $orderparam['roomfee'] = $bundle['bprice'] - $orderparam['comfee'] - $orderparam['providerfee'];
        }

        $orderparam['crid'] = $roominfo['crid'];
        $orderparam['cwid'] = 0;
        $orderparam['providercrid'] = $bundle['crid'];	//来源平台crid
        $orderparam['pid'] = $bundle['pid'];
        $orderparam['itemlist'] = array_map(function($course) {
            return array(
                'itemid' => $course['itemid'],
                'cwid' => 0,
                'pid' =>$course['pid'],
                'folderid' => $course['folderid'],
                'omonth' => $course['imonth'],
                'oday' => $course['iday'],
                'oname' => $course['iname'],
                'iprice' => $course['iprice']
            );
        }, $bundle['courses']);
        $iprices = array_column($bundle['courses'], 'iprice');
        $acount = array_sum($iprices);
        unset($iprices);
        //包中课程的价格按比例重新换算
        array_walk($orderparam['itemlist'], function(&$item, $k, $args) {
            $item['uid'] = $args['uid'];
            $item['rname'] = $args['roominfo']['crname'];
            $item['osummary'] = $args['bundlename'].'-'.$item['oname'].(!empty($item['omonth']) ? $item['omonth'].'月' : $item['oday'].'天');
            $item['fee'] = round($item['iprice'] * $args['bprice'] / $args['acount'], 2);
            $item['comfee'] = round($item['fee'] * $args['profitratio']['company'] / $args['profitratio']['baseTotal'], 2);
            $item['providerfee'] = round($item['fee'] * $args['profitratio']['agent'] / $args['profitratio']['baseTotal'], 2);
            $item['roomfee'] = $item['fee'] - $item['comfee'] - $item['providerfee'];
            $item['domain'] = $args['roominfo']['domain'];
        }, array(
            'uid' => $user['uid'],
            'roominfo' => $roominfo,
            'bundlename' => $bundle['name'],
            'acount' => $acount,
            'bprice' => $bundle['bprice'],
            'profitratio' => $profitratio
        ));
        $payOrderModel = new PayorderModel();
        $orderId = $payOrderModel->addOrder($orderparam);

        if(!$orderId || $orderId <= 0){
            return returnData(0,'创建订单失败');
        }

        $orderparam['orderid'] = $orderId;
        return $this->payment($orderparam,$this->parameters);
    }

    /**
     * 生成支付代码
     * @param $orderparam
     * @return array
     */
    private function payment($orderparam,$parameters){

        $transaction = new Transaction($this->paytype);

        $payObj = $transaction->getObj();
        $attach = md5($orderparam['uid'].'_'.$orderparam['orderid']);
        //商户订单号
        $out_trade_no = $orderparam['orderid'];
        //订单名称
        $subject = $orderparam['ordername'];
        $subject = shortstr($subject,80,'');
        //付款金额
        $total_fee = $orderparam['totalfee'];
        //订单描述
        $body = $orderparam['remark'];
        $body = shortstr($body,80,'');
        $parameters['out_trade_no']  = $out_trade_no;
        $parameters['subject'] = $subject;
        $parameters['total_fee'] = $total_fee;
        $parameters['body'] = $body;
        $parameters['notify_url'] = 'notify_url';
        $parameters['attach'] = $attach;

        $payResult = $payObj->getPaymentCode($orderparam,$parameters);

        $paymentConfig = Ebh()->config->get('payment');
        return returnData(1,'',array(
            'pay_data'   =>  $payResult,
            'runtype'   =>  $paymentConfig[$this->paytype]['runtype']
        ));
    }


    public function notifyAction(){
        $transaction = new Transaction($this->paytype);

        $payObj = $transaction->getObj();

        $payObj->notify();
        exit;
    }


    /**
     * 创建一个订单
     * @return array
     */
    public function orderAction(){

        $userModel = new UserModel();
        $userInfo = $userModel->getUserByUid($this->uid);

        if(!$userInfo){
            return returnData(0,'读取用户信息错误');
        }

        //如果提交了优惠券
        $iscoupon = false;
        if($this->couponcode != ''){
            $couponsModel = new CouponsModel();
            $coupon = $couponsModel->getOne(array('code'=>$this->couponcode));
            if(!empty($coupon) && ($coupon['uid'] != $this->uid)){
                $iscoupon = true;
            }
        }
        $couponcode = $iscoupon ? $this->couponcode : '';
        //优惠券判断结束
        $payitemModel = new PayitemModel();
        $parameters = array(
            'itemidlist'    =>  implode(',',$this->itemid)
        );
        //读取用户希望购买的产品列表
        $itemList = $payitemModel->getItemList($parameters);

        if(empty($itemList)){
            return returnData(0,'读取产品信息失败');
        }

        ///////////////////////////////////////////////////////////////////
        //当本校免费课程时用户是本校学生，价格置0
        $roomuserModel = new RoomUserModel();
        $is_alumni = $roomuserModel->isAlumni($this->crid, $this->uid);
        if ($is_alumni) {
            $folderid_arr = array_column($itemList, 'folderid');
            $folderid_arr = array_unique($folderid_arr);
            $folderModel = new FolderModel();
            $folder_arr = $folderModel->getSchoolFreeFolderidList($folderid_arr);
        }
        ////////////////////////////////////////////////////////////////////

        //定义订单数组
        $orderParameters = array();
        $orderParameters['dateline'] = SYSTIME;
        $orderParameters['ip'] = $this->ip;
        $orderParameters['uid'] = $this->uid;
        $orderParameters['crid'] = $this->crid;
        $orderParameters['payfrom'] = $this->payFrom[$this->paytype];
        $orderParameters['paytype'] = $this->paytype;
        $orderParameters['couponcode'] = !empty($couponcode) ? $couponcode : ''; //优惠码
        $ordername = '';	//订单名称
        $remark = '';		//订单备注
        $totalfee = 0;	//订单总额
        $comfee = 0;	//公司分到总额
        $roomfee = 0;	//平台分到总额
        $providerfee = 0;	//内容提供商分到总额
        $flodernum = count($itemList);                        //获取订单课程数量
        $discount = $this->getDiscountByFolderNum($flodernum);//根据课程数量获取折扣//

        for($i = 0; $i < count($itemList); $i ++) {
            if (!empty($folder_arr) && in_array($itemList[$i]['folderid'], $folder_arr)) {
                $itemList[$i]['iprice'] = 0;
                $itemList[$i]['comfee'] = 0;
                $itemList[$i]['roomfee'] = 0;
                $itemList[$i]['providerfee'] = 0;
                $itemList[$i]['iprice_yh'] = 0;
                $itemList[$i]['comfee_yh'] = 0;
                $itemList[$i]['roomfee_yh'] = 0;
                $itemList[$i]['providerfee_yh'] = 0;
            }

            $itemList[$i]['fee'] = $itemList[$i]['iprice']*$discount;
            $itemList[$i]['oname'] = $itemList[$i]['iname'];
            $itemList[$i]['omonth'] = $itemList[$i]['imonth'];
            $itemList[$i]['oday'] = $itemList[$i]['iday'];
            $itemList[$i]['osummary'] = $itemList[$i]['isummary'];
            $itemList[$i]['uid'] = $this->uid;
            $pid = $itemList[$i]['pid'];
            $itemList[$i]['rname'] = $itemList[$i]['crname'];
            //如果该课程参加了优惠并且使用优惠券处理
            if($itemList[$i]['isyouhui'] && !empty($couponcode)){
                $itemList[$i]['fee'] = $itemList[$i]['iprice_yh'];
                $itemList[$i]['comfee'] = $itemList[$i]['comfee_yh'];
                $itemList[$i]['roomfee'] = $itemList[$i]['roomfee_yh'];
                $itemList[$i]['providerfee'] = $itemList[$i]['providerfee_yh'];
                $totalfee += $itemList[$i]['iprice_yh'];
            }else{
                $itemList[$i]['comfee'] = $itemList[$i]['comfee']*$discount;
                $itemList[$i]['roomfee'] = $itemList[$i]['roomfee']*$discount;
                $itemList[$i]['providerfee'] = $itemList[$i]['providerfee']*$discount;//*****订单详情，每个商品都变成折后价*******
                $totalfee += $itemList[$i]['iprice'];
            }

            $comfee += $itemList[$i]['comfee'];
            $roomfee += $itemList[$i]['roomfee'];
            $providerfee += $itemList[$i]['providerfee'];

            if(empty($ordername)){
                $ordername = $itemList[$i]['oname'];
            }else{
                $ordername .= ','.$itemList[$i]['oname'];
            }

            $theremark = $itemList[$i]['iname'].'_'.(empty($itemList[$i]['omonth']) ? $itemList[$i]['oday'].' 天 _':$itemList[$i]['omonth'].' 月 _').$itemList[$i]['fee'].' 元';
            if(empty($remark)) {
                $remark = $theremark;
            }else{
                $remark .= '/'.$theremark;
            }

            $providercrid = $itemList[$i]['providercrid'];
        }
        $orderParameters['crid'] = $itemList[0]['crid'];
        $orderParameters['providercrid'] = $itemList[0]['providercrid'];	//来源平台crid
        $orderParameters['pid'] = $pid;
        $orderParameters['itemlist'] = $itemList;
        $orderParameters['totalfee'] = $totalfee*$discount;//**折后价**
        $orderParameters['comfee'] = $comfee;
        $orderParameters['roomfee'] = $roomfee;
        $orderParameters['providerfee'] = $providerfee;
        $orderParameters['ordername'] = '开通 '.$ordername.' 服务';
        $orderParameters['remark'] = $remark;

        //查看企业选课,是则替换价格和期限
        if(count($this->itemid) == 1){
            $schsourceModel = new SchsourceModel();
            $schsourceitem = $schsourceModel->getSelectedItems(array('crid'=>$this->crid,'itemid'=>$this->itemid[0]));

            if(!empty($schsourceitem)){
                foreach($schsourceitem as $si){
                    $orderParameters['totalfee'] = $si['price'];
                    $orderParameters['omonth'] = $si['month'];
                    $orderParameters['crid'] = $si['crid'];
                    $orderParameters['providercrid'] = $si['sourcecrid'];
                    $orderParameters['isschsource'] = 1;
                    $orderParameters['comfee'] = 0;
                    $orderParameters['roomfee'] = 0;
                    $orderParameters['providerfee'] = 0;
                }
            }
        }



        $payOrderModel = new PayorderModel();
        $orderId = $payOrderModel->addOrder($orderParameters);
        if(!$orderId || $orderId <= 0){
            return returnData(0,'创建订单失败');
        }

        $orderParameters['orderid'] = $orderId;
        $transaction = new Transaction($this->paytype);

        $payObj = $transaction->getObj();
        $payResult = $payObj->getPaymentCode($orderParameters,$this->parameters);
        return returnData(1,'',array(
            'pay_data'   =>  $payResult
        ));

    }


    /**
     * 根据课程数量计算折扣
     * @param $foldernum
     * @return int
     */
    private function getDiscountByFolderNum($foldernum){
        $classroomModel = new ClassRoomModel();
        $systeminfo = $classroomModel->getSystemSetting($this->crid);
        $discount = 1;
        if($systeminfo['iscollect']){//开启了打折
            $num = $foldernum;      //课程数量
            $disarr = json_decode($systeminfo['discounts']);         //折扣列表(数组)
            array_multisort($disarr,SORT_ASC,$disarr);
            $count = count($disarr);
            if($count) {         //判断是否设置折扣
                $nummax = $disarr[$count-1][0];
                $nummin = $disarr[0][0];
                if($nummax < $num){
                    $discount = $disarr[$count-1][1];
                }
                if($nummin<=$num && $nummax >= $num){
                    $count = 0;
                    foreach($disarr as $v){
                        $count++;
                        if($v[0] > $num){
                            $discount = $disarr[$count-2][1];
                            break;
                        }
                        if($v[0] == $num){
                            $discount = $disarr[$count-1][1];
                            break;
                        }
                    }
                }
            }
        }

        return $discount;
    }

    /**
     * 对分销的参数解析
     */
    function parse_sharekey($sharekey) {
        if (empty($sharekey)) {
            return false;
        }
        $sharekey = str_replace(' ', '+', $sharekey);
        $sharekey = explode('%',authcode($sharekey, 'DECODE'));
        return $sharekey;
    }

    /**
     *判断是否存在开通服务后问卷，验证问卷有效性，并设置订单缓存用于支付成功后获取
     * @return surveysid调查问卷id
     */
    private function checkSurveySid($param){
        $itemcount = intval($param['count']);
        $folderid = intval($param['folderid']);
        $orderid = intval($param['orderid']);
        $crid = intval($param['crid']);
        $uid = intval($param['uid']);
        $result = false;
        //重置当前用户ebhservice中设置的开通服务后问卷缓存
        $redis = Ebh()->cache->getRedis();
        $redis_key = 'payordersurvey_' . $crid . '_' . $uid;
        $redis->delete($redis_key);
        //1判断当前课程folderid是否存在,并且不是服务包
        if(empty($itemcount) || ($itemcount != 1) || empty($folderid) || empty($crid) || empty($uid) || empty($orderid)){
            return false;
        }
        //2读取缓存中开通服务后问卷id
        $redis_key = 'payitemsurvey_' . $crid . '_' . $folderid;
        $surveyinfo = $redis->get($redis_key);//读取缓存中开通服务后调查问卷信息
        if (!empty($surveyinfo)) {
            $surveyinfo = json_decode($surveyinfo, true);
            $surveysid = (!empty($surveyinfo['sid']) && ($surveyinfo['sid'] > 0)) ? intval($surveyinfo['sid']) : 0;//问卷id
            $surveycrid = !empty($surveyinfo['crid']) ? $surveyinfo['crid'] : 0;
            $surveyfolderid = !empty($surveyinfo['folderid']) ? $surveyinfo['folderid'] : 0;    //问卷对应的课程id
        //3获取问卷状态,调查问卷为已发布,未超时,未删除且用户未回答过则返回true,否则false
            if (!empty($surveysid) && ($surveycrid == $crid) && ($surveyfolderid == $folderid)) {
                $surveyparam = array('sid' => $surveysid, 'uid' => $uid, 'crid' => $surveycrid, 'type' => 6);
                $surveyModel = new SurveyModel();
                $check = $surveyModel->getSurveyStatus($surveyparam);;
                if ($check) {
        //4添加问卷sid和订单orderid到临时缓存中，在支付成功后的可获取,有效期5分钟
                    $redis_key = 'payordersurvey_' . $crid . '_' . $uid;
                    $ordersurvey = json_encode(array('surveysid'=>$surveysid,'orderid'=>$orderid,'folderid'=>$folderid));
                    $redis->set($redis_key, $ordersurvey, 300);
                    $result = $surveysid;
                }
            }
        }
        return $result;
    }
}
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
        'wxapppay'  =>  9,
        'balance'   =>  8
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
            'notifyAction'  =>  array(
                'paytype'  =>  array('name'=>'paytype','require'=>true,'type'=>'enum','range'=>Ebh()->config->get('payment.paytype')),//支付方式 wxapppay=小程序
            )
        );
    }


    public function notifyAction(){
        $transaction = new Transaction($this->paytype);

        $payObj = $transaction->getObj();

        $payObj->notify();
        exit;
    }



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
}
<?php
/**
 * 网校装扮
 * Author: eker
 * Email: eker-huang@outlook.com
 */
class DesignController extends Controller{
    public function parameterRules(){
        return array(
            'saveAction'   =>  array(
                'did' => array('name' => 'did', 'type' => 'int', 'default' => 0),
                'uid'   =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
                'head'  =>  array('name'=>'head','require'=>true,'type'=>'string'),
                'foot'  =>  array('name'=>'foot','require'=>true,'type'=>'string'),
                'body'  =>  array('name'=>'body','require'=>true,'type'=>'string'),
                'roomtype'  =>  array('name'=>'roomtype','require'=>true,'type'=>'string'),
                'settings' => array('name'=>'settings','default'=>''),
                'status'  =>  array('name'=>'status','type'=>'int','default'=>0),
                'clientType' => array('name' => 'clientType', 'type' => 'int', 'default' => 0)
            ),
            'getdesignAction' =>array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
                'roomtype' => array('name'=>'roomtype','require'=>true,'type'=>'string'),
                'clientType' => array('name' => 'clientType', 'type' => 'int', 'default' => 0),
                'did' => array('name' => 'did', 'type' => 'int', 'default' => 0)
            ),
            'setfresscourseAction' => array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'did' => array('name'=>'did','require'=>true,'type'=>'int','min'=>1),
                'cwid' => array('name'=>'cwid','require'=>true,'type'=>'string'),
            ),
            'resetAction' => array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
            ),
            'getroominfoAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            'getcoursecategorysAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
             'getWeatherAction' => array(
                'city' => array(
                    'name' => 'city',
                    'type' => 'string',
                    'require' => true
                ),
                'day' => array(
                    'name' => 'day',
                    'type' => 'int'
                )
            ),
            'getdesigntemplatesAction' => array(
                'clientType' => array('name' => 'clientType', 'type' => 'int', 'default' => 0),
                'num' => array('name' => 'num', 'type' => 'int', 'default' => 0),
                'istop' => array('name' => 'istop', 'type' => 'int', 'default' => 0, 'min' => 0, 'max' => 1)
            ),
            'savePreviewAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                ),
                'preview' => array(
                    'name' => 'preview',
                    'type' => 'string'
                )
            )
        );
    }
    
    /**
     * 网校配置着数据保存
     */
    public function saveAction(){
       // log_message(1111);
        $ck = false;
        $roomModel = new ClassRoomModel();
        //$newroomtype = $roomModel->getRoomType($this->crid);
        //先查询是否存在
        $designModel = new DesignModel();
        $design = $designModel->getDesignByCrid($this->crid, $this->roomtype, $this->clientType, $this->did);
        $param = array(
            'uid'=>$this->uid,
            'crid'=>$this->crid,
            'roomtype'=>$this->roomtype,
            'head'=>$this->head,
            'foot'=>$this->foot,
            'body'=>$this->body,
            'settings'=>$this->settings,
            'status'=>$this->status,
            'client_type' => $this->clientType
        );

        if(empty($design)){
            //新增
            //$param['checked'] = 1;
            $did = $ck = $designModel->addDesign($param);
        }else{
            //修改
            $param['did'] = $design['did'];
            $param['status'] = 0;
            $ck = $designModel->editDesign($param, $this->crid, $this->clientType);
            $did = $design['did'];
        }
        if($ck){
            //修改网校装扮类型字段
            //$roomModel->update($this->crid,array('isdesign'=>1));
            return returnData(1,'保存成功', $did);
        }else{
            return returnData(0,'保存失败');
        }
    }
    
    /**
     * 获取网校配置
     */
    public function getdesignAction(){
        $crid = $this->crid;
        $roomtype = $this->roomtype;
        if($crid<=0 && in_array($roomtype, array('com','edu'))===false){
            return returnData(0,'参数错误',array());
        }        
        $designModel = new DesignModel();
        $design = $designModel->getDesignByCrid($crid,$roomtype,$this->clientType, $this->did);
        if(!empty($design)){
            return returnData(1,'',$design);
        }else{
            return returnData(1,'',array());
        }
    }
    
    /**
     * 设置免费试听
     */
    public function setfresscourseAction(){
        $did = $this->did;
        $cwid = $this->cwid;
        if(strpos($cwid, ',') !== false){
            $cwidarr = explode(',', $cwid);
        }else{
            $cwidarr = array($cwid);
        }

        $cwidarr = array_map('intval', $cwidarr);
        $cwidarr = array_filter($cwidarr, function($cwid) {
            return $cwid > 0;
        });
        if (empty($cwidarr)) {
            return returnData(0,'请求参数错误', array());
        }
        $cwidarr = array_unique($cwidarr);
        $model = new DesignCoursewareModel();
        $ck = $model->setDesignCoursewares($cwidarr, $this->crid, $did, DesignCoursewareModel::FREE);
        if(!empty($ck)){
            return returnData(1);
        }else{
            return returnData(0,'请求接口错误,操作未成功', array());
        }
    }
    
    /**
     * 重置网校装扮
     */
    public function resetAction(){
        $crid = $this->crid;
        $roomModel = new ClassRoomModel();
        $ck =$roomModel->update($crid,array('isdesign'=>0));
        if(!empty($ck)){
            return returnData(1);
        }else{
            return returnData(0,'请求接口错误,操作未成功',array());
        }
    }

    /**
     * 获取网校基本信息
     * @return array
     */
    public function getroominfoAction() {
        $roomModel = new ClassRoomModel();
        $roominfo = $roomModel->getModel($this->crid);
        if (!empty($roominfo)) {
            //将自定义标签转为一维数组
            if ($roominfo['crlabel'] != '') {
                $roominfo['crlabel'] = explode(',', $roominfo['crlabel']);
                $roominfo['crlabel'] = array_filter($roominfo['crlabel'], function($crlabel) {
                   return $crlabel != '';
                });
            }
            //将客服qq转为数组，客服名称与qq数量不一致则清除数据
            $qqlabels = explode(',', $roominfo['kefu']);
            $qqs = explode(',', $roominfo['kefuqq']);
            unset($roominfo['kefu'], $roominfo['kefuqq']);
            if (!empty($qqlabels) && count($qqlabels) == count($qqs)) {
                $roominfo['kefuqq'] = array();
                foreach ($qqlabels as $k => $v) {
                    $roominfo['kefuqq'][] = array(
                        'kefu' => $v,
                        'qq' => $qqs[$k]
                    );
                }
            }
            //将时间戳转为时间
            if ($roominfo['begindate'] > 0) {
                $roominfo['begindate'] = date('Y-m-d H:i:s', $roominfo['begindate']);
            }
            if ($roominfo['enddate'] > 0) {
                $roominfo['enddate'] = date('Y-m-d H:i:s', $roominfo['enddate']);
            }
        }
        return $roominfo;
    }

    /**
     * 获取网校课程分类列表
     */
    public function getcoursecategorysAction() {
        $itemModel = new PayitemModel();
        $schoolItems = $itemModel->getSchoolItems($this->crid);
        $otherItems = $itemModel->getSchItems($this->crid);
        $pids = $sids = $others = array();
        foreach ($schoolItems as $item) {
            if (!isset($pid[$item['pid']])) {
                $pids[$item['pid']] = $item['pid'];
            }

            if ($item['sid'] == 0 && !isset($others[$item['pid']])) {
                $others[$item['pid']] = true;
            } else if($item['sid'] > 0 && !isset($sids[$item['sid']])) {
                $sids[$item['sid']] = $item['pid'];
            }
        }
        foreach ($otherItems as $item) {
            if (!isset($pid[$item['pid']])) {
                $pids[$item['pid']] = $item['pid'];
            }
            if ($item['sid'] == 0 && !isset($others[$item['pid']])) {
                $others[$item['pid']] = true;
            } else if($item['sid'] > 0 && !isset($sids[$item['sid']])) {
                $sids[$item['sid']] = $item['pid'];
            }
        }
        unset($schoolItems, $otherItems);
        $packModel = new PaypackageModel();
        $packages = $packModel->getPackageMenus($pids, $this->crid);
        if (empty($packages)) {
            return array();
        }
        $pid = key($packages);
        $firstPackage = $packages[$pid];
        array_walk($packages, function(&$package) {
           unset($package['displayorder'], $package['t']);
        });
        $sorts = array();
        if ($firstPackage['displayorder'] != -1) {
            $packages[$pid]['cur'] = true;
            //加载第一个包的分类
            $sids = array_filter($sids, function($pid) use($firstPackage) {
                return $firstPackage['pid'] == $pid;
            });
            $sids = array_keys($sids);
            $sortModel = new PaysortModel();
            $sorts = $sortModel->getSortMenuList($sids, $firstPackage['pid']);
            //判断是否有未分配分类的课程
            if (isset($others[$firstPackage['pid']])) {
                $sorts[] = array(
                    'sid' => 0,
                    'sname' => '其他课程',
                    'pid' => $firstPackage['pid']
                );
            }
            //全部
            if (count($sorts) > 1) {
                array_unshift($sorts, array(
                    'sid' => -1,
                    'sname' => '全部',
                    'pid' => $firstPackage['pid']
                ));
            }
            $sid = key($sorts);
            $sorts[$sid]['cur'] = true;
        }
        if (count($packages) > 1) {
            $allpackage = array(
                'pid' => 0,
                'pname' => '全部'
            );
            if ($firstPackage['displayorder'] == -1) {
                $allpackage['cur'] = true;
            }
            array_unshift($packages, $allpackage);
        }
        return array(
            'packages' => array_values($packages),
            'sorts' => array_values($sorts)
        );
    }

    /**
     *获取天气情况接口
     */
    public function getWeatherAction() {
        $weatherserver = Ebh()->config->get('weatherserver');
        $weatherUrl = $weatherserver['server'][0];
        $weatherUrl .= '&';
        $day = $this->day;
        $param['day'] = empty($day) ? 0 : $day;
        //获取城市名字
        $param['city'] = mb_convert_encoding($this->city,'gb2312','utf-8');
        $data = array();
        $url = $weatherUrl.http_build_query($param);
        $res = do_post($url,$data);
        $xml_array = simplexml_load_string($res); //将XML中的数据,读取到数组对象中
        $resdata[] =  $xml_array->Weather;
        if (empty($day)) {
            $param['day'] = 1;
            $weatherUrl .= http_build_query($param);
            $res = do_post($weatherUrl,$data);
            $xml_array = simplexml_load_string($res);
            $resdata[] =  $xml_array->Weather;
        }
        return $resdata;
    }

    /**
     * 获取装扮列表
     */
    public function getdesigntemplatesAction() {
        $model = new DesignModel();
        return $model->getDesignTemplateList($this->clientType, $this->istop, $this->num);
    }

    /**
     * 设置装扮预览图
     * @return mixed
     */
    public function savePreviewAction() {
        $model = new DesignModel();
        return $model->setPreview($this->did, $this->preview, $this->crid);
    }
}
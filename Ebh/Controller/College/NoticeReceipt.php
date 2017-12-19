<?php
/**
 * ebhservice.
 * Author: tyt
 * Email: 345468755@qq.com
 * 消息通知查看接口
 */
class NoticeReceiptController extends Controller{
    public $receipt;

    public function init(){
        parent::init();
        $this->receipt = new NoticeModel();
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','default' => 0,'type'=>'int'),
                'noticeid'  =>  array('name'=>'noticeid','type'=>'int','require'=>true),
                'isreceipt'  =>  array('name'=>'isreceipt','type'=>'int','default' => 0),
                'ntype'  =>  array('name'=>'ntype','default' => 2,'type'=>'int'),//通知类型,1为全校师生 2为全校教师 3为全校学生 4为班级学生
                'choice'  =>  array('name'=>'choice','type'=>'int'),//可能是筛选的
                'q'  =>  array('name'=>'q','default'=>''),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>0)
            ),
            'noviewslistAction'  =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','default' => 0,'type'=>'int'),
                'isreceipt'  =>  array('name'=>'isreceipt','type'=>'int','default' => 0),
                'export'  =>  array('name'=>'export','type'=>'int','default' => 0),//是否导出cvs
                'ntype'  =>  array('name'=>'ntype','default' => 2,'type'=>'int'),//通知类型,1为全校师生 2为全校教师 3为全校学生 4为班级学生
                'classid'  =>  array('name'=>'uid','default' => 0,'type'=>'int'),//预留
                'gradeid'  =>  array('name'=>'gradeid','default' => 0,'type'=>'int'),//预留
                'noticeid'  =>  array('name'=>'noticeid','type'=>'int','default'=>0),
                'q'  =>  array('name'=>'q','default'=>''),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>0)
            ),
            'detailAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),
                'crid'  =>  array('name'=>'crid','type'=>'int','default'=>0),
                'receiptid'  =>  array('name'=>'receiptid','type'=>'int','default'=>0),
                'noticeid'  =>  array('name'=>'noticeid','type'=>'int','default'=>0)
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'noticeid'  =>  array('name'=>'noticeid','require'=>true),
                'choice'  =>  array('name'=>'choice','type'=>'int','require'=>true),
                'explains'  =>  array('name'=>'explains','default'=>'')
            )
        );
    }

    /**
     * 添加查看
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['noticeid'] = $this->noticeid;
        $parameters['choice'] = $this->choice;
        $parameters['explains'] = $this->explains;
        return $this->receipt->addReceipt($parameters);
    }

    /**
     * 获取总的已经的查看列表
     * @return array
     */
     public function listAction(){
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        $filterParams['noticeid'] = $this->noticeid;
        if ($this->uid) {
            $filterParams['uid'] = $this->uid;
        }
        if (!empty($this->q)) {
            $filterParams['q'] = $this->q;
        }
        if (isset($this->choice)) {
            $filterParams['choice'] = $this->choice;
        }
        if ($this->ntype == 2) {
            $filterParams['groupid'] = 5;
        } elseif ($this->ntype == 3) {
            $filterParams['groupid'] = 6;
        }
        $res = $this->receipt->getListAndTotalPage($filterParams, array(
            'pagesize' => $this->pagesize,
            'page' => $this->page
        ), false);
        if (!empty($res['list'])) {
            //把用户的信息追加
            $this->bulidUserInfo($res);
        }
        return $res;
        
    }
    
    /**
     * 获取总的没有查看的列表人头，//逻辑，先按照类型，关联老师学生，走不同的逻辑，关联全校的情况，去roomusers 表取出,剔除已经查看人的列表，考虑用不同的表not in (uids集合);子查询的效率也不是很高。目前只有考虑到not in 
     * @return array
     */
    public function noviewslistAction(){
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        $filterParams['noticeid'] = $this->noticeid;
        //已经查看的所有人
        $receiptList = array();
        //学生或者老师
        if ($this->ntype == 2) {
            $filterParams['groupid'] = 5;
        } elseif ($this->ntype == 3) {
            $filterParams['groupid'] = 6;
        }
        $receiptList = $this->receipt->getReceiptDetail($filterParams);
        //构建所有已查看的人的id
        $filterUids = '';
        if (!empty($receiptList)) {
            //array_colomun();
            foreach ($receiptList as $key => $value) {
               $filterUids .= $value['uid'].',';
            }
        }
        if ($this->ntype == 1) {//全校学生和学生
            //此处考虑到性能，暂不考虑,后续优化users表，使用存储过程或者spinx会考虑使用
        } elseif ($this->ntype == 3) {//全校学生
            //去学生表查询 not in 范围
            $tmodel = new RoomUserModel();
            $res['list'] = $tmodel->getUserIdListFilter($this->crid,$this->page,$this->pagesize,substr($filterUids, 0, -1),$this->export);
            //是否导出cvs，不是全部导出
            if (!$this->export) {//不是导出的则需要分页
                $res['totalpage'] = $tmodel->getUserIdListFilterCount($this->crid,substr($filterUids, 0, -1));
            }
        } elseif ($this->ntype == 2) {//全校的老师
            //去教师表查询 not in 范围
            $tmodel = new RoomTeacherModel();
            $res['list'] = $tmodel->getTeacheIdListFilter($this->crid,$this->page,$this->pagesize,substr($filterUids, 0, -1),$this->export);
            //小于1000说明是需要分页的，不是全部导出
            if (!$this->export) {
                $res['totalpage'] = $tmodel->getTeacheIdListFilterCount($this->crid,substr($filterUids, 0, -1));
            }
        }

        if ($this->export) {//导出用
            //导出情况需要合并数组，未查看的人和查看的人merge
            $res['list'] = array_merge($receiptList,$res['list']);
        }

        if (!empty($res['list'])) {
            //把用户的信息追加
            $this->bulidUserInfo($res);
        }
        return $res;
    }


   
    /**
     * 查看详情
     * @return mixed
     */
    public function detailAction() {
        $filterParams['crid'] = $this->crid;
        $filterParams['uid'] = $this->uid;
        if ($this->noticeid) {
            $filterParams['noticeid'] = $this->noticeid;
        }
        $detail = $this->receipt->getReceiptDetail($filterParams);
        return $detail;
    }


    /**
     *构建用户信息的函数，包括班级
     *@param $result,返回list包括uid
     */
    public function bulidUserInfo(&$result,$defaultKey='uid') {
        if (!empty($result['list'])) {
            $uidsArr = array();
            foreach ($result['list'] as $value) {
                $uidsArr[] = $value[$defaultKey];
            }
            //获取用户的信息追加
            $uids = implode(',', array_unique($uidsArr));
            $userModel = new UserModel();
            $userinfos = $userModel->getUserInfoByUid($uids);

            //只是老师的情况没有班级的不需要查班级表
            if (!empty($this->ntype) && $this->ntype == 2) {
                $classinfos = array();
            } else {
                $classesModel = new ClassesModel();
                $classinfos = $classesModel->getClassInfoByCrid($this->crid,array_unique($uidsArr));
            }

            //数据赋值
            if (!empty($classinfos)) {
                foreach ($classinfos as $cvalue) {
                    $user_classes[$cvalue['uid']] = $cvalue;
                }
            }
            if (!empty($userinfos)) {
                foreach ($userinfos as $uvalue) {
                    $user_infos[$uvalue['uid']] = $uvalue;
                }
                foreach ($result['list'] as &$rvalue) {
                    if (isset($user_infos[$rvalue[$defaultKey]])) {//把用户信息追加
                        $rvalue['username'] = $user_infos[$rvalue[$defaultKey]]['username'];
                        $rvalue['realname'] = $user_infos[$rvalue[$defaultKey]]['realname'];
                        $rvalue['sex'] = $user_infos[$rvalue[$defaultKey]]['sex'];
                        $rvalue['face'] = $user_infos[$rvalue[$defaultKey]]['face'];
                        $rvalue['class'] = empty($user_classes[$rvalue[$defaultKey]]['classname'])?'':$user_classes[$rvalue[$defaultKey]]['classname'];
                    }
                }
            }
        }
        return $result;
    }
}
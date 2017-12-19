<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:32
 */
class CreditController extends Controller{
    public function parameterRules(){
        return array(
            'infoAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            //学生积分排行榜
            'rankAction' => array(
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'require' => true
                ),
                'orderType' => array(
                    'name' => 'orderType',
                    'type' => 'int',
                    'default' => 0
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 1
                )
            )
        );
    }

    /**
     * 指定用户积分统计
     */
    public function infoAction(){
        $userModel = new UserModel();
        $userInfo = $userModel->getUserByUid($this->uid);

        if(!$userInfo){
            return returnData(0,'用户不存在');
        }



        /**
         * 读取签到次数
         */
        $param = array();
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $signlogmodel = new SignLogModel();
        $signCount =  $signlogmodel->getSignCount($param);

        //获取网校积分排行

        $roomUserModel = new RoomUserModel();
        $rank = $roomUserModel->getRoomUserCreditRank($this->crid);
        $myrank = $roomUserModel->getRoomUserRankByUid($this->crid,$this->uid);


        $creditLevel = Ebh()->config->get('creditlevel');

        $result['credit'] = $userInfo['credit'];
        $result['sign_count'] = $signCount;


        $playlogMoldel = new PlayLogModel();


        $result['course_count'] = $playlogMoldel->getCourseCountByUid(array('crid'=>$this->crid,'uid'=>$this->uid));
        $result['rank'] = $rank;
        $result['myrank'] = $myrank;
        $result['credit_level'] = $creditLevel;

        //获取用户已做作业对应分类的数量
        $result['examType'] = array();
        $schestype = new SchestypeModel();
        $esTypes = $schestype->getEstypeList(array(
            'crid'  =>  $this->crid,
        ));
        if(!empty($esTypes)){
            $esTypeIds = array();
            $examType = array();
            foreach ($esTypes as $esType){
                $esTypeIds[] = $esType['id'];
                $examType[$esType['id']] = array(
                    'id'    =>  $esType['id'],
                    'estype'    =>  $esType['estype'],
                    'examcount' =>  0,
                    'questioncount' =>0
                );
            }

            $examUitl = new ExamUitl($this->crid,$this->uid);
            $res = $examUitl->getUserTypeInfo($esTypeIds);
            if($res){
                foreach ($res as $exmaCount){
                    if(isset($examType[$exmaCount['estype']])){
                        $examType[$exmaCount['estype']]['examcount'] = $exmaCount['examListsize'];
                        $examType[$exmaCount['estype']]['questioncount'] = $exmaCount['questionListsize'];
                    }
                }
                $result['examType'] = array_values($examType);
            }
        }


        //$examUitl->getUserTypeInfo()

        //exit;

        return returnData(1,'',$result);

    }

    /**
     * 学生积分排行榜
     * @return mixed
     */
    public function rankAction() {
        $model = new ClassstudentsModel();
        $count = $model->getRankCount($this->classid);
        $students = $count == 0 ? array() : $model->getCreditRankList($this->classid, array('page' => $this->page, 'pagesize' => $this->pagesize), $this->orderType);
        return array(
            'count' => $count,
            'students' => $students
        );
    }
}
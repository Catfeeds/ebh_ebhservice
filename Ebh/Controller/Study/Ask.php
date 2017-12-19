<?php

/**
 * LogController 学生答题
 * Author: tzq
 * Email: 290847305@qq.com
 * time: 2017/10/24
 */
class AskController extends Controller
{


    public function init(){
        parent::init();//初始化
    }

    /***
     * 参数验证
     * @return array
     */
    public function parameterRules(){
        return array(

            'newcourseAction' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true),
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true)
            ),
            'getsortnameAction' => array(
                'type' => array(
                    'name' => 'type'
                , 'type' => 'int'
                , 'require' => true
                ),
                'crid'=> array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true

                )),

                'getRateAction' =>array(
                    'uid'=>array('name'=>'uid','type'=>'int','require'=>true),
                    'realname'=>array('name'=>'realname','type'=>'string','require'=>true),
                    'crid'=>array('name'=>'crid','type'=>'int','require'=>true)
                )
        );
    }

    /***获取提问回答排名
     * @param int $type 1提问排名2回答排名
     * @return array
     */
    public function getsortnameAction(){

        switch ($this->type) {
            case 2:
                $AskModel = new AskAnswersModel();
                $list = $AskModel->getAskanswer($this->crid);
                break;
            case 1:
                $questModel = new AskQuestionModel();
                $list = $questModel->getQuestions($this->crid);
                break;
            default:
                $list = array();

        }

        return $list;

    }

    /***
     * 获取问题比例
     * @param int $uid 用户uid
     * @param string $realname 用户名称
     * @return mixed
     */
    public function getRateAction(){
         $askModel = new AskQuestionModel();
         return $askModel->getRate($this->uid,$this->realname,$this->crid);
    }


}
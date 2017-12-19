<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Controller {
    public function __construct(){
    }
    /**
     *
     * 完成初始化工作
     */
    public function init() {

        $this->createParameter();
        $this->filterCheck();
    }


    /**
     * 根据参数的规则解析生成参数
     */
    protected function createParameter(){
        foreach($this->getParameterRules() as $key=>$rule){
            $this->$key = Ebh()->request->getByRule($rule);
        }
    }


    public function getParameterRules(){
        $rules = array();
        $allRules = $this->parameterRules();
        if (!is_array($allRules)) {
            $allRules = array();
        }


        if (isset($allRules[EbhFactory::$actionName]) && is_array($allRules[EbhFactory::$actionName])) {
            $rules = $allRules[EbhFactory::$actionName];
        }

        if (isset($allRules['*'])) {
            $rules = array_merge($allRules['*'], $rules);
        }

        return $rules;


    }

    public function parameterRules(){
        return array();
    }
    protected function filterCheck() {
        $filter = Ebh()->get('filter', 'Filter_None');
        if (isset($filter)) {
            if (!($filter instanceof Filter)) {
                throw new Exception_InternalServerError('Ebh()->filter should be instanceof Filter');
            }
        }
        $filter->check();
    }
}
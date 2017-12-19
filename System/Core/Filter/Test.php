<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Filter_Test implements Filter{

    protected $signName;

    public function __construct($signName = 'sign') {
        $this->signName = $signName;
    }


    public function check(){
        $allParams = Ebh()->request->getAll();
        if (empty($allParams)) {
            return;
        }


        $sign = isset($allParams[$this->signName]) ? $allParams[$this->signName] : '';
        unset($allParams[$this->signName]);

        if($sign != '666666'){
            Ebh()->log->debug('Wrong Sign', array('needSign' => '666666'));
            throw new Exception_BadRequest('wrong sign');
        }

    }
}
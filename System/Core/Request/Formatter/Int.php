<?php
/**
 * 整形格式化
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */

class Request_Formatter_Int extends Request_Formatter_Base implements Request_Formatter {

    /**
     * 对整型进行格式化
     *
     * @param mixed $value 变量值
     * @param array $rule array('min' => '最小值', 'max' => '最大值')
     * @return int/string 格式化后的变量
     *
     */
    public function parse($value, $rule) {
        return intval($this->filterByRange(intval($value), $rule));
    }
}

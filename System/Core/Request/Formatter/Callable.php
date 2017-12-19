<?php
/**
 * 格式化回调
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */


class Request_Formatter_Callable extends Request_Formatter_Base implements Request_Formatter {

    /**
     * 对回调类型进行格式化
     *
     * @param mixed $value 变量值
     * @param array $rule array('callback' => '回调函数', 'params' => '第三个参数')
     * @return boolean/string 格式化后的变量
     *
     */
    public function parse($value, $rule) {
        if (!isset($rule['callback']) || !is_callable($rule['callback'])) {
            throw new Exception_BadRequest(
                T('invalid callback for rule: {name}', array('name' => $rule['name']))
            );
        }

        if (isset($rule['params'])) {
            return call_user_func($rule['callback'], $value, $rule, $rule['params']);
        } else {
            return call_user_func($rule['callback'], $value, $rule);
        }
    }
}

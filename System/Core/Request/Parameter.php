<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class Request_Parameter{

    /**
     * 统一格式化参数操作
     *
     * @param $varName 变量名
     * @param $rule 格式规则
     * @param $params 参数列表
     * @return null
     */
    public static function format($varName, $rule, $params){
        $value = isset($rule['default']) ? $rule['default'] : NULL;
        $type = !empty($rule['type']) ? strtolower($rule['type']) : 'string';

        $key = isset($rule['name']) ? $rule['name'] : $varName;
        $value = isset($params[$key]) ? $params[$key] : $value;

        if ($value === NULL && $type != 'file') { //排除文件类型
            return $value;
        }

        return self::formatAllType($type, $value, $rule);
    }

    protected static function formatAllType($type, $value, $rule){
        $className = 'Request_Formatter_'.ucfirst($type);
        $formatter = Ebh()->get($className, $className);
        if (!($formatter instanceof Request_Formatter)) {
            throw new Exception_InternalServerError(
                "invalid type: {$type} for rule: {$rule['name']}"
            );
        }
        return $formatter->parse($value, $rule);
    }
}
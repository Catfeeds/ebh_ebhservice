<?php
/**
 * 格式化枚举类型
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class Request_Formatter_Enum extends Request_Formatter_Base implements Request_Formatter {

    /**
     * 检测枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @return 当不符合时返回$rule
     */
    public function parse($value, $rule) {
        $this->formatEnumRule($rule);

        $this->formatEnumValue($value, $rule);

        return $value;
    }

    /**
     * 检测枚举规则的合法性
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws Exception_InternalServerError
     */
    protected function formatEnumRule($rule) {
        if (!isset($rule['range'])) {
            throw new Exception_BadRequest("miss {$rule['name']}'s enum range");
        }

        if (empty($rule['range']) || !is_array($rule['range'])) {
            throw new Exception_BadRequest("{$rule['name']}'s enum range can not be empty");
        }
    }
}

<?php
/**
 * 公共基类
 * - 提供基本的公共功能，便于子类重用
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */

class Request_Formatter_Base {

    /**
     * 根据范围进行控制
     */
    protected function filterByRange($value, $rule) {
        $this->filterRangeMinLessThanOrEqualsMax($rule);

        $this->filterRangeCheckMin($value, $rule);

        $this->filterRangeCheckMax($value, $rule);

        return $value;
    }

    protected function filterRangeMinLessThanOrEqualsMax($rule) {
        if (isset($rule['min']) && isset($rule['max']) && $rule['min'] > $rule['max']) {
            throw new Exception_InternalServerError(
                "min should <= max, but now {$rule['name']} min = {$rule['min']} and max = {$rule['max']}"
            );
        }
    }

    protected function filterRangeCheckMin($value, $rule) {
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new Exception_BadRequest(
                "{$rule['name']} should >= {$rule['min']}, but now {$rule['name']} = {$value}"
            );
        }
    }

    protected function filterRangeCheckMax($value, $rule) {
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new Exception_BadRequest(
                "{$rule['name']} should <= {$rule['max']}, but now {$rule['name']} = {$value}"
            );
        }
    }

    /**
     * 格式化枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws Exception_InternalServerError
     */
    protected function formatEnumValue($value, $rule) {
        if (!in_array($value, $rule['range'])) {
            throw new Exception_BadRequest(
                "{$rule['name']} should be in ".implode('/', $rule['range']).", but now {$rule['name']} = {$value}"
            );
        }
    }
}

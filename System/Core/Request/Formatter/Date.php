<?php
/**
 * 时间格式化
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class Request_Formatter_Date extends Request_Formatter_Base implements Request_Formatter {

    /**
     * 对日期进行格式化
     *
     * @param timestamp $value 变量值
     * @param array $rule array('format' => 'timestamp', 'min' => '最小值', 'max' => '最大值')
     * @return timesatmp/string 格式化后的变量
     *
     */
    public function parse($value, $rule) {
        $rs = $value;

        $ruleFormat = !empty($rule['format']) ? strtolower($rule['format']) : '';
        if ($ruleFormat == 'timestamp') {
            $rs = strtotime($value);
            if ($rs <= 0) {
            	$rs = 0;
            }

            $rs = $this->filterByRange($rs, $rule);
        }

        return $rs;
    }
}

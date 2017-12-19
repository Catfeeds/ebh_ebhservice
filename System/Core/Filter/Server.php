<?php
/**
 * Filter_Server 过滤器，只要用于调用客户端的IP验证，如果调用的客户端IP地址不在 allowservers.php的列表中，则不予通过
 * Author: lch
 * Email: 15335667@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Filter_Server implements Filter{
    public function __construct() {
    }

	/**
	 * 重写验证方法
	 */
    public function check(){
		if (!$this->_checkServer()) {
			$ip = getip();
			Ebh()->log->debug('The client ipaddress is not allow', $ip);
            throw new Exception_BadRequest("The client ipaddress is not allow", 1);;
        }
    }
	/**
     * 验证是否合法IP来源
     */
    private function _checkServer() {
        $ip = getip();
        $allowIps = Ebh()->config->get('allowservers');	//合法的IP列表在 Config/allowserver.php文件中配置
        if (empty($allowIps))   //必须有限制，否则直接返回false
            return FALSE;
        if (in_array($ip, $allowIps))
            return TRUE;
        foreach ($allowIps as $sip) {   //如果不在列表中，则验证是否*匹配 如 192.168.0.* 那么所有 192.168.0.段的IP地址都合法
            $sipseg = explode('.', $sip);
            $ipseg = explode('.', $ip);
            if (($ipseg[0] == $sipseg[0] || $sipseg[0] == '*') &&
                ($ipseg[1] == $sipseg[1] || $sipseg[1] == '*') &&
                ($ipseg[2] == $sipseg[2] || $sipseg[2] == '*') &&
                ($ipseg[3] == $sipseg[3] || $sipseg[3] == '*')) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
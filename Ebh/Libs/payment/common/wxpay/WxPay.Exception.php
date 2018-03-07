<?php
/**
 * 
 * 微信支付API异常类
 * @author widyhu
 *
 */
class WxPayException extends EbhException {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}

<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:13
 */
class OpenLimit{
    /*
	检查开通情况
	*/
    public function checkStatus(&$item,$uid){
        if(empty($item['bid']) && empty($item['itemid'])){
            return TRUE;
        }
        $idtype = empty($item['itemid'])?'bid':'itemid';
        $id = $item[$idtype];

        $ocdata['crid'] = $item['crid'];
        $ocdata[$idtype] = $id;
        $ocdata['uid'] = $uid;
        $opencount = runAction('Classroom/Item/openCount',array($ocdata));
        $item['opencount'] = $opencount['opencount'] > $item['limitnum']?$item['limitnum']:$opencount['opencount'];
        $item['selfcount'] = empty($opencount['selfcount'])?0:$opencount['selfcount'];
        //如果人数达到上限,且曾经未开通,则不能开通
        $cantpay = $item['opencount'] == $item['limitnum'] && empty($item['selfcount']);
        return !$cantpay;
    }
}
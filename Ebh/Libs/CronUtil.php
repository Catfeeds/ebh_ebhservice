<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:11
 * 计划任务控制类
 */
class CronUtil{

    //定时任务添加接口
    const ADD_URL = '/cron/add';
    //定时任务删除接口
    const DEL_URL = '/cron/del';
    /**
     * 添加一个URL类型非循环的定时任务
     * @param $jobId 任务ID
     * @param $startTime 任务开始时间
     * @param $url 访问的url
     * @return bool
     */
    public static function addUrlCronWithoutLoop($jobId,$startTime,$url){
        $data['jobid'] = $jobId;
        $data['isloop'] = false;
        $data['start'] = $startTime;

        $data['command'] = array(
            'type'  =>  1,
            'url'   =>  $url,
            'method'    =>  'GET'
        );
        $result = self::doPost(self::ADD_URL,$data);

        if($result['ret'] == 200){
            return true;
        }else{
            return  false;
        }
    }

    /**
     * 删除定时任务
     * @param $jobId 任务ID
     * @return bool
     */
    public static function deleteCron($jobId){
        $data['jobid'] = $jobId;
        $result = self::doPost(self::DEL_URL,$data);
        if($result['ret'] == 200){
            return true;
        }else{
            return  false;
        }
    }

    private static function doPost($uri, $data){
        $uri = Ebh()->config->get('apiserver.cron.host').$uri;
        $ch = curl_init();
        $datastr = (json_encode($data));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$datastr);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($datastr))
        );
        curl_setopt($ch, CURLOPT_URL, $uri);
        $ret = curl_exec($ch);
        curl_close($ch);
        return json_decode($ret,true);
    }
}
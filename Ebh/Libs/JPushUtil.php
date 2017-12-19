<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:10
 */
require_once APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'JPush' . DIRECTORY_SEPARATOR . 'autoload.php';
use JPush\Client as JPush;

/**
 * Class JPushUtil
 * 推送类  实例化时传入网校ID
 */
class JPushUtil{
    public $client = null;
    private $config = array();
    public function __construct($crid){
        $pushConfig = Ebh()->config->get('push');
        if(!isset($pushConfig[$crid])){
            throw new Exception_InternalServerError('该网校未开启推送');
        }
        $this->config = $pushConfig[$crid];
        $this->client =  new JPush($pushConfig[$crid]['app_key'], $pushConfig[$crid]['master_secret']);
    }

    /**
     * 向设备发送推送
     * @param $content 推送的内容
     * @param array $extras 推送的扩展数据
     * @param string $platform 推送平台 默认所有平台 array('ios','android')
     * @return bool
     */
    public function push($content,$extras = array(),$platform = 'all'){
        $extras['time'] = SYSTIME;
        $push_payload = $this->client->push()
            ->setPlatform($platform)
            ->addAllAudience()
            ->setNotificationAlert($content);

        if($platform == 'all' || (is_array($platform) && in_array('ios',$platform))){
            $push_payload->iosNotification($content,array(
                'sound' =>  'sound.caf',
                'extras'    =>  $extras
            ));
        }
        if($platform == 'all' || (is_array($platform) && in_array('android',$platform))){
            $push_payload->androidNotification($content,array(
                'extras'    =>  $extras
            ));
        }

        $push_payload->message($content, array(
            'extras'    =>  $extras
        ))->options(array(
                'apns_production'   => !$this->config['debug']
            ));


        try {
            $response = $push_payload->send();
            if($response['http_code'] == 200){
                return true;
            }else{
                Ebh()->log->error(var_export($response,true));
                return false;
            }
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            //print $e;
            Ebh()->log->error(var_export($e,true));
            return  false;
        } catch (\JPush\Exceptions\APIRequestException $e) {

            // try something here
            Ebh()->log->error(var_export($e,true));
            return  false;
        }

    }
}
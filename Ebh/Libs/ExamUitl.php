<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:13
 */
class ExamUitl{
    private $uid = 0;
    private $crid = 0;
    public function __construct($crid,$uid){
        $this->uid = $uid;
        $this->crid = $crid;
    }


    public function getK(){
        return authcode(json_encode(array('uid'=>$this->uid,'crid'=>$this->crid,'t'=>SYSTIME)),'ENCODE');
    }



    public function getUserTypeInfo($esType){
        $url = '/exam/estype';
        $parameters['k'] = $this->getK();
        $parameters['estype'] = $esType;
        $parameters['crid'] = $this->crid;
        $ret = $this->do_post($url,$parameters);


        if($ret['errCode'] == 0){
            return $ret['datas']['estypeMap'];
        }else{
            return false;
        }
    }
    /**
     * 提交接口到服务器
     * @param $uri
     * @param $data
     * @return mixed
     */
    private function do_post($uri, $data){
        $uri = Ebh()->config->get('apiserver.examv2.host').$uri;
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
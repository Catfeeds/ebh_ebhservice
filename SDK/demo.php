<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
require_once "EbhClient.php";
defined('APPID') || define('APPID', 'ebhXPlZuZXpCUUS');
defined('APPSECRET') || define('APPSECRET','JagRGE6sjlc648661ZksgJqxkxAdjDVd');


$client = new EbhClient(APPID,APPSECRET);


//$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setFilter(new FilterDemo(APPSECRET))->setService('Forums.frontAllForumList')->addParams('crid','10194')->addParams('uid','12165')->addParams('p',1)->setParser(new ParserDemo())->request();

/*$data['crid'] = 10194;
$data['uid'] = 12165;
$data['p'] = 1;
$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setParser(new ParserDemo())->setFilter(new FilterDemo(APPSECRET))->setService('Forums.frontMyForumList')->addParams($data)->request();
*/

//$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setFilter(new FilterDemo(APPSECRET))->setParser(new ParserDemo())->setService('Forums.add')->addParams('crid','10194')->addParams('name','测试社区')->addParams('image','http://www.baidu.com')->addParams('category',array('123','1234'))->addParams('manager','12165,13192')->request();

//$data = json_decode('{"fid":1,"name":"\u4e92\u52a8\u793e\u533a\u6d4b\u8bd5","image":"","notice":"12345 \u4e0a\u5c71\u6253\u8001\u864e","manager":"12165,12186","categorys":{"1":"\u516c\u544a","2":"\u5176\u4ed6"}}',true);
//$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setFilter(new FilterDemo(APPSECRET))->setParser(new ParserDemo())->setService('Forums.edit')->addParams($data)->request();


/*$data = '{"fid":1,"uid":"12165","cate_id":1,"crid":"10194","title":"\u53d1\u5e03\u5e16\u5b50\u6d4b\u8bd51234","imgs":"http:\/\/img.ebanhui.com\/ebh\/2017\/03\/06\/14887891844185.jpeg","content":"<p><span style=\"font-size: 16px;\"><img src=\"http:\/\/img.ebanhui.com\/ebh\/2017\/03\/06\/14887891844185.jpeg\" _src=\"http:\/\/img.ebanhui.com\/ebh\/2017\/03\/06\/14887891844185.jpeg\" width=\"224\" height=\"202\"\/>\u7684\u6492\u65e6<\/span><br><\/p><p><span style=\"font-size: 16px;\"><br><\/span><\/p><p><span style=\"font-size: 16px;\">\u6492\u65e6\u6492<\/span><\/p><p><span style=\"font-size: 16px;\"><br><\/span><\/p>"}';

$data = json_decode($data,true);
$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setFilter(new FilterDemo(APPSECRET))->setParser(new ParserDemo())->setService('ForumsSubject.add')->addParams($data)->request();
*/

/*
$data['fid'] = 1;
$data['p'] = 1;
$data['is_hot'] = 0;
$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setParser(new ParserDemo())->setFilter(new FilterDemo(APPSECRET))->setService('ForumsSubject.getSubject')->addParams($data)->request();
*/

/*$data['fid'] = 1;
$data['uid'] = 2;*/

$data['sid'] = 1;
$result = $client->setHost("http://www.ebhservice.com/ebh.php")->setParser(new ParserDemo())->setFilter(new FilterDemo(APPSECRET))->setService('ForumsSubjectReply.list')->addParams($data)->request();

var_dump($result);

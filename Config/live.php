<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:46
 */
$live = array(
    'imurl'=>'http://www.ebh.net/im/live.html?key=[key]',			//聊天url
    'imurl_app'=>'http://www.ebh.net/im/app.html?key=[key]'
);
$live['default'] = 'Netease';//默认使用的服务商
$live['10194'] = 'Netease';//单独网校开启单独的服务商
$live['12719'] = 'Netease';//单独网校开启单独的服务商
$live['12809'] = 'Netease';//hwszdx.ebh.net 成都	需要使用网易直播的，则只需在这里配置即可
$live['deskey'] = '12345678'; //直播des加密key
$live['desiv'] = '12345678';//直播信息des加密iv
$live['Sata'] = array(
    'pushUrl'   =>  'rtmp://ebhrtmppub.satacdn.com/ebhlive/[liveid]',
    'rtmpPullUrl'   =>  'rtmp://ebhrtmpplay.satacdn.com/ebhlive/[liveid]',
    'hlsPurllUrl'   =>  'http://ebhhlsplay.satacdn.com/ebhlive/[liveid]/index.m3u8'

);
$live['Netease'] = array(
    'AppKey'    =>  '63b202b36e994396a82af8f6b7dbe8d4',
    'AppSecret' =>  'b2a324f30c954c85915a8165939821b1'
);
$live['teacher_board_rtmp'] = 'rtmp://ebhrtmpplay.satacdn.com/ebhlive/[liveid]s';
$live['teacher_camera_rtmp'] = 'rtmp://ebhrtmpplay.satacdn.com/ebhlive/[liveid]c';
$live['teacher_board_http'] = 'http://ebhhlsplay.satacdn.com/ebhlive/[liveid]s/index.m3u8';

return $live;
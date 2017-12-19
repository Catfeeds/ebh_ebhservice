<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:54
 */
class OpenModel{
    public function dobind($type,$data,$user){
        $retflag = false;
        $bdmodel = new BindModel();
        $umodel = new UserModel();
        if($type=='qq'){//QQ
            $bdata =array(
                'uid'=>$user['uid'],
                'is_qq'=>1,
                'qq_str'=>json_encode(
                    array(
                        'qq'=>'',
                        'uid'=>$user['uid'],
                        'openid'=>	$data['openid'],
                        'nickname'=>$data['nickname'],
                        'dateline'=>SYSTIME
                    )
                )
            );
            //log_message(var_export($bdata,true));
            $retflag = $bdmodel->doBind($bdata,$user['uid']);

            //更新主表qqopid字段
            if(!empty($retflag)){
                $udata = array(
                    'qqopid'=>$data['openid'],
                );
                $umodel->update($udata,$user['uid']);
            }
        }elseif($type=='wx'){//微信
            $bdata =array(
                'uid'=>$user['uid'],
                'is_wx'=>1,
                'wx_str'=>json_encode(
                    array(
                        'wx'=>'',
                        'uid'=>$user['uid'],
                        'openid'=>$data['openid'],
                        'unionid'=>$data['unionid'],
                        'nickname'=>$data['nickname'],
                        'dateline'=>SYSTIME,
                        'from'=>'gzh'
                    )
                )
            );
            $retflag = $bdmodel->doBind($bdata,$user['uid']);

            //更新主表wxopenid字段
            if(!empty($retflag)){
                $udata = array(
                    'wxunionid'=>$data['unionid'],
                    'wxopid'=>$data['openid']//这个要注意下 公众号过来的是 wxopid这个字段 @eker 2016年5月18日10:41:21
                );
                $umodel->update($udata,$user['uid']);
            }

        }elseif($type=='sina'){//微博
            $bdata =array(
                'uid'=>$user['uid'],
                'is_weibo'=>1,
                'weibo_str'=>json_encode(
                    array(
                        'weibo'=>'',
                        'uid'=>$user['uid'],
                        'sinaopid'=>$data['openid'],
                        'nickname'=>$data['nickname'],
                        'dateline'=>SYSTIME
                    )
                )
            );
            $retflag = $bdmodel->doBind($bdata,$user['uid']);

            //更新主表wxopenid字段
            if(!empty($retflag)){
                $udata = array(
                    'sinaopid'=>$data['openid'],
                );
                $umodel->update($udata,$user['uid']);
            }
        }

        return $retflag;
    }
}
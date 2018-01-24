<?php

/**
 * 网校信息
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/15
 * Time: 14:28
 */
class RoomController extends Controller
{
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules()
    {
        return array(
            //网校基本信息
            'infoAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //更新网校基本信息
            'updateAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'crname' => array(
                    'name' => 'crname',
                    'type' => 'string'
                ),
                'cface' => array(
                    'name' => 'cface',
                    'type' => 'string'
                ),
                'summary' => array(
                    'name' => 'summary',
                    'type' => 'string'
                ),
                'message' => array(
                    'name' => 'message',
                    'type' => 'string'
                ),
                'qcode' => array(
                    'name' => 'qcode',
                    'type' => 'string'
                ),
                'crphone' => array(
                    'name' => 'crphone',
                    'type' => 'string'
                ),
                'craddress' => array(
                    'name' => 'craddress',
                    'type' => 'string'
                ),
                'kefu' => array(
                    'name' => 'kefu',
                    'type' => 'string'
                ),
                'kefuqq' => array(
                    'name' => 'kefuqq',
                    'type' => 'string'
                ),
                'crlabel' => array(
                    'name' => 'crlabel',
                    'type' => 'string'
                ),
                'icp' => array(
                    'name' => 'icp',
                    'type' => 'string'
                ),
                'wechatimg' => array(
                    'name' => 'wechatimg',
                    'type' => 'string'
                ),
                'navigator' => array(
                    'name' => 'navigator',
                    'type' => 'string'
                ),
                'lng' => array(
                    'name' => 'lng',
                    'type' => 'float'
                ),
                'lat' => array(
                    'name' => 'lat',
                    'type' => 'float'
                )
            ),
            //网校独立域名
            'domainAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //申请独立域名
            'applyDomainAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'fulldomain' => array(
                    'name' => 'fulldomain',
                    'type' => 'string',
                    'require' => true
                ),
                'crname' => array(
                    'name' => 'crname',
                    'type' => 'string',
                    'require' => true
                )
            ),
            //seo信息
            'seoAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //更新seo
            'updateSeoAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'favicon' => array(
                    'name' => 'favicon',
                    'type' => 'string'
                ),
                'faviconimg' => array(
                    'name' => 'faviconimg',
                    'type' => 'string'
                ),
                'metakeywords' => array(
                    'name' => 'metakeywords',
                    'type' => 'string'
                ),
                'metadescription' => array(
                    'name' => 'metadescription',
                    'type' => 'string'
                ),
                'analytics' => array(
                    'name' => 'analytics',
                    'type' => 'string'
                ),
                'subtitle' => array(
                    'name' => 'subtitle',
                    'type' => 'string'
                )
            ),
            //微信公众服务号消息
            'ethsettingAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //更新微信公众号
            'updateEthsettingAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'appID' => array(
                    'name' => 'appID',
                    'type' => 'string',
                    'require' => true
                ),
                'appsecret' => array(
                    'name' => 'appsecret',
                    'type' => 'string',
                    'require' => true
                ),
                'phone' => array(
                    'name' => 'phone',
                    'type' => 'string',
                    'require' => true
                ),
                'wechat' => array(
                    'name' => 'wechat',
                    'type' => 'string',
                    'require' => true
                ),
                'tempid' => array(
                    'name' => 'tempid',
                    'type' => 'string',
                    'require' => true
                ),
                'token' => array(
                    'name' => 'token',
                    'type' => 'string',
                    'require' => true
                ),
                'server_url' => array(
                    'name' => 'server_url',
                    'type' => 'string',
                    'require' => true
                ),
                'domain' => array(
                    'name' => 'domain',
                    'type' => 'string',
                    'require' => true
                ),
                'ebhcode' => array(
                    'name' => 'ebhcode',
                    'type' => 'string',
                    'require' => true
                )
            ),
            //网校其它设置信息
            'othersettingAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //更新网校其它设置
            'updateOthersettingAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'refusesTranger' => array(
                    'name' => 'refusesTranger',
                    'type' => 'int'
                ),
                'mobileRegister' => array(
                    'name' => 'mobileRegister',
                    'type' => 'int'
                ),
                'reviewInterval' => array(
                    'name' => 'reviewInterval',
                    'type' => 'int'
                ),
                'postInterval' => array(
                    'name' => 'postInterval',
                    'type' => 'int'
                ),
                'limitnum' => array(
                    'name' => 'limitnum',
                    'type' => 'int'
                ),
                'creditrule' => array(
                    'name' => 'creditrule',
                    'type' => 'string'
                ),
                'showmodule' => array(
                    'name' => 'showmodule',
                    'type' => 'int'
                ),
                'showlink' => array(
                    'name' => 'showlink',
                    'type' => 'int'
                ),
                'ebhbrowser' => array(
                    'name' => 'ebhbrowser',
                    'type' => 'int'
                ),
                'isbanbuy' => array(
                    'name' => 'isbanbuy',
                    'type' => 'int'
                ),
                'isbanregister' => array(
                    'name' => 'isbanregister',
                    'type' => 'int'
                ),
                'isbanthirdlogin' => array(
                    'name' => 'isbanthirdlogin',
                    'type' => 'int'
                ),
                'isdepartment' => array(
                    'name' => 'isdepartment',
                    'type' => 'int'
                ),
				'cwlistonlyself' => array(
                    'name' => 'cwlistonlyself',
                    'type' => 'int'
                )
            ),
            //登录限制
            'userClientListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //登录限制数
            'userClientCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //IP黑名单
            'ipBlackListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                ),
                'sortmode' => array(
                    'name' => 'sortmode',
                    'type' => 'int'
                )
            ),//IP黑名单统计
            'ipBlackListCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //添加IP黑名单
            'addIpBlackAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'require' => true
                ),
                'addr' => array(
                    'name' => 'addr',
                    'type' => 'string'
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string'
                )
            ),
            //删除黑名单
            'deleteIpBlackAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'ips' => array(
                    'name' => 'ips',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //添加用户黑名单
            'addUserBlackAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'username' => array(
                    'name' => 'username',
                    'type' => 'string',
                    'require' => true
                ),
                'operid' => array(
                    'name' => 'operid',
                    'type' => 'int',
                    'require' => true
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string'
                )
            ),
            //用户黑名单列表
            'userBlackListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                ),
                'sortmode' => array(
                    'name' => 'sortmode',
                    'type' => 'int'
                )
            ),
            //用户黑名单统计
            'userBlackListCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //删除用户黑名单
            'deleteUserBlackAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uids' => array(
                    'name' => 'uids',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //关键词过滤统计
            'filtersCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                )
            ),
            //关键词过滤列表
            'filtersListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'k' => array(
                    'name' => 'k',
                    'type' => 'string'
                ),
                'sortmode' => array(
                    'name' => 'sortmode',
                    'type' => 'int'
                )
            ),
            //删除关键词过滤
            'deleteFilterAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'fids' => array(
                    'name' => 'fids',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //添加关键词过滤
            'addFiltersAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'keyword' => array(
                    'name' => 'keyword',
                    'type' => 'string',
                    'require' => true
                ),
                'replace' => array(
                    'name' => 'replace',
                    'type' => 'string'
                )
            ),
            //取消用户登录限制
            'deleteUserClientAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'uids' => array(
                    'name' => 'uids',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //站点导航
            'navigatorAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
			//获取自定义富文本
            'getCustomMessageAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
				'index' => array(
                    'name' => 'index',
                    'type' => 'string',
                    'require' => true
                )
            ),
			//保存自定义富文本
            'saveCustomMessageAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
				'index' => array(
                    'name' => 'index',
                    'type' => 'string',
                    'require' => true
                ),
				'custommessage' => array(
                    'name' => 'custommessage',
                    'type' => 'string',
                    'default'=>''
                )
            ),
            //解绑独立域名
            'unbindDomainAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            'domainExistsAction' => array(
                'fulldomain' => array(
                    'name' => 'fulldomain',
                    'type' => 'string',
                    'require' => true
                )
            ),
            'getFolderIdAction' => array(
                'crid' => array('name'=>'crid','type'=>'int')
            ),
            //获取网校模块
            'getModuleAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            'getBindStatusAction' => array(
                'domain' => array(
                    'name' => 'domain',
                    'type' => 'string'
                )
            ),
            'getRefuseStatusAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int'
                )
            ),
            'ipIsExistsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int'
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string'
                ),
            ),
            'userIsExistsAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int'
                ),
                'username' => array(
                    'name' => 'username',
                    'type' => 'string'
                ),
            ),
            //读取管理员菜单
            'adminMenusAction' => array(
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 网校基本信息
     * @return array
     */
    public function infoAction()
    {
        $classroom_model = new ClassRoomModel();
        $room = $classroom_model->getModel($this->crid);
        if (empty($room)) {
            return false;
        }
        if (empty($room['cface'])) {
            $qModel = new ComponentItemOptionModel();
            $qcode = $qModel->getQcode($this->crid);
            if (!empty($qcode)) {
                $room['qcode'] = $qcode;
            }
        }
        $class_model = new ClassesModel();
        $class_count = $class_model->getCountForRoom($this->crid, $room['isschool']);
        $room['classnum'] = $class_count === false ? '' : $class_count;
        $folder_model = new FolderModel();
        $folder_count = $folder_model->getCountForRoom($this->crid);
        $room['foldernum'] = $folder_count === false ? '' : $folder_count;
        $courseware_model = new CoursewareModel();
        $reviewnum = $courseware_model->getReviewCountForRoom($this->crid);
        $room['reviewnum'] = $reviewnum === false ? '' : $reviewnum;
        $playLogModel = new PlayLogModel();

        $countArr = $playLogModel->getListForClassroom2(array(
            'crid' => $this->crid,
            'get_nologs' => false
        ));
        $room['studynum'] = 0;
        if (!empty($countArr)) {
            $c = array_map(function($ci) {
                return !empty($ci['scount']) ? $ci['scount'] : 0;
            }, $countArr);
            $room['studynum'] = array_sum($c);
        }

        $model = new SystemSettingModel();
        $otherInfo = $model->getModel($this->crid);
        if (!empty($otherInfo)) {
            $room = array_merge($room, $otherInfo);
        }


        if (empty($room['crname'])) {
            $room['crname'] = '';
        }
        if (empty($room['cface'])) {
            $room['cface'] = '';
        }
        if (empty($room['begindate'])) {
            $room['begindate'] = 0;
        }
        if (empty($room['enddate'])) {
            $room['enddate'] = 0;
        }
        if (empty($room['domain'])) {
            $room['domain'] = '';
        }
        if (empty($room['fulldomain'])) {
            $room['fulldomain'] = '';
        }
        if (empty($room['catid'])) {
            $room['catid'] = 0;
        }
        if (empty($room['grade'])) {
            $room['grade'] = 0;
        }
        if (empty($room['summary'])) {
            $room['summary'] = '';
        }
        if (empty($room['contacts'])) {
            $room['contacts'] = '';
        }
        if (empty($room['message'])) {
            $room['message'] = '';
        }
        if (empty($room['contacts'])) {
            $room['contacts'] = '';
        }
        if (empty($room['craddress'])) {
            $room['craddress'] = '';
        }
        if (empty($room['crphone'])) {
            $room['crphone'] = '';
        }
        if (empty($room['crlabel'])) {
            $room['crlabel'] = '';
        }
        if (empty($room['stunum'])) {
            $room['stunum'] = 0;
        }
        if (empty($room['teanum'])) {
            $room['teanum'] = 0;
        }
        if (empty($room['coursenum'])) {
            $room['coursenum'] = 0;
        }
        if (empty($room['examcount'])) {
            $room['examcount'] = 0;
        }
        if (empty($room['asknum'])) {
            $room['asknum'] = 0;
        }
        if (empty($room['classnum'])) {
            $room['classnum'] = 0;
        }
        if (empty($room['foldernum'])) {
            $room['foldernum'] = 0;
        }
        if (empty($room['reviewnum'])) {
            $room['reviewnum'] = 0;
        }
        if (empty($room['studynum'])) {
            $room['studynum'] = 0;
        }
        if (empty($room['kufuqq'])) {
            $room['kufuqq'] = '';
        }
        if (empty($room['kufu'])) {
            $room['kufu'] = '';
        }
        if (empty($room['wechatimg'])) {
            $room['wechatimg'] = '';
        }
        if (empty($room['payqcode'])) {
            $room['payqcode'] = '';
        }
        if (empty($room['icp'])) {
            $room['icp'] = '';
        }
        return $room;
    }

    /**
     * 更新网校基本信息
     * @return mixed
     */
    public function updateAction()
    {
        $params = array();
        $params['crid'] = $this->crid;
        if (isset($this->cface)) {
            $params['cface'] = trim($this->cface);
        }
        if (isset($this->crname)) {
            $params['crname'] = trim($this->crname);
        }
        if (isset($this->summary)) {
            $params['summary'] = trim($this->summary);
        }
        if (isset($this->message)) {
            $params['message'] = trim($this->message);
        }
        if (isset($this->crphone)) {
            $params['crphone'] = trim($this->crphone);
        }
        if (isset($this->craddress)) {
            $params['craddress'] = trim($this->craddress);
        }
        if (isset($this->kefu)) {
            $params['kefu'] = trim($this->kefu);
        }
        if (isset($this->kefuqq)) {
            $params['kefuqq'] = trim($this->kefuqq);
        }
        if (isset($this->crlabel)) {
            $params['crlabel'] = trim($this->crlabel);
        }
        if (isset($this->icp)) {
            $params['icp'] = trim($this->icp);
        }
        if (isset($this->wechatimg)) {
            $params['wechatimg'] = trim($this->wechatimg);
        }
        if (isset($this->lng)) {
            $params['lng'] = $this->lng;
        }
        if (isset($this->lat)) {
            $params['lat'] = $this->lat;
        }
		if (isset($this->navigator)){
			$params['navigator'] = trim($this->navigator);
		}
        $classroom_model = new ClassRoomModel();
        $ret =  $classroom_model->update($this->crid, $params);
        if (isset($this->wechatimg)) {
            $cmodel = new ComponentItemOptionModel();
            $cmodel->qcode($this->crid, $params['wechatimg']);
        }
        return $ret;
    }

    /**
     * 网校独立域名信息
     */
    public function domainAction()
    {
        $model = new DomainModel();
        return $model->getDomainCheck($this->crid);
    }

    /**
     * 检查域名是否存在
     * @return bool
     */
    public function domainExistsAction() {
        $model = new DomainModel();
        return $model->domainExists($this->fulldomain);
    }

    /**
     * 申请独立域名
     */
    public function applyDomainAction()
    {
        $model = new DomainModel();
        $domainExists = $model->domainExists($this->fulldomain);
        if ($domainExists) {
            //域名已占用或已拥有
            return 1;
        }
        $hasFullDomain = $model->hasFullDomain($this->crid);
        if ($hasFullDomain) {
            //域名已生效
            return 2;
        }
        $ret = $model->applyDomain($this->crid, $this->fulldomain, $this->crname);
        if ($ret === true) {
            //域名修改成功
            return 3;
        }
        if (!empty($ret)) {
            //域名申请成功
            return 4;
        }
        if (!empty($ret)) {
            //域名申请失败
            return 5;
        }
    }

    /**
     * 解绑独立域名
     */
    public function unbindDomainAction()
    {
        $model = new DomainModel();
        return $model->unbindDomain($this->crid);
    }

    /**
     * seo信息
     */
    public function seoAction()
    {
        $model = new SystemSettingModel();
        $seo = $model->getModel($this->crid);
        if (!empty($seo)) {
            $classroom_model = new ClassRoomModel();
            $room_info = $classroom_model->getInfoForSeo($this->crid);
            $seo['icp'] = $room_info['icp'];
            $seo['crname'] = $room_info['crname'];
        } else {
            $rmodel = new ClassRoomModel();
            $room = $rmodel->getModel($this->crid);
            $seo = array(
                'crname' => $room['crname'],
                'favicon' => '',
                'faviconimg' => '',
                'metakeywords' => '',
                'metadescription' => '',
                'analytics' => '',
                'subtitle'=> '',
                'icp'=> ''
            );
        }
        return $seo;
    }

    /**
     * 更新seo信息
     */
    public function updateSeoAction()
    {
        $model = new SystemSettingModel();
        $params = array();
        if (isset($this->favicon) && $this->favicon != 'undefined') {
            $params['favicon'] = trim($this->favicon);
        }
        if (isset($this->faviconimg) && $this->faviconimg != 'undefined') {
            $params['faviconimg'] = trim($this->faviconimg);
        }
        if (isset($this->metakeywords) && $this->metakeywords != 'undefined') {
            $params['metakeywords'] = trim($this->metakeywords);
        }
        if (isset($this->metadescription) && $this->metadescription != 'undefined') {
            $params['metadescription'] = trim($this->metadescription);
        }
        if (isset($this->analytics) && $this->analytics != 'undefined') {
            $params['analytics'] = trim($this->analytics);
        }
        if (isset($this->subtitle) && $this->subtitle != 'undefined') {
            $params['subtitle'] = trim($this->subtitle);
        }

        if ($model->exists($this->crid)) {
            return $model->update($this->crid, $params);
        } else {
            return $model->add($this->crid, $params);
        }
    }

    /*
     * 微信公众服务号消息
     */
    public function ethsettingAction()
    {
        $model = new EthModel();
        return $model->getConfigByCrid($this->crid);
    }

    /**
     * 更新微信公众号
     */
    public function updateEthsettingAction()
    {
        $model = new EthModel();
        $params['crid'] = $this->crid;
        $params['appID'] = $this->appID;
        $params['appsecret'] = $this->appsecret;
        $params['tempid'] = $this->tempid;
        $params['phone'] = $this->phone;
        $params['wechat'] = $this->wechat;
        $params['token'] = $this->token;
        $params['server_url'] = $this->server_url;
        $params['domain'] = $this->domain;
        $params['ebhcode'] = $this->ebhcode;
        return $model->saveSetting($params);
    }

    /**
     * 网校其它设置信息
     */
    public function othersettingAction() {
        $model = new SystemSettingModel();
        return $model->getOtherSetting($this->crid);
    }

    /**
     * 更新网校其它设置
     * @return mixed
     */
    public function updateOthersettingAction() {
        $model = new SystemSettingModel();
        $params = array();
        if ($this->refusesTranger !== NULL) {
            $params['refuse_stranger'] = $this->refusesTranger;
        }
        if ($this->mobileRegister !== NULL) {
            $params['mobile_register'] = $this->mobileRegister;
        }
        if ($this->reviewInterval !== NULL) {
            $params['review_interval'] = $this->reviewInterval;
        }
        if ($this->postInterval !== NULL) {
            $params['post_interval'] = $this->postInterval;
        }
        if ($this->limitnum !== NULL) {
            $params['limitnum'] = $this->limitnum;
        }
        if ($this->creditrule !== NULL) {
            $params['creditrule'] = $this->creditrule;
        }
        if ($this->showlink !== NULL) {
            $params['showlink'] = min(1, max(0, $this->showlink));
        }
        if ($this->showmodule !== NULL) {
            $params['showmodule'] = min(1, max(0, $this->showmodule));
        }
        if ($this->ebhbrowser !== NULL) {
            $params['ebhbrowser'] = min(1, max(0, $this->ebhbrowser));
        }
        if ($this->isbanbuy !== NULL) {
            $params['isbanbuy'] = min(1, max(0, $this->isbanbuy));
        }
        if ($this->isbanregister !== NULL) {
            $params['isbanregister'] = min(1, max(0, $this->isbanregister));
        }
        if ($this->isbanthirdlogin !== NULL) {
            $params['isbanthirdlogin'] = min(1, max(0, $this->isbanthirdlogin));
        }
        if ($this->isdepartment !== NULL) {
            $params['isdepartment'] = min(1, max(0, $this->isdepartment));
        }
		if ($this->cwlistonlyself !== NULL) {
            $params['cwlistonlyself'] = min(1, max(0, $this->cwlistonlyself));
        }
        if ($model->exists($this->crid)) {
            return $model->update($this->crid, $params);
        } else {
            return $model->add($this->crid, $params);
        }
    }

    /**
     * 登录限制
     */
    public function userClientListAction() {
        $model = new UserClientModel();
        $limit = array();
        if ($this->pagesize !== NULL) {
            $limit['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limit['page'] = $this->pagenum;
        }
        return $model->getClientList($this->crid, $this->k, $limit);
    }
    /*
     * 登录限制数
     */
    public function userClientCountAction() {
        $model = new UserClientModel();
        return $model->getClientCount($this->crid, $this->k);
    }

    /**
     * IP黑名单统计
     */
    public function ipBlackListCountAction() {
        $model = new IpBlackListModel();
        return $model->getCount($this->crid, $this->k);
    }

    /**
     * IP黑名单列表
     */
    public function ipBlackListAction() {
        $model = new IpBlackListModel();
        $limit = array();
        if ($this->pagesize !== NULL) {
            $limit['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limit['page'] = $this->pagenum;
        }
        return $model->getList($this->crid, $this->k, $this->sortmode, $limit);
    }

    /**
     * 添加IP黑名单
     */
    public function addIpBlackAction() {
        $model = new IpBlackListModel();
        $ip = ip2long($this->ip);
        return $model->add($ip, strval($this->addr), strval($this->remark), $this->crid, $this->uid);
    }

    /**
     * 校验IP是否已经存在于黑名单
     */
    public function ipIsExistsAction(){
        $model = new IpBlackListModel();
        $ip = ip2long($this->ip);
        return $model->ipIsExists($ip,$this->crid);
    }

    /**
     * 校验用户名是否已经存在于黑名单
     */
    public function userIsExistsAction(){
        $model = new UserBlackListModel();
        return $model->userIsExists($this->username,$this->crid);
    }

    /*
     * 删除黑名单
     */
    public function deleteIpBlackAction() {
        $model = new IpBlackListModel();
        return $model->remove($this->crid, $this->ips);
    }

    /**
     * 添加用户黑名单
     */
    public function addUserBlackAction() {
        $userModel = new UserModel();
        $user = $userModel->getUserByUsername($this->username);
        if (empty($user)) {
            return -1;
        }
        $model = new UserBlackListModel();
        return $model->add($user['uid'], $this->username, $this->remark, $this->crid, $this->operid);
    }

    /**
     * 用户黑名单列表
     */
    public function userBlackListAction() {
        $model = new UserBlackListModel();
        $limit = array();
        if ($this->pagesize !== NULL) {
            $limit['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limit['page'] = $this->pagenum;
        }
        $ret = $model->getList($this->crid, $this->k, $this->sortmode, $limit, false);
        if (!empty($ret)) {
            $uidArr = array_column($ret, 'uid');
            $userModel = new UserModel();
            $userArr = $userModel->getUserByUids(implode(',', $uidArr));
            array_walk($ret, function(&$v, $k, $users) {
                $v['realname'] = $users[$v['uid']]['realname'];
                $v['face'] = $users[$v['uid']]['face'];
                $v['sex'] = $users[$v['uid']]['sex'];
                $v['groupid'] = $users[$v['uid']]['groupid'];
            }, $userArr);
        }
        return $ret;
    }

    /**
     * 用户黑名单统计
     */
    public function userBlackListCountAction() {
        $model = new UserBlackListModel();
        return $model->getCount($this->crid, $this->k);
    }

    /**
     * 删除用户黑名单
     * @return int
     */
    public function deleteUserBlackAction() {
        $model = new UserBlackListModel();
        return $model->remove($this->crid, $this->uids);
    }

    /*
     * 关键词过滤统计
     */
    public function filtersCountAction() {
        $model = new FiltersModel();
        return $model->getCount($this->crid, $this->k);
    }

    /**
     * 关键词过滤列表
     */
    public function filtersListAction() {
        $model = new FiltersModel();
        $limit = array();
        if ($this->pagesize !== NULL) {
            $limit['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limit['page'] = $this->pagenum;
        }
        return $model->getList($this->crid, $this->k, $this->sortmode, $limit);
    }

    /**
     * 删除关键词过滤
     */
    public function deleteFilterAction() {
        $model = new FiltersModel();
        return $model->remove($this->crid, $this->fids);
    }

    /**
     * 添加关键词过滤
     */
    public function addFiltersAction() {
        $model = new FiltersModel();
        return $model->add($this->keyword, $this->replace, $this->crid, $this->uid);
    }

    /**
     * 取消用户登录限制
     * @return int
     */
    public function deleteUserClientAction() {
        $model = new UserClientModel();
        return $model->remove($this->crid, $this->uids);
    }

    /**
     * 站点导航
     */
    public function navigatorAction() {
        $model = new ClassRoomModel();
        $ret = $model->getNavigator($this->crid);
        $ret = unserialize($ret);
        if ($ret === FALSE) {
            return FALSE;
        }
        return $ret['navarr'];
    }
	
	/*
	获取自定义富文本
	*/
	public function getCustomMessageAction(){
		$model = new ClassRoomModel();
		return $model->getCustomMessage(array('crid'=>$this->crid,'index'=>$this->index));
	}
	
	/*
	保存自定义富文本
	*/
	public function saveCustomMessageAction(){
		$model = new ClassRoomModel();
		return $model->saveCustomMessage(array('crid'=>$this->crid,'index'=>$this->index,'custommessage'=>$this->custommessage));
	}
	
    /**
     * [获取所有课程的ID]
     * @return [array] [folderid 数组]
     */
    public function getFolderIdAction(){
        $model = new FolderModel();
        $folderidList = $model->getAllFolderId($this->crid);
        return $folderidList ? $folderidList : false;
    }

    /**
     * [getModuleAction 获取当前网校的已启用的模块]
     * @return [array] 
     */
    public function getModuleAction(){
        $appModel = new AppmoduleModel();
        $model = new RoomModuleModel();
        $modules = $appModel->getSimpleList(false, 1);
        $moduleSet = $model->getList($this->crid, 2);
        if (!empty($moduleSet)) {
            foreach ($moduleSet as $moduleid => $module) {
                $modules[$moduleid] = $module;
            }
        }
        if (!empty($modules)) {
            $modules = array_filter($modules, function($item) {
               return in_array($item['modulecode'],
                   array('kpan', 'xuanke', 'health', 'forum', 'activity', 'weixin', 'selectcourse', 'survey', 'survey'));
            });
        }
        $moduleList = array_column($modules, 'modulecode');
        //$moduleList = $model->getModuleList($this->crid);
        return $moduleList ? $moduleList : false;
    }

    /**
     * 读取管理员菜单
     */
    public function adminMenusAction() {
        $params = array(
            'crid' => $this->crid,
            'roomtype' => $this->roomtype
        );
        $model = new MenuModel();
        $ret = $model->getMenuList($params);
        if ($this->uid > 0 && !empty($ret)) {
            $teacherRoleModel = new TeacherRoleModel();
            $role = $teacherRoleModel->getTeacherRole($this->uid, $this->crid);
            if (empty($role) || !is_array($role)) {
                $this->setMenuArr($ret);
                return $ret;
            }
            $permissions = json_decode($role['permissions'], true);
            if (empty($permissions) || !is_array($permissions)) {
                $this->setMenuArr($ret);
                return $ret;
            }
            array_walk($ret, function(&$item, $k, $permissions) {
                if (!empty($item['status']) && in_array($item['mid'], $permissions)) {
                    $item['status'] = 1;
                } else {
                    $item['status'] = 0;
                }
                if (empty($item['children'])) {
                    return;
                }
                array_walk($item['children'], function(&$item, $k, $permissions) {
                    if (!empty($item['status']) && in_array($item['mid'], $permissions)) {
                        $item['status'] = 1;
                    }else {
                        $item['status'] = 0;
                    }
                }, $permissions);
            }, $permissions);
        }
        $this->setMenuArr($ret);
        return $ret;
    }

    private function setMenuArr(&$ret) {
        //过滤隐藏的菜单
        array_walk($ret, function(&$item) {
            if (empty($item['children'])) {
                return;
            }
            $children = array_filter($item['children'], function($child) {
                return !empty($child['status']);
            });
            $item['children'] = array_values($children);
        });
        $ret = array_filter($ret, function($item) {
            return !empty($item['status']) && empty($item['children']);
        });
        $ret = array_values($ret);
    }
}
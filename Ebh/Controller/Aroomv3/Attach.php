<?php
/**
 * User: ckx
 * Date: 2018/1/3
 */
class AttachController extends Controller {
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules() {
        return array(
            //添加附件
            'addAction' => array(
                'file_server' => array(
                    'name' => 'file_server',
                    'type' => 'string'
                ),
				'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
				'uid' => array(
                    'name' => 'uid',
                    'require' => true,
                    'type' => 'int'
                ),
                'file_name' => array(
                    'name' => 'file_name',
                    'type' => 'string'
                ),
                'file_url' => array(
                    'name' => 'file_url',
                    'type' => 'string'
                ),
                'file_md5' => array(
                    'name' => 'file_md5',
                    'type' => 'string'
                ),
                'file_size' => array(
                    'name' => 'file_size',
                    'type' => 'int'
                ),
				'message' => array(
					'name' => 'message',
					'type' => 'string'
				)
            ),
            'ListAction' => array(
				'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
				'attid' => array(
                    'name' => 'attid',
                    'type' => 'string'
                )
			)
        );
    }

    /**
     * 添加附件
     */
    public function AddAction() {
		$attid = 0;
        if (!empty($this->file_name) && !empty($this->file_size)){
			$nameArr = explode('.', $this->file_name);
			if (count($nameArr) > 0) {
				$suffix = end($nameArr);
			} else {
				$suffix = '';
			}
            $attachmentModel = new AttachmentModel();
            $attid = $attachmentModel->add($this->uid, $this->crid, array(
                'checksum' => $this->file_md5,
                'title' => $this->file_name,
                'message' => $this->message,
                'source' => $this->file_server,
                'url' => $this->file_url,
                'filename' => $this->file_name,
                'size' => $this->file_size,
                'suffix' => $suffix
            ));
			
        }
		return $attid;
    }
	
	/**
     * 根据id获取多个附件
	 */
	public function ListAction(){
		$attrModel = new AttachmentModel();
		$list = $attrModel->getMultiAttachByAttid($this->attid,$this->crid);
		return $list;
	}
    
}
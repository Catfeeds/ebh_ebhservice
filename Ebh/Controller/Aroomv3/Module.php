<?php

/**
 * 网校模块配置
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/18
 * Time: 10:05
 */
class ModuleController extends Controller
{
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules()
    {
        return array(
            //模块
            'modulesAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'tor' => array(
                    'name' => 'tor',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //模块配置
            'updateAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'tor' => array(
                    'name' => 'tor',
                    'require' => true,
                    'type' => 'int'
                ),
                'data' => array(
                    'name' => 'data',
                    'require' => true,
                    'type' => 'array'
                )
            ),
			'necessaryModuleAction'=>array(
				'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
			)
        );
    }

    /**
     * 模块
     * @return mixed
     */
    public function modulesAction()
    {
        $app_module_model = new AppmoduleModel();
        $room_module_model = new RoomModuleModel();
        $modules = $app_module_model->getSimpleList($this->tor, 1);
        $room_modules = $room_module_model->getList($this->crid, $this->tor);
        foreach ($room_modules as $moduleid => $module) {
            $modules[$moduleid] = $module;
        }
        array_walk($modules, function(&$module, $mid, $crid) {
            if (!empty($module['url'])) {
                $module['url'] = str_replace('[crid]', $crid, $module['url']);
            }
        }, $this->crid);
        $more_modules = array_filter($modules, function($inner_module) {
            return !empty($inner_module['ismore']) && $inner_module['modulecode'] != 'more';
        });

        $modules = array_diff_key($modules, $more_modules);
        $displayorders = array_column($modules, 'displayorder');
        $more_module_key = null;
        $more_index = 0;
        foreach ($modules as $k => $module_item) {
            if ($module_item['modulecode'] == 'more') {
                $more_module_key = $k;
                break;
            }
            $more_index++;
        }

        if ($this->tor == 0) {
            if ($more_module_key !== NULL) {
                $more_displayorder = array_column($more_modules, 'displayorder');
                array_multisort($more_displayorder, SORT_ASC, SORT_NUMERIC, $more_modules);
                $modules[$more_module_key]['children'] = $more_modules;
                $max_displayorder = max($displayorders);
                $max_displayorder_count = array_count_values($displayorders);
                if ($max_displayorder_count > 1 && $modules[$more_module_key]['displayorder'] != $max_displayorder) {
                    $modules[$more_module_key]['displayorder'] = $max_displayorder + 1;
                    $displayorders[$more_index] = $modules[$more_module_key]['displayorder'];
                }
                unset($more_displayorder);
            }
        } else {
            if (!empty($more_module_key)) {
                unset($room_modules[$more_module_key]);
            }
        }
        unset($more_modules);
        if (empty($modules)) {
            return array();
        }
        if (!empty($modules)) {
            $moduleid_arr = array_keys($modules);
        }
        array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
            $moduleid_arr, SORT_DESC, SORT_NUMERIC, $modules);
        return $modules;
    }

    public function updateAction() {
        $appModuleModel = new AppmoduleModel();
        $roomModuleModel = new RoomModuleModel();
        $appModules = $appModuleModel->getSimpleList($this->tor);
        //验证数据是否合法
        $moduleIdArr = array_column($this->data, 'moduleid');
        $moreKey = null;
        foreach ($this->data as $k => $more) {
            if ($more['modulecode'] == 'more') {
                $moreKey = $k;
                if (!empty($more['children'])) {
                    $childModuleidArr = array_column($more['children'], 'moduleid');
                    $moduleIdArr = array_merge($moduleIdArr, $childModuleidArr);
                }
                break;
            }
        }
        $moduleIdArr = array_flip($moduleIdArr);
        $appModuleIdArr = array_flip(array_keys($appModules));
        //非法的模块ID
        $invalidModuleIdArr = array_diff_key($moduleIdArr, $appModuleIdArr);
        if (!empty($invalidModuleIdArr)) {
            return false;
        }
        if ($moreKey !== NULL && !empty($this->data[$moreKey]['children'])) {
            $this->data = array_merge($this->data, $this->data[$moreKey]['children']);
            unset($this->data[$moreKey]['children']);
        }
        array_walk($this->data, function(&$v, $k) {
            $v['displayorder'] = $k;
        });
        Ebh()->db->begin_trans();
        foreach ($this->data as $sql) {
            $status = $roomModuleModel->setModules($this->crid, $this->tor, $sql);
            if ($status === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }
        Ebh()->db->commit_trans();
        return true;
    }
	
	/*
	 *需要显示的特定页面模块
	*/
	public function necessaryModuleAction(){
		$ammodel = new AppmoduleModel();
		$list = $ammodel->getstudentmodule(array('crid'=>$this->crid,'tors'=>1,'available'=>1,'showmode'=>1));
		return $list;
	}
}
<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:45
 */
class PackageController extends Controller{
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
            ),
            'schsourceAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
            )
        );
    }

    /**
     * 获取网校服务包和服务包分类列表
     */
    public function listAction(){
        $payPackageModel = new PaypackageModel();

        $packageList = $payPackageModel->getPackageList(array('crid'=>$this->crid,'status'=>1,'displayorder'=>'itype asc,displayorder asc,pid desc','limit'=>'0,1000'));
        $pids = array();
        $result = array();
        if(!empty($packageList)){
            foreach ($packageList as $package){
                $pids[] = $package['pid'];
                $result[$package['pid']] = array(
                    'pid'   =>  $package['pid'],
                    'pname' =>  $package['pname'],
                    'son'   =>  array()
                );
            }

            $paySortModel = new PaysortModel();
            $pids = implode(',',$pids);
            $paySortList = $paySortModel->getSortsByPidsInItems(array('pids'=>$pids,'crid'=>$this->crid,'order'=>'sdisplayorder asc,sid asc'));

            if(!empty($paySortList)){
                foreach ($paySortList as $paySort){
                    if(isset($result[$paySort['pid']])){
                        $result[$paySort['pid']]['son'][] = $paySort;
                    }
                }
            }


        }
        return returnData(1,'',array_values($result));

    }

    /**
     * 获取企业选课分类
     */
    public function schsourceAction(){
        $payPackageModel = new PaypackageModel();

        $packageList = $payPackageModel->getSchSourcePackageList(array('crid'=>$this->crid,'status'=>1,'displayorder'=>'itype asc,displayorder asc,pid desc','limit'=>'0,1000'));
        $pids = array();
        $result = array();
        if(!empty($packageList)){
            foreach ($packageList as $package){
                $pids[] = $package['pid'];
                $result[$package['pid']] = array(
                    'pid'   =>  $package['pid'],
                    'pname' =>  $package['pname'],
                    'son'   =>  array()
                );
            }
            $paySortModel = new PaysortModel();
            $pids = implode(',',$pids);
            $paySortList = $paySortModel->getSortsByPidsInItems(array('pids'=>$pids,'crid'=>$this->crid,'order'=>'sdisplayorder asc,sid asc'));

            if(!empty($paySortList)){
                foreach ($paySortList as $paySort){
                    if(isset($result[$paySort['pid']])){
                        $result[$paySort['pid']]['son'][] = $paySort;
                    }
                }
            }
        }


        return returnData(1,'',array_values($result));
    }
}
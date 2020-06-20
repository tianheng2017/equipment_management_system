<?php

namespace app\admin\controller\device;

use app\admin\model\Device;
use app\admin\model\DeviceLog;
use app\admin\model\SystemAdmin;
use app\admin\model\SystemProvince;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="Area")
 */
class Area extends AdminController
{
    protected $provinces;
    protected $map;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->provinces = SystemProvince::getProvinceList();
        $this->map = $this->device_where();
        $this->assign('provinces', $this->provinces);
    }

    
    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()){
            $data['data'] = Device::where($this->map)->field('id,name,x_coordinates as lng,y_coordinates as lat')->select();
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="弹窗")
     */
    public function pop()
    {
        $id = $this->request->get('id/d');
        $device = Device::where($this->map)->where('id', $id)->find();
        if (!empty($device)){
            $lastLog = DeviceLog::where('device_id', $device['device_id'])->order('id', 'desc')->limit(1)->find();
            $lastLog['name'] = $device['name'];
            $this->assign('data', $lastLog);
            return $this->fetch();
        }
        return false;
    }

    /**
     * @NodeAnotation(title="区域数据结构")
     */
    public function tree(){
        $provinces = SystemProvince::list_to_tree(SystemProvince::field('id,pid,name as title,zoom,center')->select()->toArray());
        foreach($provinces[0]['children'] as $k => $v){
            $devices = Device::where($this->map)->where('area_id', $v['id'])->select()->toArray();
            if (!empty($devices)){
                $provinces[0]['children'][$k]['title'] = $provinces[0]['children'][$k]['title'].'（'.count($devices).'）';
                foreach ($devices as $a => $b){
                    $b['title'] = $b['name'];
                    unset($b['name']);
                    $b['id'] = 's'.$b['id'];
                    $provinces[0]['children'][$k]['children'][] = $b;
                }
            }
        }
        return json($provinces);
    }
}
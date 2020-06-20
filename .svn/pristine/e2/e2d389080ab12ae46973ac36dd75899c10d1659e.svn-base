<?php

namespace app\admin\controller\device;

use app\admin\model\Device;
use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="device_log")
 */
class Log extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\DeviceLog();
        
        $this->assign('getDeviceList', $this->model->getDeviceList());

    }

    
    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFieds')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $map = $this->deviceLog_where();
            $count = $this->model
                ->withJoin('device', 'LEFT')
                ->where($map)
                ->where($where)
                ->count();
            $list = $this->model
                ->withJoin('device', 'LEFT')
                ->where($map)
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }
}
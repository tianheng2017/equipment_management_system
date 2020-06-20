<?php

namespace app\common\controller;


use app\admin\model\Device;
use app\admin\model\SystemAdmin;
use app\BaseController;
use EasyAdmin\tool\CommonTool;
use think\facade\Env;
use think\Model;

/**
 * Class AdminController
 * @package app\common\controller
 */
class AdminController extends BaseController
{

    use \app\common\traits\JumpTrait;

    /**
     * 当前模型
     * @Model
     * @var object
     */
    protected $model;

    /**
     * 字段排序
     * @var array
     */
    protected $sort = [
        'id' => 'desc',
    ];

    /**
     * 允许修改的字段
     * @var array
     */
    protected $allowModifyFileds = [
        'status',
        'sort',
        'remark',
        'is_delete',
        'is_auth',
        'title',
    ];

    /**
     * 不导出的字段信息
     * @var array
     */
    protected $noExportFileds = ['delete_time', 'update_time'];

    /**
     * 下拉选择条件
     * @var array
     */
    protected $selectWhere = [];

    /**
     * 是否关联查询
     * @var bool
     */
    protected $relationSerach = false;

    /**
     * 模板布局, false取消
     * @var string|bool
     */
    protected $layout = 'layout/default';

    /**
     * 用户数据
     */
    protected $user;

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        $this->layout && $this->app->view->engine()->layout($this->layout);
        $this->user = SystemAdmin::where('id', session('admin.id'))->find();
    }

    /**
     * 设备约束
     */
    public function device_where()
    {
        if ((int)$this->user['auth_ids'] == 1){
            $map[] = ['uid', '>', 0];
        }elseif((int)$this->user['auth_ids'] == 2){
            //代理商获取所有下级
            $ids = SystemAdmin::where('pid', $this->user['id'])->column('id');
            $map[] = ['uid', 'in', $ids];

        }else{
            $map[] = ['uid', '=', $this->user['id']];
        }
        return $map;
    }

    /**
     * 用户管理约束
     */
    public function puser_where()
    {
        if ((int)$this->user['auth_ids'] == 1){
            $map[] = ['pid', '>=', 0];
        }else{
            $map[] = ['pid', '=', $this->user['id']];
        }
        return $map;
    }

    /**
     * 设备日志约束
     */
    public function deviceLog_where()
    {
        //约束代理只能查看自己下级设备的日志，用户只能查看自己设备的日志
        if ($this->user['auth_ids'] == 2){
            $uids = SystemAdmin::where('pid', $this->user['id'])->column('id');
            $device_ids = Device::whereIn('uid', $uids)->column('device_id');
            $map[] = ['device.device_id', 'in', $device_ids];
        }elseif($this->user['auth_ids'] == 3){
            $device_ids = Device::where('uid', $this->user['id'])->column('device_id');
            $map[] = ['device.device_id', 'in', $device_ids];
        }else{
            $map[] = ['device.device_id', '<>', ''];
        }
        return $map;
    }

    /**
     * 模板变量赋值
     * @param string|array $name 模板变量
     * @param mixed $value 变量值
     * @return mixed
     */
    public function assign($name, $value = null)
    {
        return $this->app->view->assign($name, $value);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    public function fetch($template = '', $vars = [])
    {
        return $this->app->view->fetch($template, $vars);
    }

    /**
     * 重写验证规则
     * @param array $data
     * @param array|string $validate
     * @param array $message
     * @param bool $batch
     * @return array|bool|string|true
     */
    public function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        try {
            parent::validate($data, $validate, $message, $batch);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return true;
    }

    /**
     * 构建请求参数
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function buildTableParames($excludeFields = [])
    {
        $get = $this->request->get('', null, null);
        $page = isset($get['page']) && !empty($get['page']) ? $get['page'] : 1;
        $limit = isset($get['limit']) && !empty($get['limit']) ? $get['limit'] : 15;
        $filters = isset($get['filter']) && !empty($get['filter']) ? $get['filter'] : '{}';
        $ops = isset($get['op']) && !empty($get['op']) ? $get['op'] : '{}';
        // json转数组
        $filters = json_decode($filters, true);
        $ops = json_decode($ops, true);
        $where = [];
        $excludes = [];

        // 判断是否关联查询
        $tableName = CommonTool::humpToLine(lcfirst($this->model->getName()));

        foreach ($filters as $key => $val) {
            if (in_array($key, $excludeFields)) {
                $excludes[$key] = $val;
                continue;
            }
            if ($this->relationSerach && count(explode('.', $key)) == 1) {
                $key = "{$tableName}.{$key}";
            }
            $op = isset($ops[$key]) && !empty($ops[$key]) ? $ops[$key] : '%*%';
            switch (strtolower($op)) {
                case '=':
                    $where[] = [$key, '=', $val];
                    break;
                case '%*%':
                    $where[] = [$key, 'LIKE', "%{$val}%"];
                    break;
                case '*%':
                    $where[] = [$key, 'LIKE', "{$val}%"];
                    break;
                case '%*':
                    $where[] = [$key, 'LIKE', "%{$val}"];
                    break;
                case 'range':
                    [$beginTime, $endTime] = explode(' - ', $val);
                    $where[] = [$key, '>=', strtotime($beginTime)];
                    $where[] = [$key, '<=', strtotime($endTime)];
                    break;
                default:
                    $where[] = [$key, $op, "%{$val}"];
            }
        }
        return [$page, $limit, $where, $excludes];
    }

    /**
     * 下拉选择列表
     * @return \think\response\Json
     */
    public function selectList()
    {
        $fields = input('selectFieds');
        $data = $this->model
            ->where($this->selectWhere)
            ->field($fields)
            ->select();
        $this->success(null, $data);
    }

}
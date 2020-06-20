<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class DeviceLog extends TimeModel
{

    protected $name = "device_log";

    protected $deleteTime = 'delete_time';

    
    public function device()
    {
        return $this->belongsTo('\app\admin\model\Device', 'device_id', 'device_id');
    }

    
    public function getDeviceList()
    {
        return \app\admin\model\Device::column('name', 'id');
    }

}
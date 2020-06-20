<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class Device extends TimeModel
{

    protected $name = "device";

    protected $deleteTime = 'delete_time';


    
    public function systemProvince()
    {
        return $this->belongsTo('\app\admin\model\SystemProvince', 'area_id', 'id');
    }

    public function systemAdmin()
    {
        return $this->belongsTo('\app\admin\model\SystemAdmin', 'uid', 'id');
    }

    
    public function getSystemProvinceList()
    {
        return \app\admin\model\SystemProvince::column('name', 'id');
    }

    public static function onAfterDelete($device)
    {
        DeviceLog::destroy(function($query) use($device){
            $query->where('device_id','=',$device->device_id);
        });
    }

}
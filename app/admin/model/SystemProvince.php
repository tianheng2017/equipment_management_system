<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class SystemProvince extends TimeModel
{

    protected $name = "system_province";

    protected $deleteTime = false;

    public static function getProvinceList(){
        return self::column('*','id');
    }

    /**
     * list_to_tree
     */
    public static function list_to_tree($list, $pk = 'id', $sid = 'pid', $child = 'children', $root = 0)
    {
        $tree = array();
        if (is_array($list)) {
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                $parentid = $data[$sid];
                if ($parentid == $root) {
                    unset($list[$key]['pid']);
                    $tree[] =& $list[$key];
                    $list[$key]['spread'] = true;
                } else {
                    if (isset($refer[$parentid])) {
                        $parent =& $refer[$parentid];
                        unset($list[$key]['pid']);
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

}
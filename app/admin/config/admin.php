<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

return [

    // 不需要验证登录的控制器
    'no_login_controller' => [
        'login',
    ],

    // 不需要验证登录的节点
    'no_login_node'       => [
        'login/index',
        'login/out',
        'login/register',
        'login/findPassword',
        'login/sendCode',
        'login/reset',
    ],

    // 不需要验证权限的控制器
    'no_auth_controller'  => [
        'ajax',
        'login',
        'index',
    ],

    // 不需要验证权限的节点
    'no_auth_node'        => [
        'login/index',
        'login/out',
        'login/register',
        'login/findPassword',
        'login/sendCode',
        'login/reset',
        'device.area/pop',
        'device.area/tree',
    ],
];
define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'device/index',
        add_url: 'device/add',
        edit_url: 'device/edit',
        delete_url: 'device/delete',
        export_url: 'device/export',
        modify_url: 'device/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'name', title: '设备名称'},
                    {field: 'device_id', title: '设备编码'},
                    {field: 'systemAdmin.username', title: '设备所有人'},
                    {field: 'systemProvince.name', title: '区域名称'},
                    {field: 'x_coordinates', title: '经度'},
                    {field: 'y_coordinates', title: '纬度'},
                    {width: 250, title: '操作', templet: ea.table.tool},

                ]],
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
    };
    return Controller;
});
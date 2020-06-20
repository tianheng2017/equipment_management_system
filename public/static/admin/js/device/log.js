define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'device.log/index',
        add_url: 'device.log/add',
        edit_url: 'device.log/edit',
        delete_url: 'device.log/delete',
        export_url: 'device.log/export',
        modify_url: 'device.log/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'ID'},
                    {field: 'device.name', title: '设备名称'},
                    {field: 'device_id', title: '设备ID'},
                    {field: 'rem_host', title: '设备IP'},
                    {field: 'voltage', title: '电压'},
                    {field: 'current', title: '电流'},
                    {field: 'temperature', title: '温度'},
                    {field: 'humidity', title: '湿度'},
                    {field: 'lampblack', title: '油烟'},
                    {field: 'hydrocarbon', title: '非甲烷总烃'},
                    {field: 'pm2p5', title: 'PM2.5'},
                    {field: 'time', title: '记录时间'},
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
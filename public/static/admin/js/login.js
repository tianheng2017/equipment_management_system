define(["easy-admin"], function (ea) {
    var Controller = {
        index: function () {
            if (top.location !== self.location) {
                top.location = self.location;
            }
            $('.bind-password').on('click', function () {
                if ($(this).hasClass('icon-5')) {
                    $(this).removeClass('icon-5');
                    $("input[name='password']").attr('type', 'password');
                } else {
                    $(this).addClass('icon-5');
                    $("input[name='password']").attr('type', 'text');
                }
            });
            $('.icon-nocheck').on('click', function () {
                if ($(this).hasClass('icon-check')) {
                    $(this).removeClass('icon-check');
                } else {
                    $(this).addClass('icon-check');
                }
            });
            //解决非要点击框框才能选中问题
            $('.login-tip').on('click', function () {
                $('.icon-nocheck').click();
            });
            ea.listen(function (data) {
                data['keep_login'] = $('.icon-nocheck').hasClass('icon-check') ? 1 : 0;
                return data;
            }, function (res) {
                ea.msg.success(res.msg, function () {
                    window.location = ea.url('index');
                });
            }, function (res) {
                ea.msg.error(res.msg, function () {
                    $('#refreshCaptcha').trigger("click");
                });
            });
        },
        register: function () {
            $('.send-code').on('click', function () {
                var username = $("input[name='username']").val();
                if (!username){
                    ea.msg.error('邮箱不能为空');
                    return;
                }
                //对电子邮件的验证
                var regemail = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
                if(!regemail.test(username)){
                    ea.msg.error('邮箱格式不正确');
                    return;
                }
                ea.request.post({
                    url: 'sendCode',
                    data: {'username': username},
                }, function (res) {
                    $('.send-code').css('background-color','#ccc');
                    ea.msg.success(res.msg);
                    settime();
                });
            });
            var countdown = 60;
            function settime() {
                if (countdown == 0) {
                    $('.send-code').attr("disabled", false);
                    $('.send-code').text("获取验证码");
                    countdown = 60;
                    return;
                } else {
                    $('.send-code').attr("disabled", true);
                    $('.send-code').css("width", "80px");
                    $('.send-code').text(countdown + ' S');
                    countdown--;
                }
                setTimeout(function() {
                    settime();
                },1000);
            }
            ea.listen(function (data) {
                return data;
            },function (res) {
                ea.msg.success(res.msg,function () {
                    window.location = ea.url('index');
                });
            });
        },
        findPassword: function () {
            ea.listen(function (data) {
                return data;
            }, function (res) {
                $('.center').hide();
                $('.layui-form-item').hide();
                $('#result').show();
                $('#result').html(res.msg);
            }, function (res) {
                ea.msg.error(res.msg);
            });
        },
        reset: function () {
            ea.listen(function (data) {
                return data;
            }, function (res) {
                ea.msg.success(res.msg,function () {
                    window.location = ea.url('index');
                });
            }, function (res) {
                ea.msg.error(res.msg);
            });
        }
    };
    return Controller;
});
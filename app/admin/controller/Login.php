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

namespace app\admin\controller;


use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Env;

/**
 * Class Login
 * @package app\admin\controller
 */
class Login extends AdminController
{

    /**
     * 初始化方法
     */
    public function initialize()
    {
        parent::initialize();
        $action = $this->request->action();
        if (!empty(session('admin')) && !in_array($action, ['out'])) {
            $adminModuleName = config('app.admin_alias_name');
            $this->redirect(__url("@{$adminModuleName}"));
        }
        $this->assign('is_mobile', $this->request->isMobile() ? 1 : 0);
    }

    /**
     * 用户登录
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $captcha = Env::get('manage.captcha', 1);
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'username|邮箱'           => 'require|email',
                'password|密码'           => 'require',
                'keep_login|是否保持登录'  => 'require',
            ];
            $captcha == 1 && $rule['captcha|验证码'] = 'require|captcha';
            $this->validate($post, $rule);
            $admin = SystemAdmin::where(['username' => $post['username']])->find();
            if (empty($admin)) {
                $this->error('用户不存在');
            }
            if (password($post['password']) != $admin->password) {
                $this->error('密码输入有误');
            }
            if ($admin->status == 0) {
                $this->error('账号已被禁用');
            }
            //登录次数+1
            $admin->login_num += 1;
            $admin->save();
            $admin = $admin->toArray();
            unset($admin['password']);
            $admin['expire_time'] = $post['keep_login'] == 1 ? true : time() + 7200;
            session('admin', $admin);
            $this->success('登录成功');
        }
        $this->assign('captcha', $captcha);
        return $this->fetch();
    }

    /**
     * 用户退出
     * @return mixed
     */
    public function out()
    {
        session('admin', null);
        $this->success('退出登录成功');
    }

    /**
     * 验证码
     * @return \think\Response
     */
    public function captcha()
    {
        return Captcha::create();
    }

    /**
     * 用户注册
     */
    public function register()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'username|邮箱'           => 'require|email',
                'code|邮箱验证码'         => 'require|length:6',
                'password|密码'           => 'require|min:6',
                'repassword|二次密码'     => 'require|confirm:password',
                'pid|推荐人ID'            => 'number|max:11',
            ];
            $this->validate($post, $rule);
            if (Cache::get($post['username']) <> $post['code']){
                $this->error('验证码不存在或已失效');
            }
            $user = SystemAdmin::where('username', $post['username'])->find();
            if (!empty($user)){
                $this->error('该邮箱已被注册');
            }
            if (!empty($post['pid'])) {
                $puser = SystemAdmin::where('id', $post['pid'])->find();
                if (empty($puser)) {
                    $this->error('推荐人不存在');
                }
            }
            //默认注册的为用户权限 authids=3
            $admin = SystemAdmin::create([
                'username'  =>  $post['username'],
                'password'  =>  password($post['password']),
                'head_img'  =>  '/static/admin/images/head.jpg',
                'login_num' =>  1,
                'auth_ids'  =>  3,
                'pid'       =>  $post['pid'] ?: 0,
            ]);
            $admin = $admin->toArray();
            unset($admin['password']);
            $admin['expire_time'] = time() + 7200;
            session('admin', $admin);
            Cache::delete($post['username']);
            $this->success('注册成功');
        }
        $pid = $this->request->param('uid/d');
        if (!empty($pid)){
            $this->assign('pid', $pid);
        }
        return $this->fetch();
    }

    /**
     * 找回密码
     */
    public function findPassword()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'username|邮箱' => 'require|email',
            ];
            $this->validate($post, $rule);
            $user = SystemAdmin::where('username', $post['username'])->find();
            if (empty($user)) {
                $this->error('账号不存在');
            }
            if (time() - $user->reset_time < 5 * 60){
                $this->error('距离系统上次发送不足5分钟，请稍后再试');
            }

            $token = md5($user->id.$user->username.$user->password);
            $url = $this->request->domain().'/admin/login/reset/username/'.$user->username.'/token/'.$token;
            $time = time();

            $title = "【找回密码】密码找回通知";
            $content = "尊敬的 ".$user->username."，";
            $content .= "您在 ".date('Y-m-d H:i:s', $time)." 提交了密码找回请求，请在1小时内点击下面的链接重置密码：<br><br>";
            $content .= $url;

            $res = sendEmail($title, $content, $user->username);
            if($res == 1){
                $user->reset_time = $time;
                $user->save();
                $this->success('系统已向您发送了一封邮件，请及时进行密码重置！');
            }else{
                $this->error('发送失败');
            }
        }
        return $this->fetch();
    }

    /**
     * 重置密码
     */
    public function reset()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'password|密码'           => 'require|min:6',
                'repassword|二次密码'     => 'require|confirm:password',
            ];
            $this->validate($post, $rule);
            $user = SystemAdmin::where('username', $post['username'])->find();
            if ($post['token'] <> md5($user->id.$user->username.$user->password)){
                $this->error('链接无效');
            }
            if (time() - 60 * 3600 > $user->reset_time){
                $this->error('该链接已过期，请重新操作！');
            }
            $res = SystemAdmin::where('username', $user->username)->update([
                'password'  =>  password($post['password'])
            ]);
            if (!empty($res)){
                $this->success('密码重置成功');
            }
            $this->error('密码重置失败');
        }
        $param = $this->request->route();
        if (empty($param['username']) || empty($param['token'])){
            $this->error('链接异常，请重新操作！','', __url('login/findPassword'));
        }
        $user = SystemAdmin::where('username', $param['username'])->find();
        if ($param['token'] <> md5($user->id.$user->username.$user->password)){
            $this->error('无效链接！','', __url('login/findPassword'));
        }
        if (time() - 60 * 3600 > $user->reset_time){
            $this->error('该链接已过期，请重新操作！','', __url('login/findPassword'));
        }
        $this->assign('data', $param);
        return $this->fetch();
    }

    /**
     * 发送邮箱验证码
     */
    public function sendCode()
    {
        $username = $this->request->post('username');
        $user = SystemAdmin::where('username', $username)->find();
        if (!empty($user)){
            $this->error('该邮箱已被注册');
        }
        if (Cache::get($username.'_remark')){
            $this->error('距离上次发送不足60秒，请稍后');
        }
        $code = getCode();
        Cache::set($username, $code, 1800);

        $title = "【验证码】账号注册通知";
        $content = "尊敬的：".$username."，您的验证码为：".$code.' ，有效期30分钟';
        $res = sendEmail($title, $content, $username);
        if($res == 1){
            Cache::set($username.'_remark', 1, 60);
            $this->success('发送成功');
        }else{
            $this->error('发送失败');
        }
    }
}
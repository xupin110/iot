<?php
namespace app\admin\controller;
use app\common\Common;
class Login extends Common
{

    public function index()
    {
        $this->assign([
            'title' => "登录后台-{$this->config['name']}"
        ]);
        return $this->fetch();
    }
    /**
     * 登录数据管理
     *
     * */
    public function doLogin()
    {
        $admin = model("Admin");
        //表单数据接收
        $user = input('post.username');#用户
        $password = input('post.password');#密码
        //登录逻辑处理
        if (!$user or !$password) {
            #用户名或者密码有空
            return json([
                'status' => 1,
                'msg' => '用户名或者密码不能留空!'
            ]);

        }
        //模型数据库查询
        $where['adminuser'] = $user;
        $userL = $admin->field('id,password,status,rands')->where($where)->find();
        if ($userL) {
            if ($userL['password'] == MD5(MD5($password.'QianWen').$userL['rands'])) {
                if ($userL['status'] == 0) {
                    session('admin_uid', $userL['id']);
                    //插入管理员登录记录
                    // model('AdminRecord')->insertRecord($userL['id']);
                    //$admin->getpri($userL['role_id']);
                    $return =array('status' => 0);
                }else {
                    $return = array('msg' => '该用户已被停用!', 'status' => 1);
                }
            }else {
                $return = array('msg' => '用户名或者密码有误!', 'status' => 1);
            }
        }else {
            $return = array('msg' => '用户名或者密码有误!', 'status' => 1);
        }        
        #返回ajax json数据
        return json($return);
    }


}


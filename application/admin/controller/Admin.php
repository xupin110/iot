<?php
namespace app\admin\controller;
class Admin extends Base
{
    /**
     * 管理员列表
     */
    public function index()
    {
        header("Content-Type: text/html; charset=utf-8");
        $admin = D("Admin");
        $admin_uid = session('admin_uid');
        if (empty($admin_uid)) {
            $this->out_login();
        }else{
            if ($admin_uid == 1) {
                if ($so=I('so')) {
                    $where.=' AND (adminuser LIKE "%'.$so.'%" OR mobile LIKE "%'.$so.'%")';
                    //$where['nickname'] =array('LIKE', "$so%");
                }
                $count = $admin->where($where)->count();
                #实例化think分页类
                $Page = new \Think\Page($count,15);
                $show       = $Page->show();// 分页显示输出
                $list = $admin->field('id,adminuser,regtime,name,mobile,status')
                              ->where($where)
                              ->order('id asc')->limit($Page->firstRow,$Page->listRows)->select();
            }else {
                $list[] = $admin->field('id,adminuser,regtime,name,mobile,status')
                              ->where(array('id' => $admin_uid))
                              ->order('id asc')->find();
            }
        }
        $this->assign(array(
            'list' => $list,
            'page' => $show,
            'title' => '管理员列表',
            'count' => $count
            ));
        return $this->fetch();
    }
    
    // 渲染模板
    public function add()
    {
        /**
         * 添加管理员
         */
        // $role = D("Role");
        // $where['role_id'] = array('neq', 1);
        // $roleres = $role->field('role_id,rolename')->where($where)->select();
        $this->assign(array(
            'title' => '添加管理员',
            'list' => $list
            ));
        $this->display();
    }
    // 执行添加
    public function do_add()
    {
        /**
         * 添加管理数据处理
         */
        $adinfo = M('admin')->where(['name' => I('post.name')])->find();
        if (I('post.pass') != I('post.pass1')) {
            #确定密码
            $redata = array(
                'status' => 1,
                'msg' => '两次密码输入密码不相符!'
            );
        } elseif ($adinfo) {
            $redata = array(
                'status' => 1,
                'msg' => '改用户名已存在，不能重复添加!'
            );
        }
        $rand = MD5(rand(000000,99999));
        $data = array(
            'name' => I('post.name'),
            'adminuser' => I('post.adminuser'),
            'password' => MD5(MD5(I('post.pass1').'QianWen').$rand),
            'regtime' => time(),
            'mobile' => I('post.mobile'),   
            'role_id' => I('post.role_id'),
            'rands' => $rand
        );
        if ($redata['status'] !== 1) {
            $result = M('Admin')->add($data);
            if ($result) {
                $redata = array(
                    'status' => 0,
                    'msg' => '添加成功'
                );
            } else {
                $redata = array(
                    'status' => 1,
                    'msg' => '添加失败'
                );
            }
        }
        $this->ajaxReturn($redata);
    }

    // 修改密码渲染模板
    public function edit_pass($id) {
        $this->assign(array(
            'title' => '修改密码',
            'id' => $id
            ));
        $this->display();
    }
    // 执行修改密码
    public function do_edit_pass()
    {
        /**
         *  验证当前密码
         * */
        if (IS_POST) {
            $where['id'] = I('post.id');
            $info = D("Admin")->field('password,rands')->where($where)->find();
            if ($info) {
                $password = I('post.password');
                $pass = I('pass');
                $pass1 = I('pass1');
                if ($info['password'] == MD5(MD5($password.'QianWen').$info['rands'])) {
                    if ($pass == $pass1 && $pass1 != NULL) {
                        $rand = MD5(rand(000000,99999));
                        $data['password'] = MD5(MD5($pass1.'QianWen').$rand);
                        $data['rands'] = $rand;
                        if (D("Admin")->where($where)->save($data)) {
                            $return = array('msg' => '修改成功', 'status' => 0);
                        }else {
                            $return = array('msg' => '修改失败', 'status' => 1);
                        }
                    }else {
                        $return = array('msg' => '两次密码输入密码不一至，或者为空', 'status' => 1);
                    }
                }else {
                    $return = array('msg' => '原密码错误', 'status' => 1);
                }
            }else {
                $return = array('msg' => '该用户不存在', 'status' => 1);
            }
            $this->ajaxReturn($return);
        }
    }
    
    /**
     * 修改资料模板渲染
     */
    public function edit($id) {
        $admin = D("Admin");

        $list = $admin->field('id,name,adminuser,mobile,status,role_id')->find($id);
        // $role = D("Role");
        // $roleres = $role->field('role_id,rolename')->select();
        $this->assign(array(
            'title' => '修改管理员',
            'list' => $list
            ));
        $this->display();
    }

    /**
     * 修改资料
     */
    public function do_edit()
    {
        $admin = D("Admin");
        if (IS_POST) {
            $data = array(
                'id' => I('post.id'),
                'name' => I('post.name'),
                'adminuser' => I('post.adminuser'),
                'mobile' => I('post.mobile'),   
                'role_id' => I('post.role_id'),
                'status' => I('post.status')
            );
            if ($admin->create($data)) {
                if ($admin->save()) {
                    //修改成功
                    $redata = array(
                        'status' => 0,
                        'msg' => '修改成功'
                    );
                }else {
                    //修改失败
                    $redata = array(
                        'status' => 1,
                        'msg' => '修改失败'
                    );
                }
            }else {
                //修改失败
                $redata = array(
                    'status' => 1,
                    'msg' => $admin->getError()
                );
            }
        }
        $this->ajaxReturn($redata);

    }

    

    /**
     * 删除管理员
     */
    public function del(){
        $admin=D('admin');
        if (IS_GET) {
            $id = I('id');
            if ($id == 1) {
                $this->ajaxReturn(array('msg' => '超级管理员不能删除！', 'status' => 1));
            }
            if($admin->where(['id' => $id])->save(['isdel' => 1])){
                $this->ajaxReturn(array('msg' => '删除管理员成功！', 'status' => 0));
            }else{
                $this->ajaxReturn(array('msg' => '删除管理员失败！', 'status' => 1));
            }
        }
    }
    /**
     * 启用管理员
     */
    public function Enabled($id) {
        $admin=D("Admin");
        if (IS_GET) {
            $where['id'] = $id;
            $data['status'] = 0;
            if ($admin->where($where)->save($data)) {
                $this->ajaxReturn(array('msg' => '管理员启用成功！', 'status' => 0));
            }else{
                $this->ajaxReturn(array('msg' => '管理员启用失败！', 'status' => 1));
            }
        }
    }
    /**
     * 停用管理员
     */
    public function disable($id) {
        $admin=D("Admin");
        if (IS_GET) {
            $where['id'] = $id;
            $data['status'] = 1;
            if ($id == 1) {
                $this->ajaxReturn(array('msg' => '超级管理员不能停用！', 'status' => 1));
                exit();
            }
            if ($admin->where($where)->save($data)) {
                $this->ajaxReturn(array('msg' => '管理员停用成功！', 'status' => 0));
            }else{
                $this->ajaxReturn(array('msg' => '管理员停用失败！', 'status' => 1));
            }
        }
    }

    /**
     *退出登录
     * */
    public function out_login()
    {
        session('admin_uid', null);
        header('location: ' . U('Login/index.html'));
    }
}
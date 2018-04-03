<?php
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        /**
         * 管理首页
         * */
        $memberCount = model('Member')->count();
        $orderCount = model('Order')->where(['c_status'=>1])->count();

        $this->assign([
            'title' => '管理首页',
            'memberCount' => $memberCount,
            'orderCount' => $orderCount
        ]);
        return $this->fetch();
    }
    /**
     * 管理员列表
     */
    public function lst() {
        $admin = D("Admin");
        $count = $admin->count();
        #实例化think分页类
        $Page = new \Think\Page($count,15);
        $show       = $Page->show();// 分页显示输出
        $list = $admin->field('a.id,a.nickname,a.lastime,a.name,a.login_time,a.c_status,b.name as school')->alias('a')->join('LEFT JOIN school_schools b ON a.relation_school=b.school_id')->order('id asc')->limit($Page->firstRow,$Page->listRows)->select();

        $this->assign(array(
            'list' => $list,
            'page' => $show,
            'title' => '管理员列表',
            'count' => $count
            ));
        return $this->fetch();
    }

    public function clear()
    {
        /**
         * 清除缓存
         * */
        $this->wxlin()->clearCache();
        delDir(RUNTIME_PATH . 'Temp');
        $this->tip('<i class=\"fa fa-check-square fa-2x\"></i> 清除成功缓存成功,跳转至首页···');
        $this->redirect('index/index', null, 2, ' ');
    }

    public function addadminuser()
    {
        /**
         * 添加管理员
         */
        $school = D("Schools");
        $list = $school->field('school_id,name')->order('school_id asc')->select();
        $this->assign(array(
            'title' => '添加管理员',
            'list' => $list
            ));
        return $this->fetch();
    }

    public function edpass()
    {
        /**
         * 修改当前密码
         * */
        $this->assign('title', '修改资料');
        return $this->fetch();
    }

    public function chekpwd()
    {
        /**
         *  验证当前密码
         * */
        $password = md5(I('post.pwd'));
        if ($password == $this->adminUser['pass']) {
            //密码验证成功
            $this->ajaxReturn([
                'sta' => true
            ]);
        }
        $this->ajaxReturn(array(
                'sta' => false,
                'tip' => '密码验证失败')
        );
    }

    public function doedpass()
    {
        /**
         * 修改密码数据处理
         * */
        if (I('post.pass') != I('post.pass1')) {
            #确定密码
            $redata = array(
                'sta' => false,
                'tip' => '两次密码输入密码不相符!'
            );
        }
        if ($redata['sta'] !== false) {

            $data = array(
                'nickname' => I('post.nickname'),
                'pass' => md5(I('post.pass')),
                'name' => I('post.name')
            );
            $sta = M('admin')->where(['id' => $this->adminUser['id']])->save($data);
            if ($sta !== false) {
                //修改成功
                $redata = array(
                    'sta' => true,
                    'tip' => '修改成功'
                );
            } else {
                //修改失败
                $redata = array(
                    'sta' => false,
                    'tip' => '修改失败'
                );
            }
        }
        $this->ajaxReturn($redata);

    }

    public function doadd()
    {
        /**
         * 添加管理数据处理
         */
        $adinfo = M('admin')->where(['name' => I('post.name')])->find();
        if (I('post.pass') != I('post.pass1')) {
            #确定密码
            $redata = array(
                'sta' => false,
                'tip' => '两次密码输入密码不相符!'
            );
        } elseif ($adinfo) {
            $redata = array(
                'sta' => false,
                'tip' => '改用户名已存在，不能重复添加!'
            );
        }

        $data = array(
            'name' => I('post.name'),
            'nickname' => I('post.nickname'),
            'pass' => md5(I('post.pass')),
            'sid' => md5(uniqid(time())),
            'lastime' => time(),
            'mobile' => I('post.mobile'),
            'relation_school' => I('post.relation_school')
        );
        if ($redata['sta'] !== false) {
            $result = M('admin')->add($data);
            if ($result) {
                $redata = array(
                    'sta' => true,
                    'tip' => '添加成功'
                );
            } else {
                $redata = array(
                    'sta' => false,
                    'tip' => '添加失败'
                );
            }
        }
        $this->ajaxReturn($redata);
    }

    public function out_login()
    {
        /**
         *退出登录
         * */
        setcookie('admin_sid', '', -1, '/');
        header('location: ' . U('login/index'));
    }
}

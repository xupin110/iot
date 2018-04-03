<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14
 * Time: 19:20
 */

namespace app\admin\controller;
use Home\Model\UserModel;

class User extends Base
{

    public function __construct()
    {
        parent::__construct();
        $this->userMod = D('User');
    }

    protected $sex = array('未知','女','男');
    

    public function index(){

        $so = I('so');
        if(!empty($so)){
            $where[0]['c_username'] = ['like',"%{$so}%"];
            $where[0]['c_tel'] = $so;
            $where[0]['c_id'] = $so;
            $where[0]['_logic'] = 'OR';
        }
        $count = $this->userMod->where($where)->count();
        $Page = new \Think\Page($count,15);
        $show = $Page->show();
        $list = $this->userMod->field('c_id,c_nickname,c_sex,c_pro,c_city,c_add_time')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('c_id desc')->select();

        $this->assign([
            'title' => '会员列表',
            'list' => $list,
            'page' => $show
            ]);
        $this->display();
    }

    /**
     * 获取用户个人信息
     */
    public function getInfo() 
    {
        if (IS_GET) {
            $where['c_id'] = ( int ) I('get.id');

            $info = $this->userMod->where($where)->find();
            if ($info) {
                $info['sex'] = $this->sex[$info['c_sex']];
                $info['c_add_time'] = date('Y-m-d H:i:s',$info['c_add_time']);
                $this->ajaxReturn(array(
                    'data' => $info,
                    'status' => 0
                    ));
            }else {
                $this->ajaxReturn(array(
                    'msg' => '没有数据',
                    'status' => 1
                    ));
            } 
        }
    }

    /**
     * 修改
     */
    public function edit($id)
    {
        //接收数据，回显页面
        $list = $this->userMod->field('c_id,c_username,c_sex,c_tel,c_address,c_staffid')->where(['c_id'=> $id])->find();

        $health = M("Staff")->where(['c_type' => 0])->field('c_id,c_name')->select();
        $this->assign([
            'title' => '修改资料',
            'list' => $list,
            'health' => $health
            ]);
        $this->display();
    }

    /**
     * 修改资料
     */
    public function do_edit()
    {
        $user = D('User');
        if (IS_POST) {
            $data = array(
                'c_id' => I('c_id'),
                'c_username' => I('post.c_username'),
                'c_sex' => I('post.c_sex'),
                'c_tel' => I('post.c_tel'),
                'c_address' => I('post.c_address'),
                'c_staffid' => I('post.c_staffid')
            );
            if ($user->create($data)) {
                if ($user->save()) {
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
                    'msg' => $user->getError()
                );
            }
        }
        $this->ajaxReturn($redata);
    }
    
    /**
     * 表格导出
     */
    public function msgOut()
    {
        header('Content-Type:text/html; charset=utf-8');
        $xlsCell = array(
            array('c_id', '会员ID'),
            array('c_username', '用户名'),
            array('c_tel', '手机号码'),
            array('c_address', '家庭地址'),
            array('c_level', '会员等级'),
            array('c_amount', '健康豆'),
            array('c_integral', '积分'),
            array('c_name', '健康师'),
            array('c_cashier_vip', '收银系统会员'),
            array('c_create_time', '创建时间'),
        );
        // 导出表格名称
        $xlsName = '会员表导出';
        $userMod = D("User");
        if (IS_GET) {
            $so = trim(I('get.so'));
            if (!empty($so)) {
                $where[0]['u.c_username'] = ['like',"%{$so}%"];
                $where[0]['u.c_tel'] = $so;
                $where[0]['u.c_id'] = $so;
                $where[0]['_logic'] = 'OR';
            }
            $xlsData = $userMod->alias('u')->field('u.c_id,u.c_username,u.c_tel,u.c_address,u.c_integral,u.c_create_time,u.c_level,u.c_cashier_vip,v.c_amount,s.c_name')
                ->join('left join __VIP_CARD__ v ON u.c_id=v.c_uid')
                ->join('left join __STAFF__ s ON u.c_staffid=s.c_id')
                ->where($where)->order('u.c_id asc')->select();
            if ($xlsData) {
                foreach ($xlsData as $kk => $vv) {
                    $xlsData[$kk]['c_create_time'] = date('Y-m-d H:i:s', $vv['c_create_time']);
                    $xlsData[$kk]['c_amount'] = $vv['c_amount'] ? $vv['c_amount'] : 0;
                    $xlsData[$kk]['c_level'] = $this->userModel->getlevel($vv['c_level']);
                    $xlsData[$kk]['c_name'] = $vv['c_name'] ? $vv['c_name'] : '无';
                    if ($vv['c_cashier_vip'] == 0) {
                        $xlsData[$kk]['c_cashier_vip'] = '否';
                    } else {
                        $xlsData[$kk]['c_cashier_vip'] = '是';
                    }
                }
            }
            $this->exportExcel($xlsName, $xlsCell, $xlsData);
        }
    }
    
}
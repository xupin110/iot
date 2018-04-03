<?php
namespace app\admin\controller;
//use Think\Controller;
class Privilege extends Base
{

    /**
     * 首页渲染
     */
	public function index() {
		$pri=D("Privilege");
        $list = $pri->pritree();
        $this->assign(array(
            'title' => '权限列表',
            'list' => $list
            ));
        return $this->fetch();
	}
	/**
     * 管理员权限添加页面渲染
     */
    public function add() {
        header("Content-Type: text/html; charset=utf-8");
        $pri=D("Privilege");
        $pris=$pri->field('id,pri_name,parent_id')->pritree();
        $this->assign(array(
            'pris' => $pris,
            'title' => '添加权限'
            ));
        $this->display();
    }
    /**
     * 管理员权限添加
     */
    public function do_add() {
        $pri=D("Privilege");
        if(IS_POST){
            $data = array(
                'pri_name' => I('pri_name'),
                'mname' => I('mname'),
                'cname' => I('cname'),
                'aname' => I('aname'),
                'parent_id' => I('parent_id')
                );
            if($pri->create($data)){
                if($pri->add()){
                    $return = array('msg' => '添加成功', 'status' => 0);
                }else{
                    $return = array('msg' => '添加成功', 'status' => 1);
                }
            }else{
                $return = array('msg' => $pri->getError(), 'status' => 1);
            }
            $this->ajaxReturn($return);
        }
    }
    /**
     * 管理员权限页面渲染
     */
    public function edit($id) {
        $pri=D("Privilege");
        $prires=$pri->find($id);
        $pris=$pri->pritree();
        $this->assign(array(
            'title' => '修改权限',
            'pris' => $pris,
            'prires' => $prires
            ));
        $this->display();
    }
    /**
     * 管理员权限修改
     */
    public function do_edit() {
        $pri=D("Privilege");
        if(IS_POST){
            $data = array(
                'id' => I('id'),
                'pri_name' => I('pri_name'),
                'mname' => I('mname'),
                'cname' => I('cname'),
                'aname' => I('aname'),
                'parent_id' => I('parent_id')
                );
            if($pri->create($data)){
                if($pri->save()){
                    $return = array('msg' => '修改成功', 'status' => 0);
                }else{
                    $return = array('msg' => '修改失败', 'status' => 1);
                }
            }else{
                $return = array('msg' => $pri->getError(), 'status' => 1);
            }
            $this->ajaxReturn($return);
        }
    }
    /**
     * 管理员权限删除
     */
    public function del(){
        $pri=D("Privilege");
        if (IS_GET) {
            $id = I('id');
            if($pri->delete($id)){
                $this->ajaxReturn(array('msg' => '删除成功！', 'status' => 0));
            }else{
                $this->ajaxReturn(array('msg' => '删除失败！', 'status' => 1));
            }
        }
    }

}





?>
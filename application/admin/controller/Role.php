<?php
namespace app\admin\controller;

class Role extends Base
{

	public function index() {
		$role=D("Role");
        $count=$role->count();// 查询满足要求的总记录数
        $Page=new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show=$Page->show();// 分页显示输出
        $list =$role->field('a.*,GROUP_CONCAT(b.pri_name) pri_name')->alias('a')->join('LEFT JOIN t_privilege b ON FIND_IN_SET(b.id,a.pri_id_list)')->limit($Page->firstRow.','.$Page->listRows)->group('a.role_id')->select();
        $this->assign([
        	'title' => '角色列表',
            'list' => $list,
            'page' => $show
            ]);// 赋值数据集
		$this->display();
	}
	/**
	 * 添加管理员角色
	 */
	public function add(){
		$role=D("Role");
		$pri=D("Privilege");
		$listpri=$pri->pritree();
		$this->assign([
			'title' => '添加管理员',
			'listpri' => $listpri
			]);
		$this->display();
	}
	public function do_add(){
		$role=D("Role");
		if (IS_POST) {
			$data = array(
				'rolename' => I('rolename'),
				'description' => I('description'),
				'pri_id_list' => implode(',', I('pri_id_list'))
				);
			if (empty($data['pri_id_list'])) {
				$return = array('msg' => '权限不得为空', 'status' => 1);
			}else {
				if ($role->create($data)) {
					if ($role->add()) {
						$return = array('msg' => '添加成功', 'status' => 0);
					}else{
						$return = array('msg' => '添加失败', 'status' => 1);
					}
				}else{
					$return = array('msg' => $role->getError(), 'status' => 1);
				}
			}
			$this->ajaxReturn($return);
		}
	}
	/**
	 * 修改管理员角色
	 */
	public function edit($id){
		$role=D("Role");
		$roleres=$role->where(['role_id' => $id])->find();
		$pri=D("Privilege");
		$listpri=$pri->pritree();
		$this->assign(array(
			'title' => '编辑角色',
			'listpri' => $listpri,
		    'roleres' => $roleres
		    ));
		$this->display();
	}
	/**
	 * 修改管理员角色
	 */
	public function do_edit(){
		$role=D("Role");
		if (IS_POST) {
			$data = array(
				'role_id' => I('id'),
				'rolename' => I('rolename'),
				'description' => I('description'),
				'pri_id_list' => implode(',', I('pri_id_list'))
				);
			if (empty($data['pri_id_list'])) {
				$return = array('msg' => '权限不得为空', 'status' => 1);
			}else {
				if ($role->create($data)) {
					if ($role->save()) {
						$return = array('msg' => '修改成功', 'status' => 0);
					}else{
						$return = array('msg' => '修改失败', 'status' => 1);
					}
				}else{
					$return = array('msg' => $role->getError(), 'status' => 1);
				}
		    }
			$this->ajaxReturn($return);
		}
	}
    /**
     * 删除管理员
     */
    public function del(){
        $role=D("Role");
        if (IS_GET) {
            $id = I('id');
            if ($id ==1) {
                $this->ajaxReturn(array('msg' => '超级管理员不能删除！', 'status' => 1));
            }
            if($role->where(['role_id' => $id])->delete()){
                $this->ajaxReturn(array('msg' => '删除成功！', 'status' => 0));
            }else{
                $this->ajaxReturn(array('msg' => '删除失败！', 'status' => 1));
            }
        }
    }
}



?>
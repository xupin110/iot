<?php
namespace app\admin\controller;



class DeviceShow extends Base
{

    public function __construct() {

    	parent::__construct();
    	$this->device = D("Deviceshow");
    }
    /**
     * 首页渲染
     */
	public function index() 
    {
        $where['c_isdel'] = 0;
		$count = $this->device->where($where)->count();
        #实例化think分页类
        $Page = new \Think\Page($count,15);
        $show       = $Page->show();// 分页显示输出
        $list = $this->device->where($where)
                ->order('c_id asc')
                ->limit($Page->firstRow,$Page->listRows)->select();
		$this->assign([
			'title' => '展示设备列表',
			'list' => $list,
			'page' => $show
			]);
		$this->display();
	}
 
    /**
     * 渲染添加页面
     */
	public function add() 
    {
		$this->assign([
			'title' => '添加展示设备'
			]);
		$this->display();
	}
    
	/**
	 * 执行添加
	 */
	public function doAdd() {
        if (IS_POST) {
            //接收数据
            $data = array(
                'c_name' => I('post.c_name'),
                'c_img' => I('post.c_img'),
                'c_kwh' => I('c_kwh'),
                'c_voltage' => I('post.c_voltage'),
                'c_inverter' => I('post.c_inverter'),
                'c_output' => I('post.c_output'),
                'c_interface' => I('post.c_interface'),
                'c_account_for' => I('post.c_account_for'),
                'c_add_time' => time()
                );
            if ($this->device->create($data)) {
            	if ($this->device->add()) {
	                $this->ajaxReturn([
	                    'msg' => '添加成功', 
	                    'status' => 0
	                    ]);
	            }else {
	                $this->ajaxReturn([
	                    'msg' => '添加失败', 
	                    'status' => 1
	                    ]);
	            }
            }
            $this->ajaxReturn([
                'msg' => $this->exch->getError(), 
                'status' => 1
                ]);
        }
	}

	/**
	 * 修改页面渲染
	 */
	public function edit($id) {

		$list = $this->device->where(['c_id' => $id])->find();
        //$company = $this->comp->field('c_id,c_shop_name')->select();

		$this->assign(array(
			'title' => '修改积分商品',
			'list' => $list,
            'company' => $company
			));
		$this->display();
	}

	/**
	 * 执行修改
	 */
	public function doEdit() {
        if (IS_POST) {
            //接收数据
            $data = array(
            	'c_id' => ( int ) I('post.c_id'),
                'c_name' => I('post.c_name'),
                'c_img' => I('post.c_img'),
                'c_kwh' => I('c_kwh'),
                'c_voltage' => I('post.c_voltage'),
                'c_inverter' => I('post.c_inverter'),
                'c_output' => I('post.c_output'),
                'c_interface' => I('post.c_interface'),
                'c_account_for' => I('post.c_account_for')
                );
            if ($this->device->create($data)) {
            	if ($this->device->save()) {
	                $this->ajaxReturn([
	                    'msg' => '修改成功', 
	                    'status' => 0
	                    ]);
	            }else {
	                $this->ajaxReturn([
	                    'msg' => '修改失败', 
	                    'status' => 1
	                    ]);
	            }
            }
            $this->ajaxReturn([
                'msg' => $this->device->getError(), 
                'status' => 1
                ]);
        }
	}
    
    /**
     * 状态修改
     */
    public function isStatus() {

        if (IS_GET) {
            $where['c_id'] = ( int ) I('get.id');
            $status = ( int ) $this->device->where($where)->getField('c_status');
            if ($status == 0) {
                $data['c_status'] = 1;
            } else {
                $data['c_status'] = 0;
            }
            if ($this->device->where($where)->save($data)) {
                $this->ajaxReturn([
                    'msg' => '修改成功', 
                    'status' => 0
                    ]);
            }
            $this->ajaxReturn([
                'msg' => '修改失败', 
                'status' => 1
                ]);
        }
    }

	/**
     * 删除
     */
    public function del()
    {
        if (IS_GET) {
            $id = ( int ) I('get.id');
            if($this->device->where(['c_id' => $id])->delete()) {
                $this->ajaxReturn(array(
                    'msg' => '删除成功！', 
                    'status' => 0
                    ));
            }else{
                $this->ajaxReturn(array(
                    'msg' => '删除失败！', 
                    'status' => 1
                    ));
            }
        }
    }


}




?>
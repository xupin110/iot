<?php
namespace app\admin\controller;

class Deviceshow extends Base {
	public $deviceShow;
	public function initialize() {

		$this->deviceShow = model("Deviceshow");
	}
	/**
	 * 首页渲染
	 */
	public function index() {
		$where['c_isdel'] = 0;
		$list = $this->deviceShow->where($where)
			->order('c_id asc')
			->paginate();

		return $this->fetch('', [
			'title' => '展示设备列表',
			'list' => $list,
		]);
	}

	/**
	 * 渲染添加页面
	 */
	public function add() {
		if (request()->isPost()) {
			//接收数据
			$data = array(
				'c_name' => input('post.c_name'),
				'c_img' => input('post.c_img'),
				'c_kwh' => input('c_kwh'),
				'c_voltage' => input('post.c_voltage'),
				'c_inverter' => input('post.c_inverter'),
				'c_output' => input('post.c_output'),
				'c_interface' => input('post.c_interface'),
				'c_account_for' => input('post.c_account_for'),
				'c_add_time' => time(),
			);

			if ($this->deviceShow->save($data)) {
				return json([
					'msg' => '添加成功',
					'status' => 0,
				]);
			} else {
				return json([
					'msg' => '添加失败',
					'status' => 1,
				]);
			}
			return json([
				'msg' => $this->exch->getError(),
				'status' => 1,
			]);
		}

		$this->assign([
			'title' => '添加展示设备',
		]);
		return $this->fetch();
	}

	/**
	 * 修改页面渲染
	 */
	public function edit() {
		if (request()->isPost()) {
			//接收数据
			$data = array(
				'c_id' => (int) input('post.c_id'),
				'c_name' => input('post.c_name'),
				'c_img' => input('post.c_img'),
				'c_kwh' => input('c_kwh'),
				'c_voltage' => input('post.c_voltage'),
				'c_inverter' => input('post.c_inverter'),
				'c_output' => input('post.c_output'),
				'c_interface' => input('post.c_interface'),
				'c_account_for' => input('post.c_account_for'),
			);
			if ($this->deviceShow->save($data)) {
				return json([
					'msg' => '修改成功',
					'status' => 0,
				]);
			} else {
				return json([
					'msg' => '修改失败',
					'status' => 1,
				]);
			}
			return json([
				'msg' => $this->deviceShow->getError(),
				'status' => 1,
			]);
		} else {
			$id = input('get.id');
			$list = $this->deviceShow->where(['c_id' => $id])->find();
			$company = model('Company')->field('c_id,c_company_name')->select();

			$this->assign(array(
				'title' => '修改展示设备',
				'list' => $list,
				'company' => $company,
			));
			return $this->fetch();
		}

	}

	/**
	 * 执行修改
	 */
	public function doEdit() {

	}

	/**
	 * 状态修改
	 */
	public function isStatus() {

		if (request()->isGet()) {
			$where['c_id'] = (int) input('get.id');
			$status = (int) $this->deviceShow->where($where)->value('c_status');
			if ($status == 0) {
				$data['c_status'] = 1;
			} else {
				$data['c_status'] = 0;
			}
			if ($this->deviceShow->where($where)->save($data)) {
				return json([
					'msg' => '修改成功',
					'status' => 0,
				]);
			}
			return json([
				'msg' => '修改失败',
				'status' => 1,
			]);
		}
	}

	/**
	 * 删除
	 */
	public function del() {
		if (request()->isGet()) {
			$id = (int) input('get.id');
			if ($this->deviceShow->where(['c_id' => $id])->delete()) {
				return json(array(
					'msg' => '删除成功！',
					'status' => 0,
				));
			} else {
				return json(array(
					'msg' => '删除失败！',
					'status' => 1,
				));
			}
		}
	}

}

?>
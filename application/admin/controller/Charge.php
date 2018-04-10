<?php
namespace app\admin\controller;

class Charge extends Base {
	public $charge;
	public $type;
	public function initialize() {

		$this->charge = model("Charge");
		$this->type = config('device.deviceType');
		$this->assign('type', $this->type);
	}
	/**
	 * 首页渲染
	 */
	public function index() {
		$where = array('c_isdel' => 0);
		$list = $this->charge->getChargeList($where);
		foreach ($list as $k => $v) {
			$list[$k]['c_type'] = $this->type[$v['c_type']];
		}
		return $this->fetch('', [
			'title' => '充电价格列表',
			'list' => $list,
		]);
	}
	/**
	 * 渲染添加页面
	 */
	public function add() {
		//is post exce the add option
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_charge_time' => input('post.c_charge_time'),
				'c_unit' => input('post.c_unit'),
				'c_price' => input('c_price'),
				'c_type' => input('c_type'),
				'c_add_time' => time(),
			];
			if ($this->charge->save($data)) {
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
		}
		//none post exce the view option
		return $this->fetch('', [
			'title' => '添加充电价格',
		]);
	}
	/**
	 * 修改页面渲染
	 */
	public function edit() {
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_chargeid' => (int) input('post.c_chargeid'),
				'c_charge_time' => input('post.c_charge_time'),
				'c_unit' => input('post.c_unit'),
				'c_type' => input('post.c_type'),
				'c_price' => input('post.c_price'),
			];
			//validate 验证
			$res = $this->charge->save($data, ['c_chargeid' => (int) input('post.c_chargeid')]);
			if ($res) {
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
		}{
			$id = input('get.id');
			$list = $this->charge->where(['c_chargeid' => $id])->find();
			return $this->fetch('', [
				'title' => '修改充电价格',
				'list' => $list,
			]);
		}
	}

	/**
	 * 状态修改
	 */
	public function isStatus() {

		if (request()->isGet()) {
			$where['c_chargeid'] = (int) input('get.id');

			$status = (int) $this->charge->where($where)->value('c_status');
			if ($status == 0) {
				$data['c_status'] = 1;
			} else {
				$data['c_status'] = 0;
			}
			if ($this->charge->save($data, $where)) {
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
			if ($this->charge->save(['c_isdel' => 1], ['c_chargeid' => $id])) {
				return json([
					'msg' => '删除成功！',
					'status' => 0,
				]);
			} else {
				return json([
					'msg' => '删除失败！',
					'status' => 1,
				]);
			}
		}

	}
}

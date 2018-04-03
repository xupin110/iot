<?php
namespace app\admin\controller;

class Company extends Base {

	public function index() {
		$list = model("Company")->find();
		$this->assign([
			'title' => '公司介绍',
			'list' => $list,
		]);
		return $this->fetch();
	}

	public function doEdit() {
		$about = model("Company");
		if (request()->isPost()) {
			$id = input('post.c_id');
			$data = array(
				'c_company_name' => input('post.c_company_name'),
				'c_service_phone' => input('post.c_service_phone'),
				'c_address' => input('post.c_address'),
			);
			//根据id来判断是新增还是修改
			if (empty($id)) {
				if ($about->add($data)) {
					return json([
						'msg' => '修改成功',
						'status' => 0,
					]);
				}
			} else {
				if ($about->save($data, ['c_id' => $id])) {
					return json([
						'msg' => '修改成功',
						'status' => 0,
					]);
				}
			}
			return json([
				'msg' => '修改失败',
				'status' => 1,
			]);
		}
	}
}

?>
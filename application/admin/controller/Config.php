<?php
namespace app\admin\controller;

class Config extends Base
{

	public function index() {
		$list = M("Config")->find();
		$this->assign([
			'title' => '公司介绍',
			'list' => $list
			]);
		$this->display();
	}


	public function doEdit() {
		$about = D("Config");
		if (IS_POST) {
			$id = I('post.c_id');
			$data = array(
				'c_company_name' => I('post.c_company_name'),
				'c_service_phone' => I('post.c_service_phone'),
				'c_address' => I('post.c_address')
				);
			if (empty($id)) {
				if ($about->create($data)) {
					if ($about->add()) {
						$this->ajaxReturn([
							'msg' => '修改成功',
							'status' => 0
							]);
					}
				}else {
					$this->ajaxReturn([
						'msg' => $about->getError(),
						'status' => 0
						]);
				}
			}else {
				$data['c_id'] = $id;
				if ($about->create($data)) {
					if ($about->save()) {
						$this->ajaxReturn([
							'msg' => '修改成功',
							'status' => 0
							]);
					}
				}else {
					$this->ajaxReturn([
						'msg' => $about->getError(),
						'status' => 0
						]);
				}
			}
			$this->ajaxReturn([
				'msg' => '修改失败',
				'status' => 1
				]);
		}
	}
}




?>
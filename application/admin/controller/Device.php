<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/27
 * Time: 18:43
 */

namespace app\admin\controller;

class Device extends Base {
	public $device;
	public $type;
	public $validate;
	public function initialize() {
		$this->device = model("Device");
		$this->type = config('device.deviceType');
		$this->validate = validate('Device');
		$this->assign('type', $this->type);
	}
	/**
	 * 首页渲染
	 */
	public function index() {

		$so = input('get.so');
		$status = input('get.status');
		$where['c_isdel'] = 0;
		if (!empty($so)) {
			// $where['c_name'] = ['like', "%sdfsdf%"];
			// $where['c_name']=['like','%'.$so.'%'];
			$map['c_name'] = ['c_name', 'like', '%{$so}%'];
			$map['c_deviceid'] = ['c_deviceid', 'like', "%{$so}%"];
			$map['c_device_sn'] = ['c_device_sn', '=', $so];
			$list = $this->device->where($map)

			// ->where(['c_deviceid',$so])
				->paginate();
			// $where[] = ['c_device_sn',$so];
			// $where[0]['c_deviceid'] = $so;
			// $where[0]['_logic'] = 'OR';
		} else {
			$list = $this->device->getDeviceList($where, 'c_deviceid desc');
		}

		//设置查询状态的条件
		// if (!empty($status)) {
		//     if ($status == 1) {
		//         $where['c_type'] = 2;
		//     } elseif ($status == 2) {
		//         $where['c_type'] = 1;
		//     } elseif ($status == 3) {
		//         $where['c_lease_status'] = 0;
		//     } elseif ($status == 4) {
		//         $where['c_lease_status'] = 1;
		//     } elseif ($status == 5) {
		//         $where['c_isdel'] = 1;
		//     }
		// }
		// var_dump($where);exit;
		if ($list) {
			foreach ($list as $k => $v) {
				$list[$k]['c_type'] = $this->type[$v['c_type']];
			}
		}
		return $this->fetch('', [
			'title' => '设备列表',
			'list' => $list,
			'status' => empty($status) ? '' : $status,
			'so' => empty($so) ? '' : $so,
		]);
	}

	/**
	 * 添加渲染
	 */
	public function add() {
		return $this->fetch('', [
			'title' => '添加设备',
		]);
	}
	public function csCode() {
		// 二维码链接
		//$qrUrl = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Wechat/entry?c_staffid=1';
		$qrUrl = 'http://www.baidu.com';
		$logo = './Public/Admin/images/QwLogin.jpg';
		// 生成二维码
		$qrcdoe = controller('Common')->qrCode($qrUrl, 2, $logo);
		var_dump($qrcdoe);
		$error = controller('Common')->getError();
		var_dump($error);
	}
	/**
	 * 执行添加
	 */
	public function doAdd() {
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_name' => input('post.c_name'),
				'c_device_sn' => input('post.c_device_sn'),
				'c_lng' => input('post.c_lng'),
				'c_lat' => input('post.c_lat'),
				'c_address' => input('post.c_address'),
				'c_type' => input('post.c_type'),
				'c_add_time' => time(),
			];
			if ($this->validate->check($data)) {
				if ($this->device->save($data)) {
					$deviceID = $this->device->id;
					// 当前域名
					$DomainName = 'http://' . $_SERVER['SERVER_NAME'];
					$qrUrl = $DomainName . url('app/home/Wechat/entry', ['deviceId' => $deviceID]);
					$logo = input('post.logo');
					// 生成二维码
					$qrcdoe = controller('Common')->qrCode($qrUrl, $deviceID, $logo);
					if ($qrcdoe == false) {
						// 删除
						$this->device->destroy($deviceID);
						return json([
							'msg' => controller("Common")->getError(),
							'status' => 1,
						]);
					}
					// 修改二维码
					$qrArr['c_qr_code'] = $DomainName . '/' . $qrcdoe;
					if ($this->device->doUpdate($qrArr, ['c_deviceid' => $deviceID])) {
						return json([
							'msg' => '添加成功',
							'status' => 0,
						]);
					}
					return json([
						'msg' => '添加失败',
						'status' => 0,
					]);
				} else {
					return json([
						'msg' => '添加失败',
						'status' => 1,
					]);
				}
			} else {
				return json([
					'msg' => $this->validate->getError(),
					'status' => 1,
				]);
			}
		}
	}
	public function edit() {
		//接收数据，回显页面
		$list = $this->device->getDeviceList(['c_deviceid' => intput('get.id')]);
		return $this->fetch('', [
			'title' => '修改设备',
			'list' => $list,
		]);
	}
	/**
	 * 执行修改
	 */
	public function doEdit() {
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_deviceid' => intput('c_deviceid'),
				'c_name' => intput('post.c_name'),
				'c_device_sn' => intput('post.c_device_sn'),
				'c_lng' => intput('post.c_lng'),
				'c_lat' => intput('post.c_lat'),
				'c_address' => intput('post.c_address'),
				'c_type' => intput('post.c_type'),
			];
			if ($this->validate->check($data)) {
				if ($this->device->save($data)) {
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
			} else {
				return json([
					'msg' => $this->validate->getError(),
					'status' => 1,
				]);
			}
		}
	}

	/**
	 * 显示状态修改
	 */
	public function isStatus() {

		if (request()->isGet()) {
			$where['c_deviceid'] = intput('get.id');
			$status = $this->device->where($where)->value('c_status');
			if ($status == 0) {
				$data['c_status'] = 1;
			} else {
				$data['c_status'] = 0;
			}
			if ($this->device->save($data, $where)) {
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
			$data['c_isdel'] = 1;
			if ($this->device->save($data, ['c_deviceid' => intput('get.id')])) {
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
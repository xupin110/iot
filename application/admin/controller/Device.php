<?php
/**
 * User: liuxiaodong
 * Date: 2018/3/5
 * Time: 18:43
 */

namespace app\admin\controller;
use app\service\Service;
use \Swoole\Pager;
use think\Paginator;

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
			$where['c_devicesn'] = ['c_devicesn', 'like', "%" . $so . "%"];
		}
		// $list = $this->device->getDeviceList($where, 'c_deviceid desc');
		// if (empty($_GET["gid"])) {
		// 	$gets["gid"] = '1';
		// }
		// $page = !empty($_GET['page']) ? $_GET['page'] : 1;
		// $pagesize = 20;
        //页数
        if (!empty($_GET['pagesize'])) {
            $pagesize = intval($_GET['pagesize']);
        } else {
            $pagesize = 10;
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : 1;
		$list = Service::getInstance()->call("Device::getDevices")->getResult(10);
//        $pager = new Pager(array('total'=> $list["total"], 'perpage'  => $pagesize, 'nowindex' => $page));
//        $this->assign('pager', array('total' => $list["total"], 'pagesize' => $pagesize, 'render' => $pager->render()));
//        var_dump($list->render());
//        exit;
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
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_name' => input('post.c_name'),
				'c_devicesn' => input('post.c_devicesn'),
				'c_lng' => input('post.c_lng'),
				'c_lat' => input('post.c_lat'),
				'c_address' => input('post.c_address'),
				'c_type' => input('post.c_type'),
				'c_add_time' => time(),
			];
			if ($this->validate->check($data)) {
				$res = Service::getInstance()->call('Device::addDevice', $data)->getResult(10);
				if ($res) {
					$deviceID = $res;
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
	//接收数据，回显页面
	public function edit() {
		if (request()->isPost()) {
			//接收数据
			$data = [
				'c_name' => input('post.c_name'),
				'c_devicesn' => input('post.c_devicesn'),
				'c_lng' => input('post.c_lng'),
				'c_lat' => input('post.c_lat'),
				'c_address' => input('post.c_address'),
				'c_type' => input('post.c_type'),
			];
			$id = input('post.c_deviceid');
			if ($this->validate->check($data)) {
				if ($this->device->save($data, ['c_deviceid' => $id])) {
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
		$list = $this->device->where(['c_deviceid' => input('get.id')])->find();
		return $this->fetch('', [
			'title' => '修改设备',
			'list' => $list,
		]);
	}
	/**
	 * 显示状态修改
	 */
	public function isStatus() {

		if (request()->isGet()) {
			$id = input('get.id');
			$res = Service::getInstance()->call("Device::updateDevice", $id)->getResult(10);
			if ($res) {
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
			// $data['c_isdel'] = 1;
			$deviceid = input('get.id');
			$res = Service::getInstance()->call("Device::delDevice", $deviceid)->getResult(10);

			if ($res) {
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
<?php
/**
 * User: liuxiaodong
 * Date: 2018/3/5
 * Time: 18:43
 */

namespace app\admin\controller;
use app\service\Service;

class Control extends Base {
	public $device;
	public $type;
	public $validate;
	public function initialize() {
		$this->type = config('device.deviceType');
		$this->assign('type', $this->type);
	}
	/**
	 * 首页渲染
	 */
	public function index() {

		$list = Service::getInstance()->call("Control::getControls")->getResult(10);
		if ($list) {
			foreach ($list as $k => $v) {
				$list[$k]['c_type'] = $this->type[$v['c_type']];
				if (isset($v['c_relay'])) {
					$list[$k]['c_relay'] = unserialize($v['c_relay']);
				}
			}

		}
		// var_dump($list);exit;
		return $this->fetch('', [
			'title' => '设备列表',
			'list' => $list,
		]);
	}

	/**
	 * 电流电压上下限控制
	 * @return mixed
	 */
	public function safe() {
		$res = Service::getInstance()->call("Monitor::getMonitors")->getResult(10);
		$list = [];
		foreach ($res as $k => $v) {
			# code...
			$list[$k]['c_deviceid'] = $v['c_deviceid'];
			$list[$k]['c_devicesn'] = $v['c_devicesn'];
			if ($v['monitor']) {
				$list[$k]['isconnect'] = 1;
			} else {
				$list[$k]['isconnect'] = 0;
			}
		}
		return $this->fetch('', [
			'title' => '阈值控制',
			'list' => $list,
		]);
	}

    /**
     * 上下限编辑页
     * @return mixed
     */
	public function controlLimit() {
		if (request()->isGet()) {
			$devicesn = input('get.devicesn');
			$type = input('get.type');
            $res = Service::getInstance()->call("Monitor::getMonitor",$devicesn)->getResult(10);
            $safeType = 'c_'.$type;
            $count=[];
            if($type != 'temp'){
                $no = unserialize($res[$safeType]);
                $count = [];
                foreach($no as $v){
                    $count[] = $v['No'];
                }
            }
			return $this->fetch('', [
				'devicesn' => $devicesn,
				'type' => $type,
                'title' =>'阈值控制',
                'no' => $count,
			]);
		}
		$this->error('参数错误');
	}
	public function doLimit(){
		if(request()->isPost()){
            $devicesn = input('post.devicesn');
            $type = input('post.type');
            $upper = input('post.upper');
            $lower = input('post.lower');
            if(empty($upper) || empty($lower) || empty($devicesn) || empty($type)){
                return json(['msg'=> 'arg error','status' => 1]);
              }
              if($type == 'temp'){
                  $data = [
                      "DeviceSn" => $devicesn,
                      "ServerControl" => $type=='current'?'10':($type=='voltage'?'11':($type=='temp'?'12':'10')),
                      $type=='current'?'CurrentCon':($type=='voltage'?'VdcCon':($type=='temp'?'TempCon':'CurrentCon')) => [
                          "Lower" => $lower,
                          "Upper" => $upper,
                      ]
                  ];
              }else{
                  $data = [
                      "DeviceSn" => $devicesn,
                      "ServerControl" => $type=='current'?'10':($type=='voltage'?'11':($type=='temp'?'12':'10')),
                      $type=='current'?'CurrentCon':($type=='voltage'?'VdcCon':($type=='temp'?'TempCon':'CurrentCon')) => [
                          "Lower" => $lower,
                          "Upper" => $upper,
                          "No" => input('post.no'),
                      ],
                  ];
              }
            $res = Service::getInstance()->call("Control::doLimit",$data)->getResult(10);
            if($res){
                return json([
                    'msg' => '控制成功',
                    'status' => 0,
                ]);
            }
            return json([
                'msg' => '控制失败',
                'status' => 1,
            ]);
		}
		return json([
		    'msg' => '参数非法',
            'status'=>1,
        ]);
    }
	/**
	 * 预订单检查测试
	 * @return \think\response\Json
	 */
	public function preOrderCheck() {
		if (request()->isPost()) {
			$data['c_devicesn'] = input('post.sn');
			$ret = Service::getInstance()->call("Control::preOrderCheck", $data)->getResult(10);
			if (!$ret) {
				return json([
					'msg' => '失败',
					'status' => 1,
				]);
			}
			return json([
				'msg' => '成功',
				'status' => 0,
			]);

		}
	}
	/**
	 * 显示状态修改
	 */
	public function update() {
		/**
		 * [
		 * 		'id' => '11',
		 * 		'sn' => '127.0.0.1',
		 * 		'key' => '2',
		 * 		'value' => '0'
		 * ]
		 */
		// var_dump(input('post.'));exit;
		if (request()->isPost()) {
			$data['c_deviceid'] = input('post.id');
			$data['c_devicesn'] = input('post.sn');
			$data['c_relay'][input('post.key')] = input('post.value');
			$res = Service::getInstance()->call("Control::update", $data)->getResult(2);
			if ($res) {
				return json([
					'msg' => '成功',
					'status' => 0,
				]);
			}
			return json([
				'msg' => '失败',
				'status' => 1,
			]);
		}{
			return json([
				'msg' => '失败',
				'status' => 1,
			]);
		}
	}/**
	 * 添加渲染
	 */
	public function contype() {
		/**
		 * [
		 * 		'id' => '11',
		 * 		'sn' => '127.0.0.1',
		 * 		'key' => '2',
		 * 		'value' => '0'
		 * ]
		 */
		if (request()->isPost()) {
			$data['c_devicesn'] = input('post.sn');
			$data['c_connect_type'] = input('post.value');
			$res = Service::getInstance()->call("Control::contype", $data)->getResult(2);
			if ($res) {
				return json([
					'msg' => '成功',
					'status' => 0,
				]);
			}
			return json([
				'msg' => '失败',
				'status' => 1,
			]);
		}{
			return json([
				'msg' => '失败',
				'status' => 1,
			]);
		}
	}
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
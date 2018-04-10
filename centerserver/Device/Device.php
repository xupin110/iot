<?php
namespace Device;
use Lib\Robot;

class Device {
	//初始连接
	public function initConnect($data) {
		if (Robot::register($data['fd'], $data['DeviceSn'])) {
			return ['errno' => 0, 'data' => 'sucess'];

		} else {
			return ['errno' => 8010, 'data' => 'error'];
		}
		;
	}
	//心跳设置状态
	public function heartbeatSet() {

	}
	//预订单状态检查
	public function preOrderCheck() {

	}
	//设备状态检查
	public function deviceCheck() {

	}
	//断开连接
	public function closeConnect() {

	}
	//客户端设置状态
	public function deviceSet() {

	}
	//断开电源
	public function closePower() {

	}

}
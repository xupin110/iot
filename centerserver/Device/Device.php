<?php
namespace Device;
use Lib\Robot;
use Lib\Util;
use Lib\Monitor;
class Device {
	//初始连接
	public function initConnect($data) {
		echo "Device ------ Device ----------initConnect\n" . PHP_EOL;
		if (Robot::register($data['fd'], $data['DeviceSn'])) {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],['RequestStatus'] => '1']);

		} else {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],['RequestStatus'] => '0']);
		}
		echo "endDevice ------ Device ----------initConnect\n" . PHP_EOL;
	}
	//心跳设置状态
	public function heartbeatSet() {
		return ['errno' => 0, 'data' => 'heartbeatSetsucess'];
	}
	//预订单状态检查
	public function preOrderCheck() {
		return ['errno' => 0, 'data' => 'hpreOrderCheckSetsucess'];
	}
	//设备状态检查
	public function deviceCheck($data) {
		$res = Monitor::updateMonitor($data);
		return ['errno' => 0, 'data' => 'deviceChecksucess'];}
	//断开连接
	public function closeConnect() {
		return ['errno' => 0, 'data' => 'closeConnectsucess'];
	}
	//客户端设置状态
	public function deviceSet() {
		return ['errno' => 0, 'data' => 'deviceSetsucess'];
	}
	//断开电源
	public function closePower() {
		return ['errno' => 0, 'data' => 'closePowersucess'];
	}

}
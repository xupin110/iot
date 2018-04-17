<?php
namespace Device;
use Lib\Robot;
use Lib\Util;
use Lib\Monitor;
class Device {
	//初始连接
	public function initConnect($data) {
		echo "Device ------ Device ----------initConnect\n" . PHP_EOL;
		if (Robot::register($data['fd'], $data['DeviceSn']) && Monitor::updateMonitor($data)) {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);

		} else {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		echo "endDevice ------ Device ----------initConnect\n" . PHP_EOL;
	}
	//心跳设置状态
	public function heartbeatSet($data) {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0','msg' => 'not allow use the heartbeat set']);
	}
	//预订单状态检查
	public function preOrderCheck($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
	}
	//设备状态检查
	public function deviceCheck($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);			
	}
	//断开连接
	public function closeConnect($data) {
		if(!Robot::unRegister($data['fd'])){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);			
		}
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);	
	}
	//客户端设置状态
	public function deviceSet() {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']); 
	}
	//断开电源
	public function closePower() {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']); 
	}

}
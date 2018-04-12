<?php
namespace Device;
use Lib\Robot;
use model\Device as DbDevice;
use Lib\Util;
use Lib\Client;
class RequestCate {
	//判断请求的类型l
	public static function requestControl($data) {
		echo "Device ------ RequestCate ----------requestControl\n" . PHP_EOL;
		print_r($data['RequestControl']);
		if ($data['RequestControl'] != '1') {
			echo "checktoken" . PHP_EOL;
			$res = self::checkToken($data['data']);
			if (!$res) {
			return Util::msg('1',['DeviceSn' => $data['data']['DeviceSn'],'RequestStatus' => '0']);				
			}
		}
		if ($data['key'] != 9) {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);			
		}
		return self::distributeRquest($data['data'], $data['RequestControl']);

	}
	//检查是否有权限 是否有注册
	public static function checkToken($data) {
		echo "Device ------ RequestCate ----------checkToken\n" . PHP_EOL;
		$devicesn = $data['DeviceSn'];
		$id = DbDevice::getOneDevice(['c_devicesn' => $devicesn]);
		if (empty($id)) {
			return false;
		}
		$fd = Robot::$table->get($devicesn);
		if (Robot::$aTable->exist($id['c_deviceid'])) {
			if ($fd) {
				if ($fd != $data['fd']) {
					if (!Robot::$table->set($devicesn, ['fd' => $data['fd'], "lasttime" => time()])) {
						return false;
					}

					return true;
				}
			}else{
				return Robot::register($data['fd'], $devicesn);	
			}

		}
		return false;

	}
	//分发请求
	public static function distributeRquest($data, $type) {

		$device = new Device();
		switch ($type) {
		case '1':
			$res = $device->initConnect($data);
			break;
		case '2':
			$res = $device->heartbeatSet($data);
			break;
		case '3':
			$res = $device->preOrderCheck($data);
			break;
		case '4':
			$res = $device->deviceCheck($data);
			break;
		case '5':
			$res = $device->closeConnect($data);
			break;
		case '6':
			$res = $device->initConnect($data);
			break;
		case '7':
			$res = $device->deviceSet($data);
			break;
		default:
			$res = ['errno' => '9205', 'msg' => '传入参数错误'];
			break;
		}
		return $res;
	}
}
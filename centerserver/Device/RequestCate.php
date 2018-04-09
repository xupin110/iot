<?php
namespace Device;
class RequestCate
{
	//判断请求的类型
	public static function requestControl($data){
		if($data['key'] != 9){
			return ['errno' => '9205','msg' => '传入参数错误'];
		}
		return self::distributeRquest($data['data'],$data['RequestControl']);

	}
	//分发请求
	public static function distributeRquest($data,$type){

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
				$res = ['errno' => '9205','msg' => '传入参数错误'];
				break;
		}
		return $res;
	}
}
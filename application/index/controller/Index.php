<?php
namespace app\index\controller;
use think\Controller;
class Index extends  Controller{
	public function index() {
		// $a = \map\Map::Staticimage('106.67923744596,28.87613983528');
		// print_r($a);exit;
		// echo "<img src='".$a."'>";
		// $device = new Device;
		// $a = $device->paginate();
		// foreach ($a as $value) {
		// 	# code...
		// 	var_dump($value['c_deviceid']);
		// }
		// if (!true && true) {
		// 	echo "test";
		// }
//        echo strtotime('00:00:00');
//        echo date('Y-m-t', strtotime('-1 month'));
    return $this->fetch();
	}

}

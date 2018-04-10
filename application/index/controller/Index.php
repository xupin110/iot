<?php
namespace app\index\controller;

class Index {
	public function index() {
		// // header('location:http://localhost:82/admin');
		// $device = new Device;
		// $a = $device->paginate();
		// foreach ($a as $value) {
		// 	# code...
		// 	var_dump($value['c_deviceid']);
		// }
		$a = new Service();
	}

}

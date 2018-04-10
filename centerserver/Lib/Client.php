<?php
/**
 * soa服务的客户端工具类
 * Class Service
 * @package Lib
 */

namespace Lib;

use Lib\Robot;

class Client {
	protected $namespace = "App";
	protected $fd = -1;
	protected static $insance = [];

	public function __construct($devicesn) {
		echo "Lib ------ Client ----------__construct\n" . PHP_EOL;
		$ret = Robot::$table->get($devicesn);
		if (isset($ret["fd"]) && !empty($ret["fd"])) {
			$this->fd = $ret["fd"];
		}
	}

	public static function getInstance($devicesn = "") {
		echo "Lib ------ Client ----------getInstance\n" . PHP_EOL;
		if (isset(self::$insance[$devicesn]) && !empty(self::$insance[$devicesn])) {
			return self::$insance[$devicesn];
		}
		$insance = new self($devicesn);
		self::$insance[$devicesn] = $insance;
		return $insance;

	}

	function call() {
		echo "Lib ------ Client ----------call\n" . PHP_EOL;
		$args = func_get_args();
		return $this->task($this->namespace . '\\' . $args[0], array_slice($args, 1));
	}

	protected function task($function, $params = array()) {
		echo "Lib ------ Client ----------task\n" . PHP_EOL;

		$data = SOAProtocol::encode($function, $params);
		$ret = CenterServer::$_server->send($this->fd, $data);
		return $ret;
	}
	function close() {
		return CenterServer::$_server->close($this->fd);
	}
}

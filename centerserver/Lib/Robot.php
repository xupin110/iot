<?php
/**
 * 中心服中的任务分发
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-22
 * Time: 下午3:27
 */

namespace Lib;
use model\Device;

class Robot {

	static public $table;
	static public $groupTable;
	static public $aTable;
	static private $devicesns;
	static public $tableAgent;

	static private $column = [
		"fd" => [\swoole_table::TYPE_INT, 8],
		"lasttime" => [\swoole_table::TYPE_STRING, 16],
	];
	static private $aColumn = [
		"devicesn" => [\swoole_table::TYPE_STRING, 15],
	];

	public static function init() {
		echo "Lib ------ Robot ----------init\n" . PHP_EOL;
		self::$table = new \swoole_table(ROBOT_MAX * 2);
		foreach (self::$column as $key => $v) {
			self::$table->column($key, $v[0], $v[1]);
		}
		self::$table->create();

		self::$aTable = new \swoole_table(ROBOT_MAX * 2);
		foreach (self::$aColumn as $key => $v) {
			self::$aTable->column($key, $v[0], $v[1]);
		}
		self::$aTable->create();
		self::loadAgents();
	}

	/**
	 * 载入分组代理信息
	 * @return bool
	 */
	public static function loadAgents() {
		echo "Lib ------ Robot ----------loadAgents\n" . PHP_EOL;
		self::$tableAgent = Device::getInstance();
		$agents = Device::getAllDevices(['c_status' => 0]);
		if (empty($agents)) {
			return false;
		}
		foreach ($agents as $agent) {
			if (count(self::$aTable) > ROBOT_MAX) {
				print_r("loadAgents fail ,because robot size max");
				Flog::log("loadAgents fail ,because robot size Max");
				return true;
			}
			self::$aTable->set($agent["c_deviceid"], [
				"devicesn" => $agent["c_devicesn"],
			]);
		}
		return true;
	}
	public static function stopAgent($id) {
		echo "Lib ------ Robot ----------stopAgent" . PHP_EOL;
		$res = self::$aTable->del($id);
		$res = Device::getOneDevice(['c_deviceid' => $id]);
		$devicesn = $res['c_devicesn'];
		if (self::$table->exist($devicesn)) {
			$client = new Client($devicesn);
			$client->call("close", []);
			if (self::$table->del($devicesn)) {
				$res1 = true;
			} else {
				$res1 = false;
			}
		}
		{
			$res1 = true;
		}
		print_r('del' . $res1);
		if ($res && $res1) {
			return true;
		}

		return false;
	}
	/**
	 * @param    [type]      $id [add id]
	 * @return   [type]          [description]
	 */
	public static function startAgent($id) {
		echo "Lib ------ Robot ----------startAgent\n" . PHP_EOL;
		$agent = Device::getOneDevice(['c_deviceid' => $id]);
		$res = self::$aTable->set($agent["c_deviceid"], [
			"devicesn" => $agent["c_devicesn"],
		]);
		if ($res) {
			return true;
		}
		return false;
	}

	/**
	 * 注册服务
	 * @param $fd
	 * @param $devicesn
	 * @return bool
	 */
	public static function register($fd, $devicesn) {
		echo "Lib ------ Robot ----------register\n" . PHP_EOL;
		print_r($devicesn);
		$id = self::$tableAgent->getOneDevice(['c_devicesn' => $devicesn]);
		print_r($id);
		if (empty($id)) {
			return false;
		}
		if (self::$aTable->exist($id['c_deviceid'])) {
			if (self::$table->exist($devicesn)) {
				$client = new Client($devicesn);
				$client->call("close", []);
			}
			if (self::$table->set($devicesn, ['fd' => $fd, "lasttime" => time()])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 注销服务
	 * @param $fd
	 * @return mixed
	 */
	public static function unRegister($fd) {
		echo "Lib ------ Robot ----------unRegister\n" . PHP_EOL;
		foreach (self::$table as $devicesn => $value) {
			if ($value["fd"] == $fd) {
				return self::$table->del($devicesn);
			}
		}
	}

	private static function loadIps() {
		echo "Lib ------ Robot ----------loadIps\n" . PHP_EOL;
		foreach (self::$table as $k => $v) {
			self::$devicesns[$k] = $v;
		}
	}

	/**
	 * 执行任务
	 * @param $task
	 * @return bool|null
	 */
	public static function Run($task) {
		echo "Lib ------ Robot ----------Run\n" . PHP_EOL;
		self::loadIps(); //载入配置到本地变量

		if (($robot = self::selectWorker($task["agents"])) == false) {
			return false;
		}
		if (!self::sendTask($robot, $task)) {
			TermLog::log($task["runid"], $task["id"], "发送业务失败", $task);
			return false;
		}
		return true;
	}

	/**
	 * 分发任务
	 * @param $robot
	 * @param $task
	 * @return bool
	 */
	private static function sendTask($robot, $task) {
		echo "Lib ------ Robot ----------sendTask\n" . PHP_EOL;
		TermLog::log($task["runid"], $task["id"], "发送到agent服务器", $robot);
		$client = new Client($robot);
		$rect = $client->call("Exec::run", $task);
		if (!$rect) {
			TermLog::log($task["runid"], $task["id"], "agent服务器停止服务", $robot . "已停止服务");
			unset(self::$devicesns[$robot]);
			if (($robot = self::selectWorker($task["agents"])) == false) {
				return false;
			}
			return self::sendTask($robot, $task);
		}
		return true;
	}

	/**
	 * 选择能执行任务的worker
	 * @return bool
	 */
	private static function selectWorker($agents) {
		echo "Lib ------ Robot ----------selectWorker\n" . PHP_EOL;
		$num = count(self::$devicesns);
		if (!$num) {
			Flog::log("No workers available");
			return false;
		}
		$agents = explode(",", $agents);
		if (empty($agents)) {
			Flog::log("没有配置运行服务器");
			return false;
		}

		$rand = rand(1, count($agents));
		$n = 0;
		foreach ($agents as $k => $aid) {
			$n++;
			if ($rand <= $n) {
				$aip = self::$aTable->get($aid);
				if (empty($aip)) {
					continue;
				}
				$robot = $aip["ip"];
				if (!isset(self::$devicesns[$robot])) {
					continue;
				}
				return $robot;
			}
		}
		Flog::log("没有选中任何服务器,服务器数量:" . $num . ",随机数:" . $rand);
		return false;
	}

}
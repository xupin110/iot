<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use app\admin\model\Device as ModelDevice;
use Lib;

class Device {

	/**
	 * worker回调中心服 任务执行状态
	 * @param $tasks
	 * @return array
	 */

	public static function notify($tasks) {
		echo "APP ------ Device ----------notify" . PHP_EOL;
		if (empty($tasks) || count($tasks) <= 0) {
			return Lib\Util::errCodeMsg(101, "The tasks can't be empty");
		}
		$header = Lib\LoadTasks::getTasks();
		foreach ($tasks as $task) {
			if ($task["code"] == 0) {
				$runStatus = Lib\LoadTasks::RunStatusSuccess;
			} else {
				$runStatus = Lib\LoadTasks::RunStatusFailed;
				Lib\Report::taskFailed($task["taskId"], $task["runid"], $task["code"]);
			}
			$header->set($task["taskId"], ["runStatus" => $runStatus, "runUpdateTime" => time()]);
			$header->decr($task["taskId"], 'execNum'); //减少当前执行数量
			if (Lib\Tasks::$table->exist($task["runid"])) {
				Lib\Tasks::$table->set($task["runid"], ["runStatus" => $runStatus]);
			}
			Lib\TermLog::log($task["runid"], $task["taskId"], "任务已经执行完成", $task);
		}
		return Lib\Util::errCodeMsg(0, "ok");
	}

	/**
	 * 获取在线worker
	 * @param int $page
	 * @param int $size
	 * @return array
	 */
	public static function getRobots($page = 1, $size = 10) {
		echo "APP ------ Device ----------getRobots" . PHP_EOL;
		$start = ($page - 1) * $size;
		$end = $start + $size;
		$data = [];
		$list = Lib\Robot::$table;
		$n = 0;
		foreach ($list as $id => $rb) {
			$n++;
			if ($n <= $start) {
				continue;
			}
			if ($n > $end) {
				break;
			}
			$data[$id] = $rb;
		}
		return ["total" => count($list), "rows" => $data];
	}

	/**
	 * 获取代理服务器
	 * @return array
	 */
	public static function getDevices($gets = [], $page = 1, $pagesize = 10) {

		$device = new ModelDevice();
		$list = $device->paginate();
		foreach ($list as $k => $task) {
			$tmp = Lib\Robot::$table->get($task["c_devicesn"]);
			if (!empty($tmp)) {
				$list[$k]["lasttime"] = $tmp["lasttime"];
				$list[$k]["isconnect"] = 1;
			} else {
				$list[$k]["isconnect"] = 0;
			}
		}
		return $list;
	}

	/**
	 * 获取单个任务
	 * @param $id
	 * @return array
	 */
	public static function getDevice($id) {
		echo "APP ------ Device ----------getDevice" . PHP_EOL;
		$Device = table("Devices")->get($id);
		if (!$Device->exist()) {
			return Lib\Util::errCodeMsg(101, "不存在");
		}
		$gids = table("Device_group")->gets(["aid" => $id]);
		$data = [
			"id" => $Device["id"],
			"alias" => $Device["alias"],
			"ip" => $Device["ip"],
			"status" => $Device["status"],
			"gids" => ["-1"],
		];
		if (!empty($gids)) {
			$gname = [];
			foreach ($gids as $gid) {
				$gname[$gid["gid"]] = $gid["gid"];
			}
			$data["gids"] = $gname;
		}
		return Lib\Util::errCodeMsg(0, "", $data);
	}

	/**
	 * 根据分组id获取Device列表
	 * @param $gid
	 * @return array
	 * @throws \Exception
	 */
	public static function getDeviceByGid($gid) {
		echo "APP ------ Device ----------getDeviceByGid" . PHP_EOL;
		$agg = table("Device_group");
		$glist = $agg->gets(["gid" => $gid]);
		if (empty($glist)) {
			return [];
		}
		foreach ($glist as $g) {
			$aids[] = $g["aid"];
		}
		if (empty($aids)) {
			return [];
		}
		$gets["in"] = ["id", $aids];
		$list = table("Devices")->gets($gets);
		$data = [];
		foreach ($list as $value) {
			$data[] = [
				"id" => $value["id"],
				"alias" => $value["alias"],
				"ip" => $value["ip"],
			];
		}
		return $data;
	}

	/**
	 * 添加任务
	 * @param $Device
	 * @return array
	 */
	public static function addDevice($Device) {
		echo "APP ------ Device ----------addDevice" . PHP_EOL;
		if (empty($Device)) {
			return Lib\Util::errCodeMsg(101, "参数为空");
		}
		$gids = $Device["gids"];
		unset($Device["gids"]);
		$id = table("Devices")->put($Device);
		if ($id === false) {
			return Lib\Util::errCodeMsg(102, "添加失败");
		}
		$Device_group = table("Device_group");
		foreach ($gids as $gid) {
			$Device_group->put(["gid" => $gid, "aid" => $id]);
		}
		//重新加载代理
		Lib\Robot::$aTable->set($id, ["ip" => $Device["ip"]]);
		return Lib\Util::errCodeMsg(0, "保存成功", $id);
	}

	/**
	 *  修改任务
	 * @param $id
	 * @param $Device
	 * @return array
	 */
	public static function updateDevice($id, $Device) {
		echo "APP ------ Device ----------updateDevice" . PHP_EOL;
		if (empty($id) || empty($Device)) {
			return Lib\Util::errCodeMsg(101, "参数为空");
		}
		$gids = $Device["gids"];
		unset($Device["gids"]);
		if (!table("Devices")->set($id, $Device)) {
			return Lib\Util::errCodeMsg(102, "更新失败");
		}
		$Device_group = table("Device_group");
		$Device_group->dels(["aid" => $id]);
		//var_dump($id);
		foreach ($gids as $gid) {
			if ($gid <= 0) {
				continue;
			}
			$Device_group->put(["gid" => $gid, "aid" => $id]);
		}
		self::reload($id);
		return Lib\Util::errCodeMsg(0, "更新成功");
	}

	/**
	 * 删除任务代理
	 * @param $id
	 * @return array
	 */
	public static function deleteDevice($id) {
		echo "APP ------ Device ----------deleteDevice" . PHP_EOL;
		if (empty($id)) {
			return Lib\Util::errCodeMsg(101, "参数为空");
		}
		if (!table("Devices")->del($id)) {
			return Lib\Util::errCodeMsg(102, "删除失败");
		}
		table("Device_group")->dels(["aid" => $id]);
		self::reload($id);
		return Lib\Util::errCodeMsg(0, "删除成功");
	}

	private static function reload($aid) {
		echo "APP ------ Device ----------reload" . PHP_EOL;
		$Devices = table("Devices");
		$info = $Devices->get($aid);
		if (empty($info) && $info["status"] == 1) {
			Lib\Robot::$aTable->del($aid);
		}
	}

}
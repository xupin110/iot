<?php
/**
 * 监控状态 标题
 * @Author   liuxiaodong
 * @DateTime 2018-04-10
 */

namespace Lib;
use model\Device;
use model\Monitor as DbMonitor;
class Monitor {
	static public $table;

	static private $column = [
		"c_devicesn" => [\swoole_table::TYPE_STRING, 12], //设备编号
		"c_voltage" => [\swoole_table::TYPE_STRING, 200], //设备电压
		"c_current" => [\swoole_table::TYPE_STRING, 200], //设备电流
		"c_temp" => [\swoole_table::TYPE_STRING, 200], //设备温度
		"c_lng" => [\swoole_table::TYPE_STRING, 50], //设备维度
		"c_lat" => [\swoole_table::TYPE_STRING, 50], //设备經度
		"c_device_request" => [\swoole_table::TYPE_STRING, 5], //设备请求方式
		"c_relay" => [\swoole_table::TYPE_STRING, 200], //设备继电器
		"c_connect_type" => [\swoole_table::TYPE_STRING, 5], //设备链接方式
	];

	/**
	 * 创建配置表
	 */
	public static function init() {
		echo "Lib ------ Monitor ----------init\n" . PHP_EOL;
		self::$table = new \swoole_table(MONITOR_SIZE * 2);
		foreach (self::$column as $key => $v) {
			self::$table->column($key, $v[0], $v[1]);
		}
		self::$table->create();
	}
    public static function updateMonitor($data){
        echo "Lib ------ Monitor ----------updateMonitor\n" . PHP_EOL;       
        $table['c_devicesn'] = $data['DeviceSn'];
        $table['c_voltage'] = serialize($data['Vdc']);
        $table['c_current'] = serialize($data['Current']);
        $table['c_temp'] = $data['Temp'];
        $table['c_lng'] = $data['Lng'];
        $table['c_lat'] = $data['Lat'];
        $table['c_device_request'] = $data['RequestControl'];
        $table['c_relay'] = serialize($data['Relay']);
        $table['c_connect_type'] = $data['ConnectType'];
        if(!self::$table->set($data['DeviceSn'],$table)){
            return false;
        }
        return true;

    }
    public static function unRegister($devicesn) {
        echo "Lib ------ Robot ----------unRegister\n" . PHP_EOL;
        foreach (self::$table as $sn => $value) {
            if ($sn == $devicesn) {
                return self::$table->del($devicesn);
            }
        }
    }

	/**
	 * 每分钟执行一次，判断下一分钟需要执行的任务
	 */
	public static function checkMonitor() {
		echo "Lib ------ Monitor ----------checkMonitor\n" . PHP_EOL;
		//清理完成任务
		self::clean();
		$Monitor = LoadMonitor::getMonitor();
		if (count($Monitor) > 0) {
			$time = time();
			foreach ($Monitor as $id => $task) {
				if ($task["status"] != LoadMonitor::T_START) {
					continue;
				}
				$ret = ParseCrontab::parse($task["rule"], $time);
				if ($ret === false) {
					Flog::log(ParseCrontab::$error);
				} elseif (!empty($ret)) {
					$min = date("YmdHi");
					$time = strtotime(date("Y-m-d H:i"));
					foreach ($ret as $sec) {
						if (count(self::$table) > Monitor_SIZE) {
							Flog::log("checkMonitor fail ,because Monitor size Max");
							break;
						}
						$k = Donkeyid::getInstance()->dk_get_next_id();
						self::$table->set($k, [
							"minute" => $min,
							"sec" => $time + $sec,
							"id" => $id,
							"runStatus" => LoadMonitor::RunStatusNormal,
						]);
					}
				}
			}
		}
		return true;
	}

	/**
	 * 清理已执行过的任务
	 */
	private static function clean() {
		echo "Lib ------ Monitor ----------clean\n" . PHP_EOL;
		$ids = [];
		$ids2 = [];
		$loadMonitor = LoadMonitor::getMonitor();
		$count = count(self::$table);
		if ($count > 0) {
			$minute = date("YmdHi");
			foreach (self::$table as $id => $task) {
				//以下状态,不需要在存储任务
				if (in_array($task["runStatus"],
					[
						LoadMonitor::RunStatusSuccess,
						LoadMonitor::RunStatusFailed,
						LoadMonitor::RunStatusError,
						LoadMonitor::RunStatusToTaskFailed,
					]
				)) {
					$ids[] = $id;
					continue;
				}
				$info = $loadMonitor->get($task["id"]);
				if (!is_array($info) || !array_key_exists("timeout", $info)) {
					continue;
				}
				//如果运行中的任务超过了阈值,则把超过1个小时没有响应的任务清除
				if ($count > Monitor_SIZE && $task["runStatus"] == LoadMonitor::RunStatusToMonitoruccess) {
					if (intval($minute) > intval($task["minute"]) + 60) {
						$ids[] = $id;
						$ids2[] = $task["id"];
						continue;
					}
				}
				//如果该任务无超时设置,则不进行处理
				if ($info["timeout"] <= 0) {
					continue;
				}
				//到了超时时间
				$timeout = intval($info["timeout"] / 60);
				$timeout = $timeout > 1 ? $timeout : 1;
				if (intval($minute) > intval($task["minute"]) + $timeout) {
					$ids[] = $id;
					if ($task["runStatus"] == LoadMonitor::RunStatusStart
						|| $task["runStatus"] == LoadMonitor::RunStatusToMonitoruccess
						|| $task["runStatus"] == LoadMonitor::RunStatusError
					) {
						$ids2[] = $task["id"];
					}
				}
			}
		}
		//删除
		foreach ($ids as $id) {
			self::$table->del($id);
		}
		//超时则把运行中的数量-1
		foreach ($ids2 as $tid) {
			$loadMonitor->decr($tid, "execNum");
		}
	}

	/**
	 * 获取当前可以执行的任务
	 * @return array
	 */
	public static function getMonitor() {
		// echo "Lib ------ Monitor ----------getMonitor\n".PHP_EOL;
		$data = [];
		if (count(self::$table) <= 0) {
			return [];
		}
		$min = date("YmdHi");

		foreach (self::$table as $k => $task) {
			if ($min == $task["minute"]) {
				if (time() == $task["sec"] && $task["runStatus"] == LoadMonitor::RunStatusNormal) {
					$data[$k] = $task["id"];
				}
			}
		}
		if (!empty($data)) {
			foreach ($data as $k => $val) {
				self::$table->set($k, ["runStatus" => LoadMonitor::RunStatusStart, "runTimeStart" => time()]);
			}
		}
		return $data;
	}
}
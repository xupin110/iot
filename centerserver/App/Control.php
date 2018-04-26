<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use Lib;
use model\Device as DbDevice;
use Lib\Monitor as Mon;
use Lib\Tasks;
use Lib\Robot;
use Lib\Client;
use Lib\Util;
class Control {

	/**
	 * worker回调中心服 任务执行状态
	 * @param $tasks
	 * @return array
	 */

	public static function notify($tasks) {
		echo "APP ------ Control ----------notify" . PHP_EOL;
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
	 * 获取代理服务器
	 * @return array
	 */
	public static function getControls($gets = [], $page = 1, $pagesize = 10) {
		// $list = DbDevice::getAllDevices();
		echo '----------------Control table'.PHP_EOL;
		$list = DbDevice::getOneColumns([],['c_deviceid','c_devicesn','c_status','c_type']);
		$res = [];
		foreach ($list as $k => $task) {
			$tmp = Lib\Robot::$table->get($task["c_devicesn"]);
			$monitor = Lib\Monitor::$table->get($task['c_devicesn']);
			$res[$k]['c_devicesn'] = $task['c_devicesn'];
			$res[$k]['c_deviceid'] = $task['c_deviceid'];
			$res[$k]['c_type'] = $task['c_type'];
			if (!empty($tmp)) {
				$res[$k]["lasttime"] = $tmp["lasttime"];
				$res[$k]["isconnect"] = 1;
				$res[$k]['c_relay'] = $monitor['c_relay'];
			} else {
				$res[$k]["isconnect"] = 0;
			}
			$res[$k]['connecttype'] = $monitor['c_connect_type'];
		}
		return $res;
	}
	public static function preOrderCheck($data){
	    $devicesn = $data['c_devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('5',['DeviceSn' => $devicesn]);
        $client = new Client($devicesn);
        $client->control($call);
        return true;
    }

    /**
     * 电流电压温度阈值控制
     * @param $data
     */
    public static function doLimit($data){
        $devicesn = $data['DeviceSn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = $data;
        $client = new Client($devicesn);
        $client->control($call);
        switch ($data['ServerControl']){
            case '10':
                sleep(2);
                $current = \Table\SafeLimit::$table->get($devicesn);
                $current = unserialize($current['safe_limit']);
                if($current){
                    if($current['CurrentCon']['No'] == $data['CurrentCon']['No']){
                        if($current['ControlStatus'] == '1'){
                            return true;
                        }
                    }
                }
                $res = false;
                break;
            case '11':
                sleep(2);
                $vdc = \Table\SafeLimit::$table->get($devicesn);
                $vdc = unserialize($vdc['safe_limit']);
                if($vdc){
                    if($vdc['VdcCon']['No'] == $data['VdcCon']['No']){
                        if($vdc['ControlStatus'] == '1'){
                            return  true;
                        }
                    }
                }
                $res = false;
                break;
            case '12':
                sleep(2);
                $temp = \Table\SafeLimit::$table->get($devicesn);
                $temp = unserialize($temp['safe_limit']);
                echo "--------------------".PHP_EOL;
                print_r($temp);
                print_r($data);
                echo "---------------------";
                if($temp){
                    if($temp['TempCon']){
                        if($temp['ControlStatus'] == '1'){
                            return true;
                        }
                    }
                }
                $res = false;
                break;
            default:
                $res = false;
                break;
        }
        return $res;
    }
	/**
	 *  修改任务
	 * @param $id
	 * @param $Device
	 * @return array
	 */
	public static function update($data) {
		echo "APP ------ Control ----------updateDevice" . PHP_EOL;
		if (empty($data['c_deviceid']) && empty($data)) {
			return false;
		}
		$ret = Tasks::updateRelay($data);
		if(!$ret){
			return false;
		}
		return true;
	}
	/**
	 * @param    [type]      $data [description]
	 * @return   [type]            [description]
	 */
	public static function contype($data) {
		echo "APP ------ Control ----------updateDevice" . PHP_EOL;
		if (empty($data['c_devicesn']) && empty($data)) {
			return false;
		}
		$ret = Tasks::contype($data);
		if(!$ret){
			return false;
		}
		return true;
	}	
	/**
	 *del the device
	 * @param    [type]      $id [deviceid]
	 * @return   [type]          [boolean]
	 */
	public static function delDevice($id) {
		echo "APP ------ Device ----------delDevice" . PHP_EOL;
		if (empty($id)) {
			return false;
		}
		$res = Lib\Robot::delAgent($id);
		$res1 = db('Device')->delete($id);
		if ($res && $res1) {
			return true;
		}
		return false;
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
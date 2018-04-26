<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/4/26 0026
 * Time: 10:45
 */


namespace Table;
use model\Device;
use model\Monitor as DbMonitor;
class SafeLimit {
    static public $table;

    static private $column = [
        "safe_limit" => [\swoole_table::TYPE_STRING, 200], //电流状态
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
    public static function updateSafeLimit($data){
        echo "Lib ------ SafeLimit ----------updateMonitor\n" . PHP_EOL;
        $devicesn = $data['DeviceSn'];
        $safe = serialize($data);
        if(!self::$table->set($devicesn,['safe_limit' =>$safe])){
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


}
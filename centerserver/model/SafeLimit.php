<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/8 0008
 * Time: 15:51
 */

namespace model;

class SafeLimit
{
    private static $_instance = null;

    /**
     * 单例模式
     * @return Predis|null
     * @throws \Exception
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function getSafeLimit($sn){
        return db('SafeLimit')->where('c_devicesn',$sn)->find();
    }
    /**
     * 将设备传上来的数据更新数据库安全值范围
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateSafeLimit($data){
        echo "Model ------ db  SafeLimit ----------updateSafeLimit\n" . PHP_EOL;
        $sn = $data['DeviceSn'];
        $res = $this->where('c_devicesn',$sn)->find();
        switch ($data['RequestControl'])
        {
            case '8':
                $current = unserialize($res['c_currentcon']);
                foreach ($current as $v){
                    if($v['No'] == $data['CurrentCon']['No']){
                        $v = $data['CurrentCon'];
                    }
                }
                $this->where('c_devicesn',$sn)->update(['c_currentcon' => serialize($current)]);
                break;
            case '9':
                $voltage = unserialize($res['c_vdccon']);
                foreach ($voltage as $v){
                    if($v['No'] == $data['VdcCon']['No']){
                        $v = $data['VdcCon'];
                    }
                }
                $this->where('c_devicesn',$sn)->update(['c_vdccon' => serialize($voltage)]);
                break;
            case '10':
                $temp = unserialize($res['c_tempcon']);
                $temp = $data['TempCon'];
                $this->where('c_devicesn',$sn)->update(['c_tempcon' => serialize($temp)]);
                break;
            default:
                break;
        }

    }
}
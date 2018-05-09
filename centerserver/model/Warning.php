<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/9 0009
 * Time: 10:53
 */

namespace model;
use \think\Model;

class Warning extends Model
{
    public static $_instance = null;
    /**
     * 单例模式
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function insertWarnData($data){
        $save['c_devicesn'] = $data['DeviceSn'];
        $save['c_type'] = $data['WarnType'];
        $res = db('SafeLimit')->where('c_devicesn',$data['DeviceSn'])->find();
        switch ($data['WarnType']){
            case 'Current':
                $current = unserialize($res['c_currentcon']);
                foreach ($current as $v){
                    if($v['No'] == $data['WarnStatus']['No']){
                        $save['c_lower'] = $v['Lower'];
                        $save['c_upper'] = $v['Upper'];
                    }
                }
                $save['c_no'] = $data['WarnStatus']['No'];
                $save['c_value'] = $data['WarnStatus']['Value'];
                break;
            case 'Vdc':
                $vdc = unserialize($res['c_vdccon']);
                foreach ($vdc as $v){
                    if($v['No'] == $data['WarnStatus']['No']){
                        $save['c_lower'] = $v['Lower'];
                        $save['c_upper'] = $v['Upper'];
                    }
                }
                $save['c_no'] = $data['WarnStatus']['No'];
                $save['c_value'] = $data['WarnStatus']['Value'];
                break;
            case 'Temp':
                $temp = unserialize($res['c_tempcon']);
                $save['c_lower'] = $temp['Lower'];
                $save['c_upper'] = $temp['Upper'];
                $save['c_value'] = $data['WarnStatus']['Value'];
                break;
                defaut:
                break;
        }
        $save['c_time'] = time();
        $res = $this->save($save);
        return $res;
    }
}
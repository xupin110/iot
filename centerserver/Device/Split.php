<?php
/**
 * Created by submlie.
 * author: liuxiadong
 * Date: 18-4-6
 * Time: 下午3:13
 */

namespace Device;

use Swoole\Protocol\SOAServer;
use Lib;
class Split
{

    public static $server;
    protected static $deviceArgs =[
        'DeviceSn','RequestControl','Relay','Vdc','Current','Temp','Lng','Lat','ConnectType'
    ];
    public function __construct(){
        self::$server = new self();

    }
    //判断是由设备提交的数据还是由后台提交的数据
    public static function isDevice($data)
    {
        $res['key'] = 0;
        foreach ($data as $key => $value) {
            # code...
            if(in_array($key, self::$deviceArgs))
            {
                $res['key'] += 1;  
            }

        }
        if ($res['key']>0){
            $res['RequestControl'] = $data['RequestControl'];
            $res['data'] = $data;
            return $res;
        }
        return true;

    }



}
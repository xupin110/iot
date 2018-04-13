<?php
/**
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-22
 * Time: 下午4:13
 */

namespace Lib;


class Util
{
    /**
     * [$data return masg data]
     * @var [serialize json]
     */
    public static $data = [
            'DeviceSn' => '127.0.0.1',
            'ServerControl' => '1',
            // 'RequestStatus' => '1'
        ];
    public static function split($type,$msg = []){
        $data = self::$data;
        foreach ($msg as $key => $value) {
            # code...
            $data[$key] = $value;
        }
        $data['ServerControl'] = $type;
        return $data; 
    }
    public static function msg($type = '1' ,$msg = [])
    {
        print_r($msg);
        if(!in_array($type, ['1','2','3','4','5','6','7','8','9'])){
            return ['error' => 9001 ,'msg' => 'server wrong 502'];
        }
        return self::split($type, $msg); 

    }
    static function listenHost()
    {
        echo "Lib ------ Util ----------listenHost\n".PHP_EOL;
        
        $listenHost = '127.0.0.1';
        return $listenHost;
    }


    public static function errCodeMsg($code = 0, $message = '', $data = array())
    {
        echo "Lib ------ Util ----------errCodeMsg\n".PHP_EOL;
        $res = array(
            'code' => $code,
            'msg' => $message ? $message : ($code ? 'fail' : 'success'),
            'data' => $data
        );

        return $res;
    }
}
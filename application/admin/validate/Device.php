<?php
namespace app\admin\validate;
use think\Validate;

class Device extends Validate{
    protected $rule=[
        'c_name' => 'require', 
        'c_device_sn' => 'require',
        'c_lng' => 'require',
        'c_lat' => 'require',
        'c_type' => 'require',
        'c_address' => 'require',
    ];


    /****场景设置***********/
    protected $scene=[
    	'add'=>['c_name','c_lng','c_type'],//添加功能

    ];



}

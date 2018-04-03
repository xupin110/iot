<?php
namespace app\admin\validate;
use think\Validate;

class Device extends Validate{
    protected $rule=[
        ['c_name','require','名称不得为空'], 
        ['c_device_sn','require','设备编号不得为空'],
        ['c_lng','require','经度不得为空'],
        ['c_lat','require','纬度不得为空'],
        ['c_type','require','类型不能为空'],
        ['c_address','require','地址不得为空'],
    ];


    /****场景设置***********/
    protected $scene=[
    	'add'=>['name','parent_id','id'],//添加功能
    	'listorder'=>['id','listorder'],//排序
    	'status'=>['id','status'],
    ];



}

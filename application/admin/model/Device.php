<?php 
namespace app\admin\model;

use think\Model;

class Device extends Model 
{

    
	public function getDeviceList($where , $order = 'c_deviceid asc' ,$limit = 15){
		return $this->where($where)
					->order($order)
					->paginate($limit);
	}
}











?>
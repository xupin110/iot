<?php
namespace app\admin\model;

use think\Model;

class Device extends Model {

	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-03
	 * @param    [type]      $where [查询的条件]
	 * @param    string      $order [排序]
	 * @param    integer     $limit [分页限制多少条]
	 * @return   [type]             [返回查询总数据]
	 */
	public function getDeviceList($where, $order = 'c_deviceid asc', $limit = 15) {
		return $this->where($where)
			->order($order)
			->paginate($limit);
	}
	public function doUpdate($where, $id) {
		return $this->where($id)->update($where);
	}

}

?>
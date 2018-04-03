<?php
namespace app\admin\model;
use Think\Model;
class Privilege extends Model {

	protected $_validate = array(
        array('pri_name','require','权限名称不得为空','',1),
        array('mname','require','模型名称不得为空','',1),
        array('cname','require','控制器名称不得为空','',1),
        array('aname','require','方法民称不得为空','',1),
        array('parent_id','require','父级权限不得为空','',1),
        array('pri_name','','权限名称不得重复',1,unique),
		);


	public function pritree() {
		$data=$this->select();
		return $this->resort($data);
	}

	public function resort($data,$parent_id=0,$level=0) {
		static $ret=array();
		foreach ($data as $k => $v) 
		{
			if($v['parent_id']==$parent_id)
			{
				$v['level']=$level;
				$ret[]=$v;
				$this->resort($data,$v['id'],$level+1);
			}
		}
		return $ret;
	}
}




?>
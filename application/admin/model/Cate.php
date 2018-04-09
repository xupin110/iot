<?php 
namespace app\admin\model;

use Think\Model;

class CateModel extends Model {

    protected $_validate = array(
		array('c_title','require','标题不得为空','',1),
		array('c_parent_id','require','父级分类不得为空','',1),
		//array('c_pc_move','require','展示端不得为空','',1),
		array('c_title','','展示端不得重复',1,unique)
		);

	public function catetree(){
		$data=$this->order('c_sort asc')->select();
		return $this->resort($data);
	}

	public function resort($data,$parent_id=0,$level=0){
		static $ret=array();
		foreach ($data as $k => $v) {
			if($v['c_parent_id']==$parent_id){
				$v['level']=$level;
				$ret[]=$v;
				$this->resort($data,$v['c_id'],$level+1);
			}
		}
		return $ret;
	}
}











?>
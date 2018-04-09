<?php
namespace app\admin\model;
use Think\Model;
class Role extends Model {

	protected $_validate = array(
		array('rolename','require','角色名称不得为空','',1),
		//array('pri_id_list','require','角色权限不得为空','',1),
		array('rolename','','角色名称不得重复',1,unique),
		);
}





?>
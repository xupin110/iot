<?php
namespace app\admin\controller;
use think\Controller;

class Base extends Controller {
	/**
	 * 当前登录会员信息
	 * */
	protected $adminUser = [];
	public $adminId;
	public function __construct() {
		parent::__construct();
		$adminId = session('admin_uid');
		if (!session('admin_uid')) {
			$this->error('请先登录系统', url('Login/index'));
		}
		$this->adminId = $adminId;
		if (request()->module() == 'Admin' && request()->controller() == 'Index') {
			$this->checkLogin();
			return true;
		}
		if (request()->module() == 'Admin' && request()->controller() == 'Admin' && request()->action() == 'logout') {
			return true;
		}
		// if (session("privilege")!='*' && !in_array(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,session('privilege'))) {
		//     if (IS_AJAX) {
		//         $this->ajaxReturn(array('msg' => '没有权限访问该功能！', 'status' => 1));
		//     }else {
		//         $this->error('没有权限访问该功能！');
		//     }

		// }
		// var_dump(config("menu.ADMIN_LIST"));exit;
		// foreach (config("menu.ADMIN_LIST") as $k) {
		//     # code...
		//     foreach ($k['menu'] as $v) {
		//         # code...
		//         var_dump($v['name']); echo "<br>";
		//     }
		// }
		// exit;
		$this->assign('menu_list', config('menu.ADMIN_LIST'));
		// var_dump(config('menu.ADMIN_LIST'));exit;
		$this->checkLogin();
	}

	/**
	 * 检查是否登录后台
	 * @return void
	 * */
	protected function checkLogin() {
		$where['id'] = session('admin_uid');
		$this->adminUser = model('admin')->where($where)->find();
		if (!$this->adminUser) {
			//登录验证失败
			header('location: ' . url('login/index'));
			exit();
		}

		$this->assign('_admin_user', $this->adminUser);
	}

	/**
	 * 弹窗提示
	 * @param string $tips 提示语
	 * @return void
	 * */
	protected function tip($tips) {
		$this->assign('tips', $tips);
		echo $this->fetch('public::tip');
	}

}
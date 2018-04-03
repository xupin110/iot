<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-03
 */
namespace app\admin\controller;

class User extends Base {
	public $user;
	public function initialize() {
		$this->user = model('User');
	}
	protected $sex = ['未知', '女', '男'];
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-03
	 * @return   [type]      [会员列表展示]
	 */
	public function index() {
		$so = input('so');
		$where = [];
		if (!empty($so)) {
			$where[0]['c_username'] = ['like', "%{$so}%"];
			$where[0]['c_tel'] = $so;
			$where[0]['c_id'] = $so;
			$where[0]['_logic'] = 'OR';
		}
		$list = $this->user->field('c_id,c_nickname,c_sex,c_pro,c_city,c_add_time,c_tel')
			->where($where)
			->order('c_id desc')
			->paginate();
		$this->assign([
			'title' => '会员列表',
			'list' => $list,
			'so' => $so,
		]);
		return $this->fetch();
	}

	/**
	 * 获取用户个人信息
	 */
	public function getInfo() {
		if (request()->isGet()) {
			$where['c_id'] = (int) input('get.id');

			$info = $this->user->where($where)->find();
			if ($info) {
				$info['sex'] = $this->sex[$info['c_sex']];
				$info['c_add_time'] = date('Y-m-d H:i:s', $info['c_add_time']);
				return json(array(
					'data' => $info,
					'status' => 0,
				));
			} else {
				return json(array(
					'msg' => input('get.id'),
					'status' => 1,
				));
			}
		}
	}

	/**
	 * 修改
	 */
	public function edit($id) {
		//接收数据，回显页面
		$list = $this->user->field('c_id,c_username,c_sex,c_tel,c_address,c_staffid')
			->where(['c_id' => $id])
			->find();

		$health = M("Staff")->where(['c_type' => 0])->field('c_id,c_name')->select();
		$this->assign([
			'title' => '修改资料',
			'list' => $list,
			'health' => $health,
		]);
		return $this->fetch();
	}

	/**
	 * 修改资料
	 */
	public function do_edit() {
		$user = $this->user;
		if (request()->isPost()) {
			$data = array(
				'c_id' => input('c_id'),
				'c_username' => input('post.c_username'),
				'c_sex' => input('post.c_sex'),
				'c_tel' => input('post.c_tel'),
				'c_address' => input('post.c_address'),
				'c_staffid' => input('post.c_staffid'),
			);
			if ($user->save($data)) {
				//修改成功
				$redata = array(
					'status' => 0,
					'msg' => '修改成功',
				);
			} else {
				//修改失败
				$redata = array(
					'status' => 1,
					'msg' => '修改失败',
				);
			}
		}
	}

	/**
	 * 表格导出
	 */
	public function msgOut() {
		header('Content-Type:text/html; charset=utf-8');
		$xlsCell = array(
			array('c_id', '会员ID'),
			array('c_username', '用户名'),
			array('c_tel', '手机号码'),
			array('c_address', '家庭地址'),
			array('c_level', '会员等级'),
			array('c_amount', '健康豆'),
			array('c_integral', '积分'),
			array('c_name', '健康师'),
			array('c_cashier_vip', '收银系统会员'),
			array('c_create_time', '创建时间'),
		);
		// 导出表格名称
		$xlsName = '会员表导出';
		$user = $this->user;
		if (request()->isGet()) {
			$so = trim(input('get.so'));
			if (!empty($so)) {
				$where[0]['u.c_username'] = ['like', "%{$so}%"];
				$where[0]['u.c_tel'] = $so;
				$where[0]['u.c_id'] = $so;
				$where[0]['_logic'] = 'OR';
			}
			$xlsData = $user->alias('u')
				->field('u.c_id,u.c_username,u.c_tel,u.c_address,u.c_integral,u.c_create_time,u.c_level,u.c_cashier_vip,v.c_amount,s.c_name')
				->join('left join __VIP_CARD__ v ON u.c_id=v.c_uid')
				->join('left join __STAFF__ s ON u.c_staffid=s.c_id')
				->where($where)
				->order('u.c_id asc')
				->select();
			if ($xlsData) {
				foreach ($xlsData as $kk => $vv) {
					$xlsData[$kk]['c_create_time'] = date('Y-m-d H:i:s', $vv['c_create_time']);
					$xlsData[$kk]['c_amount'] = $vv['c_amount'] ? $vv['c_amount'] : 0;
					$xlsData[$kk]['c_level'] = $this->userel->getlevel($vv['c_level']);
					$xlsData[$kk]['c_name'] = $vv['c_name'] ? $vv['c_name'] : '无';
					if ($vv['c_cashier_vip'] == 0) {
						$xlsData[$kk]['c_cashier_vip'] = '否';
					} else {
						$xlsData[$kk]['c_cashier_vip'] = '是';
					}
				}
			}
			$this->exportExcel($xlsName, $xlsCell, $xlsData);
		}
	}

}
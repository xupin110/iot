<?php
return [
	'ADMIN_LIST' => array(
		array(
			'controller' => 'Charge',
			'icon' => 'fa fa-list',
			'tle' => '充电管理',
			'menu' => array(
				array(
					'action' => 'index',
					'name' => '充电价格列表',
					'href' => url('Charge/index'),
				),
				[
					'action' => 'index',
					'name' => '添加充电价格',
					'href' => url('Charge/add'),
				],
			),
		),
		array(
			'controller' => 'Device',
			'icon' => 'fa fa-television',
			'tle' => '设备管理',
			'menu' => array(
				array(
					'action' => 'index',
					'name' => '设备列表',
					'font' => 'clone',
					'href' => url('Device/index'),
				),
				[
					'action' => 'index',
					'name' => '添加设备',
					'font' => 'clone',
					'href' => url('Device/add'),
				],
				array(
					'action' => 'index',
					'name' => '展示设备列表',
					'font' => 'clone',
					'href' => url('Deviceshow/index'),
				),
				[
					'action' => 'index',
					'name' => '添加展示设备',
					'font' => 'clone',
					'href' => url('Deviceshow/add'),
				],
			),
		),
		array(
			'controller' => 'Monitor',
			'icon' => 'fa fa-newspaper-o',
			'tle' => '监测管理',
			'menu' => array(
				[
					'action' => 'index',
					'name' => '租用设备列表',
					'font' => 'clone',
					'href' => url('Monitor/index'),
				],
				[
					'action' => 'status',
					'name' => '设备实时状态',
					'font' => 'clone',
					'href' => url('Monitor/status'),
				],
				[
					'action' => 'status',
					'name' => '数据渲染展示',
					'font' => 'clone',
					'href' => url('Monitor/datashow'),
				],
			),
		),
		array(
			'controller' => 'Control',
			'icon' => 'fa fa-user-md',
			'tle' => '控制管理',
			'menu' => array(
				[
					'action' => 'index',
					'name' => '控制列表',
					'font' => 'clone',
					'href' => url('Control/index'),
				],
				[
					'action' => 'safe',
					'name' => '安全控制',
					'font' => 'clone',
					'href' => url('Control/safe'),
				],
			),
		),
		array(
			'controller' => 'User',
			'icon' => 'fa fa-users',
			'tle' => '会员管理',
			'menu' => array(
				array(
					'action' => 'index',
					'name' => '会员列表',
					'href' => url('User/index'),
				),
			),
		),
		array(
			'controller' => 'Order',
			'icon' => 'fa fa-cart-plus',
			'tle' => '订单管理',
			'menu' => array(
				array(
					'action' => 'index',
					'name' => '订单列表',
					'href' => url('Order/index'),
				),
			),
		),
		array(
			'controller' => 'Turn',
			'icon' => 'fa fa-bar-chart',
			'tle' => '资金流水管理',
			'menu' => array(
				array(
					'action' => 'count',
					'name' => '资金流水列表 ',
					'font' => 'clone',
					'href' => url('Award/count'),
				),
			),
		),
		array(
			'controller' => 'Company',
			'icon' => 'fa fa-bar-chart',
			'tle' => '公司管理',
			'menu' => array(
				array(
					'action' => 'count',
					'name' => '公司配置 ',
					'font' => 'clone',
					'href' => url('Company/index'),
				),
			),
		),
		array(
			'controller' => 'Admin',
			'icon' => 'fa fa-user',
			'tle' => '管理员管理',
			'menu' => array(
				array(
					'action' => 'index',
					'name' => '管理员列表',
					'font' => 'user-plus',
					'href' => url('Admin/index'),
				),
				array(
					'action' => 'index',
					'name' => '角色管理',
					'font' => 'wrench',
					'href' => url('Role/index'),
				),
				array(
					'action' => 'index',
					'name' => '权限管理',
					'font' => 'user-plus',
					'href' => url('Privilege/index'),
				),
				array(
					'action' => 'clear',
					'name' => ' 清除缓存',
					'font' => 'refresh',
					'href' => url('Index/clear'),
				),
			),
		),

	),
];

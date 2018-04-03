<?php
namespace app\admin\controller;

class Order extends Base 
{
	public function __construct() {

    	parent::__construct();
    	$this->order = D("Order");
    }
    /**
     * 充值订单
     */
    public function index() 
    {
        $where['o.c_isdel'] = 0;
        $so = I('get.so');
        if (!empty($so)) {
            $where[0] = ['u.c_nickname' =>['like',"%{$so}%"],
                'c_order_sn' => $so,
                'c_orderid' => $so,
                'u.c_id' => $so,
                '_logic' => 'OR'
            ];
        }
		$count = $this->order->alias('o')->alias('o')
                ->join('LEFT JOIN __USER__ u ON o.c_uid=u.c_id')->where($where)->count();
        #实例化think分页类
        $Page = new \Think\Page($count,15);
        $show       = $Page->show();// 分页显示输出
        $list = $this->order->field('o.*,u.c_nickname,d.c_name')
                ->alias('o')
                ->join('LEFT JOIN __USER__ u ON o.c_uid=u.c_id')
                ->join('LEFT JOIN __DEVICE__ d ON o.c_device_id=d.c_deviceid')
                ->where($where)
                ->order('o.c_orderid desc')
                ->limit($Page->firstRow,$Page->listRows)->select();
		$this->assign([
			'title' => '订单列表',
			'list' => $list,
			'page' => $show
			]);
		$this->display();
    }
    /**
     * 订单详情
     * @return xmind
     */
    public function orderDetail() 
    {
        if (IS_GET) {
            $id = I('get.id');

            $list = $this->order->field('o.*,u.c_nickname,d.c_name')
                ->alias('o')
                ->join('LEFT JOIN __USER__ u ON o.c_uid=u.c_id')
                ->join('LEFT JOIN __DEVICE__ d ON o.c_device_id=d.c_deviceid')
                ->where(['o.c_orderid' => $id, 'o.c_isdel' => 0])->find();

            if ($list) {
                $list['c_status'] = D("Home/Order")->orderStatus($list['c_status']);
                $list['c_type'] = D("Device")->getType($list['c_type']);
                $list['c_start_time'] = date('Y-m-d H:i:s', $list['c_start_time']);
                $list['c_shop_time'] = date('Y-m-d H:i:s', $list['c_shop_time']);
                $list['c_add_time'] = date('Y-m-d H:i:s', $list['c_add_time']);
                $list['c_pay_time'] = date('Y-m-d H:i:s', $list['c_pay_time']);
                switch ($list['c_charge_type']) {
                    case 0:
                        $list['c_charge_type'] = '未知';
                        break;
                    case 1:
                        $list['c_charge_type'] = '手机充电';
                        break;
                    case 1:
                        $list['c_charge_type'] = '电动车充电';
                        break;
                }
                switch ($list['c_pay_status']) {
                    case 0:
                        $list['c_pay_status'] = '未支付';
                        break;
                    case 1:
                        $list['c_pay_status'] = '已支付';
                        break;
                }
                $this->ajaxReturn([
                    'msg' => '',
                    'data' => $list,
                    'status' => 0
                    ]);
            }
            $this->ajaxReturn([
                'msg' => '没有数据',
                'status' => 1
                ]); 
        }
    }
    /**
     * 带走租用设备审核
     * @return minx
     */
    public function audit()
    {
        if (IS_GET) {
            $orderId = I("get.orderId");
            $status = I("get.status");
            if (empty($orderId)) {
                $this->ajaxReturn([
                    'msg' => '请求参数不得为空.',
                    'status' => 1
                    ]);
            }
            if ($status == 1) {
                $this->ajaxReturn([
                    'msg' => '审核成功',
                    'status' => 0
                    ]);
            }
            $this->ajaxReturn([
                'msg' => '审核失败',
                'status' => 1
                ]);
        }
    }


    /**
     * 删除
     */
    public function del(){

        if (IS_GET) {
            $data['c_isdel'] = 1;
            if($this->order->where(['c_orderid' => I('get.id')])->save($data)) {
                $this->ajaxReturn(array(
                    'msg' => '删除成功！', 
                    'status' => 0
                    ));
            }else{
                $this->ajaxReturn(array(
                    'msg' => '删除失败！', 
                    'status' => 1
                    ));
            }
        }
    }


}





?>
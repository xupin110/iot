<?php
namespace app\admin\model;
use Think\Model;
class User extends Model {

	protected $_validate = array(
        array('c_username','require','姓名不得为空','',1),
        array('c_sex','require','性别不得为空','',1),
        array('c_tel','require','电话不得为空','',1),
        array('c_address','require','地址不得为空','',1),
        array('c_health_id','require','健康师不得为空','',1),
		);
    

    public $expires = array('一个月','两个月','三个月','四个月','五个月','六个月','七个月','八个月','九个月','十个月','十一个月','十二个月','两年');

    /**
     * 充值回调
     * @return minxd
     */
	public function getRecharge($orderid, $reason) 
	{
        $userMod = D('Home/User');
		$orderMod = M("Order");
		$where = array('c_orderid' => $orderid, 'c_pay_status' => 0);
		$orderInfo = $orderMod->field('c_orderid,c_order_sn,c_uid,c_goodsid,c_goods_title,c_price,c_integral,c_level,c_expires,c_type')->where($where)->find();
		if (!$orderInfo) {
            $result = array('msg' => '订单已支付或未找到该笔订单', 'status' => 1);
			return $result;
		}
        $info = $userMod->getUserInfo($orderInfo['c_uid'], 'c_id,c_level,c_tel');
        if (!$info) {
            $result = array('msg' => '会员没有找到.', 'status' => 1);
            return $result;
        }
        $vip_wh= array('c_uid' => $info['c_id'], 'c_status' => 0);
        $vip_card = M("VipCard")->field('c_id,c_uid,c_amount,c_level,c_shop_time')->where($vip_wh)->find();
        M()->startTrans();
        if ($vip_card) {
            // 获取当前时间戳
            $time = time();
            // 充值等级是否和会员权益一样,如果一样直接相加续卡权益时间
            if ($orderInfo['c_level'] == $vip_card['c_level']) {
                // 会员权益是否到期
                if ($vip_card['c_shop_time'] > $time) {
                    // 没到期，会员剩余到期时间
                    $vip_Surplus = $vip_card['c_shop_time'] - $time;
                    // 会员剩余时间加上续费时间
                    $vip_date = $orderInfo['c_expires'] + $vip_Surplus;
                } else {
                    // 到期，现在续卡时间
                    $vip_date = $orderInfo['c_expires'];
                }
                // 已使用
                $status = 1;
                // 权益当前等级
                $vip_level = $vip_card['c_level'];
            } else {
                // 如果不一样，判断会员权益是否到期，到期直接修改会员等级和续卡权益时间
                if ($vip_card['c_shop_time'] < $time) {
                    // 到期，现在续卡时间
                    $vip_date = $orderInfo['c_expires'];
                    // 已使用
                    $status = 1;
                    // 充值权益等级
                    $vip_level = $orderInfo['c_level'];
                } else {
                    // 如果有效期等于0，修改订单状态
                    if ($orderInfo['c_expires'] == 0) {
                        // 已使用
                        $status = 1;
                    } else {
                        // 未使用
                        $status = 0;
                    }
                    // 还有权益未到期，
                    $vip_date = $vip_card['c_shop_time'];
                    // 权益当前等级
                    $vip_level = $vip_card['c_level'];
                }
            }
            // 会员等级是否和充值等级一样，如果一样
            if ($info['c_level'] == $orderInfo['c_level']) {
                // 充值等级不变
                $level = $info['c_level'];
            } elseif ($info['c_level'] < $orderInfo['c_level']) {
                // 如果会员等级小于充值等级，升级
                $level = $orderInfo['c_level'];
            } else {
                // 否则原等级
                $level = $info['c_level'];
            }
            // 当前会员充值
            $cardArr = array('c_id' => $vip_card['c_id'],'c_price' => $orderInfo['c_price'],'c_level' => $vip_level,'shop_time'=>$vip_date);

            $res_vipcard = $userMod->updateVipCard($cardArr);
            if (!$res_vipcard) {
                $result = array('msg' => '会员卡信息修改失败.', 'status' => 1);
                return $result;
            }
        } else {
            // 添加会员卡
            $res_vipcard = M("VipCard")->add(array(
                'c_cardid' => $this->getVipCard($orderInfo['c_uid']), 
                'c_amount' => $orderInfo['c_price'],
                'c_uid' => $orderInfo['c_uid'],
                'c_level' => $orderInfo['c_level'],
                'c_add_time' => time(),
                'c_shop_time' => $orderInfo['c_expire']
                ));
            // 已使用
            $status = 1;
            // 充值等级
            $level = $orderInfo['c_level'];
        }
        if ($info['c_level'] != $level || $orderInfo['c_integral'] > 0) {
            // 修改会员信息
            $res_user = $user->where(['c_id' => $orderInfo['c_uid']])->save(array(
                    'c_integral' => ['exp', "c_integral+{$orderInfo['c_integral']}"],
                    'c_level' => $level
                    ));
            if (!$res_user) {
                M()->rollback();
                $result = array('msg' => '会员信息修改失败.', 'status' => 1);
                return $result;
            }
        }
        // 生成健康豆充值记录
        $res_health = M("HealthRecord")->add(array(
            'c_uid' => $orderInfo['c_uid'],
            'c_amount' => $orderInfo['c_price'],
            'c_info' => $orderInfo['c_goods_title'] .'--'. $reason,
            'c_add_time' => time()
            ));
        // 是否有赠送积分，生成积分记录
        $integral_sta = 0;
        if ($orderInfo['c_integral'] > 0) {
            $res_integral = M("IntegralRecord")->add(array(
                'c_uid' => $orderInfo['c_uid'],
                'c_amount' => $orderInfo['c_integral'],
                'c_info' => $orderInfo['c_goods_title'],
                'c_type' => '充值赠送积分',
                'c_add_time' => time()
                ));
            $res_youhuIntegral = A("Home/Youhu")->userVipCharege($info['c_tel'], $orderInfo['c_integral'], Reward, $orderInfo['c_order_sn']);
            // 积分是否成功
            if ($res_youhuIntegral) {
                $integral_sta = 2;
            } else {
                $integral_sta = 1;
            }
            if (!$res_integral) {
                M()->rollback();
                $result = array('msg' => '积分赠送失败.', 'status' => 1);
                return $result;
            }
        }
        $res_youhu = A("Home/Youhu")->userVipCharege($info['c_tel'], $orderInfo['c_price'], $orderInfo['c_price']);
        // 金额是否成功
        if ($res_youhu) {
            $youhu_sta = 1;
        } else {
            $youhu_sta = 0;
        }
        $order_data = array(
            'c_pay_status' => 1,
            'c_pay_time' => time(),
            'c_status' => $status,
            'c_youhu_status' => $youhu_sta,
            'c_integral_sta' => $integral_sta
            );
        // 修改订单状态
        $res_Order = $orderMod->where(['c_orderid' => $orderInfo['c_orderid']])->save($order_data);
        if ($res_vipcard && $res_health && $res_Order) {
            //实物储存
            M()->commit();
            $result = array('msg' => '充值成功.', 'status' => 0);
            return $result;
        }
        M()->rollback();
        $result = array('msg' => '充值失败.', 'status' => 1);
        return $result;
	}

    /**
     * 获取会员到期时间
     * @param  [type]  $expires 月份
     * @param  integer $month   字符串转换数值
     * @return int
     */
    public function get_expires($expires) {

    	switch ($expires) {
    		case '零个月':
    		        $month = 0;
    			break;
    		case '一个月':
    		        $month = 1;
    			break;
    		case '两个月':
    			    $month = 2;
    			break;
    		case '三个月':
    			    $month = 3;
    			break;
    		case '四个月':
    			    $month = 4;
    			break;
    		case '五个月':
    			    $month = 5;
    			break;
    		case '六个月':
    			    $month = 6;
    			break;
    		case '七个月':
    			    $month = 7;
    			break;
    		case '八个月':
    			    $month = 8;
    			break;
    		case '九个月':
    			    $month = 9;
    			break;
    		case '十个月':
    			    $month = 10;
    			break;
    		case '十一个月':
    			    $month = 11;
    			break;
    		case '十二个月':
    			    $month = 12;
    			break;
    		case '两年':
    			    $month = 24;
    			break;
    		default:
    			    return false;
    			break;
    	}
    	if ($month == 0) {
    		return $month;
    	}
	    // 转换成时间戳格式
		$vip_expire = strtotime("+{$month} month",time());

		return $vip_expire; 
    }



    /**
     * 会员卡编号
     * @return 数值
     */
    public function getVipCard($uid) {

    	$cardid = '0'.date('md').$uid;
        if (M("VipCard")->where(['c_cardid' => $cardid])->select()) {
			self::getVipCard();
		}
    	return $cardid;
    }
}




?>
<?php
namespace app\home\controller;
use think\Controller;


class WechatController extends Controller
{
    
/**
    public function __construct()
    {
        //执行上级父类构造
        parent::__construct();

        $this->setConfig();
    }
    public function setConfig() {
        $wx_pay = C('WX_PAY');
        $this->appID = $wx_pay['appID'];
        $this->appsecret = $wx_pay['appsecret'];
        $this->redirect_uri = 'http://'. $_SERVER['SERVER_NAME'] .'/Home/Wechat/userinfo';
    }

    /**
     * 授权入口
     */
    /**
     * @Author   liuxiaodong
     * @DateTime 2018-04-03
     * @return   [type]      [description]
     */
    public function entry()
    {
        header("Content-Type: text/html; charset=utf-8");
        // header('Access-Control-Allow-Origin: *');
        $deviceId = I('get.deviceId');
        $type = I('get.type');
        if (!empty($deviceId)) {
            session('deviceId', $deviceId);
        }
        // 回调地址编码
        $urlencode = urlencode($this->redirect_uri);

        //1.获取code
        $url_callback = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .$this->appID. '&redirect_uri='.$urlencode.'&response_type=code&scope=snsapi_userinfo&state=ucenter#wechat_redirect';

        header("Location:{$url_callback}");
    }

    /**
     * 拉取用户信息
     * @param  String $code
     */
    public function userinfo($code)
    {
        header("Content-Type: text/html; charset=utf-8");
        //使用code 换取access_token
        $access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appID.'&secret='.$this->appsecret .'&code='.$code.'&grant_type=authorization_code';
        //请求连接
        $accessToken = $this->curl_request($access_token);
        //反序列化
        $accessObj = json_decode($accessToken);
        //使用accesstoken 拉去用户信息
        $userInfo = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessObj->access_token . '&openid=' . $accessObj->openid . '&lang=zh_CN';
        //请求连接
        $user = $this->curl_request($userInfo);
        //反序列化
        $userObj = json_decode($user);

        $this->addUserinfo($userObj);
    }

    /**
     * 授权存入数据库，获取用户id
     * @param obj $userObj
     */
    public function addUserinfo( $userObj )
    {
        header('Content-Type:text/html;charset=utf-8');
        $user = D('User');
        $data = array(
            'c_openid' => $userObj->openid,
            'c_nickname' => $userObj->nickname,
            'c_avatar' => $userObj->headimgurl,
            'c_pro' => $userObj->province,
            'c_city' => $$userObj->city,
            'c_sex' => $userObj->sex,
            'c_add_time' => time()
            );
        $where['c_openid'] = $userObj->openid;
        $resInfo = $user->field('c_id,c_openid')->where($where)->find();
        // 会员是否存在，没有就添加
        if ($resInfo) {
            $userId = $resInfo['c_id'];
        }else{
            $userId = $user->createUserAdd($data); 
        }
        $this->getJumpUrl($userId);
    }
    
    /**
     * 授权跳转
     * @param  int $userId
     */
    public function getJumpUrl($userId = 0)
    {
        $deviceId = session('deviceId');
        // 生成条件
        $where = array('c_deviceid' => $deviceId, 'c_status' => 0, 'c_isdel' => 0);
        $resDevice = D("Device")->getDevice($where, 'c_deviceid,c_device_sn,c_type,c_lease_status');
        if (!$resDevice) {
            // 没找到数据，跳转到首页
            $jumpUrl = 'http://case2.qw1000.cn/index.html?uid='.$userId;
        } else {
            // 带走租用设备
            if ($resDevice['c_type'] == 2) {
                if ($resDevice['c_lease_status'] >= 1) {
                    // 已激活，跳转充电页面
                    $jumpUrl = 'http://case2.qw1000.cn/cqyd/#/charging?uid='.$userId.'&deviceId='.$resDevice['c_deviceid'];
                } else {
                    // 待租状态，跳转到激活页面
                    $jumpUrl = 'http://case2.qw1000.cn/cqyd/#/rent?uid='.$userId.'&deviceId='.$resDevice['c_deviceid'];
                }
            } else {
                // 临时租用设备,跳转到搜索页面
                $jumpUrl = 'http://case2.qw1000.cn/cqyd/#/equipconfirm?uid='.$userId.'&deviceSn='.$resDevice['c_device_sn'];
            }
        }
        session('deviceId', null);
        header("Location:{$jumpUrl}");
    }


    function curl_request($url, $type = "get", $data = '')
    {
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        //区别大小写
        $type = strtolower($type);
        switch ($type) {
            case 'get':
                break;
            case 'post':
                //post请求CURLOPT_POST
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        //采集
        $result = curl_exec($ch);
        //关闭
        curl_close($ch);
        return $result;
    }



}
<?php
namespace app\admin\model;

use think\Model;

class AdminRecord extends Model
{
  // 生成管理员记录
    public function insertRecord($admin_uid) {

    	if ($admin_uid) {
    		$res = $this->getLocation();
    		$data = array(
    			'login_ip' => get_client_ip(),
    			'admin_id' => $admin_uid,
    			'login_time' => time(),
    			'address' => $res['city']
    			);
    		M("AdminRecord")->add($data);
    	}
    	
    }



    
    public function getLocation() {
        $ip = $_SERVER["REMOTE_ADDR"];//获取客户端IP

        $url = "http://api.map.baidu.com/location/ip?ak=aBQklSlLAbuAsIqxKaVgd87Ybcg4klfT&ip={$ip}&coor=bd09ll";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if(curl_errno($ch))
        { echo 'CURL ERROR Code: '.curl_errno($ch).', reason: '.curl_error($ch);}
        curl_close($ch);

        
        $info = json_decode($output, true);
        $arr = array();
        if($info['status'] == "0"){
            $arr['lotx'] = $info['content']['point']['y'];
            $arr['loty'] = $info['content']['point']['x'];
            $arr['city'] = $info['content']['address_detail']['city'];
            $arr['keywords'] = explode("市",$citytemp);
            $arr['status'] = true;
        } else {
            $arr['status'] = false;
        }
        return $arr;
    }
}
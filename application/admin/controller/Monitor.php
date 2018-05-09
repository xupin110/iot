<?php
/**
 * User: liuxiaodong
 * Date: 2018/3/5
 * Time: 18:43
 */

namespace app\admin\controller;
use app\service\Service;
use function Couchbase\defaultDecoder;

class Monitor extends Base {
	public $monitor;
	public $type;
	public $validate;
	public function initialize() {
		$this->monitor = model("Monitor");
		$this->type = config('device.deviceType');
		$this->validate = validate('Device');
		$this->assign('type', $this->type);
	}
	/**
	 * 首页渲染
	 */
	public function index() {

		$so = input('get.so');
		$status = input('get.status');
		$where['c_isdel'] = 0;
		if (!empty($so)) {
			$where['c_devicesn'] = ['c_devicesn', 'like', "%" . $so . "%"];
		}
		// $list = $this->device->getDeviceList($where, 'c_deviceid desc');
		// if (empty($_GET["gid"])) {
		// 	$gets["gid"] = '1';
		// }
		// $page = !empty($_GET['page']) ? $_GET['page'] : 1;
		// $pagesize = 20;
		$list = Service::getInstance()->call("Monitor::getMonitors")->getResult(10);
		var_dump($list);exit;
		if ($list) {
			foreach ($list as $k => $v) {
				$list[$k]['c_type'] = $this->type[$v['c_type']];
			}

		}
		return $this->fetch('', [
			'title' => '设备列表',
			'list' => $list,
			'status' => empty($status) ? '' : $status,
			'so' => empty($so) ? '' : $so,
		]);
	}
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-11
	 * @return   [type]      [description]
	 */
	public function status() {

		$res = Service::getInstance()->call("Monitor::getMonitors")->getResult(10);
		$list = [];
		foreach ($res as $k => $v) {
			# code...
			if($v['monitor']){
				$v['monitor']['c_voltage'] = unserialize($v['monitor']['c_voltage']);
				$v['monitor']['c_current'] = unserialize($v['monitor']['c_current']);
				$v['monitor']['c_relay'] = unserialize($v['monitor']['c_relay']);
				$list[$k] = $v['monitor'];
				$list[$k]['c_deviceid'] = $v['c_deviceid'];
				$list[$k]['c_type'] = $this->type[$v['c_type']];
				$list[$k]['map'] = \map\Map::Staticimage($list[$k]['c_lng'].','.$list[$k]['c_lat']);
				$list[$k]['isconnect'] = 1;
				// $list[$k]['map'] = \map\Map::Staticimage('106.67923744596,28.87613983528');
			}
			else{
				$list[$k]['c_deviceid'] = $v['c_deviceid'];
				$list[$k]['c_devicesn'] = $v['c_devicesn'];
				$list[$k]['c_type'] = $this->type[$v['c_type']];
				$list[$k]['isconnect'] = 0;
			}
		}
//		 var_dump($list);exit;
		return $this->fetch('', [
			'title' => '设备列表',
			'list' => $list,
		]);
	}

    public  function datashow(){
        $res = Service::getInstance()->call("Monitor::getMonitors")->getResult(10);
        $list = [];
        foreach ($res as $k => $v) {
            # code...
            if($v['monitor']){
                $v['monitor']['c_voltage'] = unserialize($v['monitor']['c_voltage']);
                $v['monitor']['c_current'] = unserialize($v['monitor']['c_current']);
                $v['monitor']['c_relay'] = unserialize($v['monitor']['c_relay']);
                $list[$k] = $v['monitor'];
                $list[$k]['c_deviceid'] = $v['c_deviceid'];
                $list[$k]['c_type'] = $this->type[$v['c_type']];
                $list[$k]['map'] = \map\Map::Staticimage($list[$k]['c_lng'].','.$list[$k]['c_lat']);
                $list[$k]['isconnect'] = 1;
                // $list[$k]['map'] = \map\Map::Staticimage('106.67923744596,28.87613983528');
            }
            else{
                $list[$k]['c_deviceid'] = $v['c_deviceid'];
                $list[$k]['c_devicesn'] = $v['c_devicesn'];
                $list[$k]['c_type'] = $this->type[$v['c_type']];
                $list[$k]['isconnect'] = 0;
            }
        }
//		 var_dump($list);exit;
        return $this->fetch('', [
            'title' => '数据展示',
            'list' => $list,
        ]);
    }

    /**
     * 返回电流的数据渲染图
     * @return mixed
     */
    public function current(){
        if(request()->isGet()){
        	$this->dataDeal('c_current','电流');
            return $this->fetch();
        }
        $this->error('数据错误');
    }
    /**
     * 渲染电压数据图
     * @return mixed
     */
    public function voltage(){
        if(request()->isGet()){
        $this->dataDeal('c_voltage','电压');
        return $this->fetch();
        }
        $this->error('数据错误');
    }
    public function dataDeal($dataType,$name){
        $no = input('get.no')?input('get.no'):1;
        $type=input('get.type')?input('get.type'):'day';
        $data = [];
        $date = [];
        $current= [];
        $nos = [];
        $devicesn = input('get.devicesn');
        $list = $this->monitor->getMonitor($devicesn,$type);
        foreach ($list as $k=>$v){
            $current = unserialize($v[$dataType]);
            $date[$k] = $v['create_time'];
            $data[$k] = $current[$no-1]['Value'];
        }
        foreach ($current as $v) {
            $nos[] = $v['No'];
        }
        if($type == "day")
            $content ="今天";
        elseif($type == "week")
            $content="本周";
        else
            $content="本月";
        $this->assign([
                'title'=>$name.'数据渲染图',
                'content' =>$content,
                'data' => json_encode($data),
                'date' => json_encode($date),
                'nos' => $nos,
                'no' => $no,
                'type'=>$type,
                'devicesn' => $devicesn,
        ]);

    }
    /**
     * 渲染温度的数据图
     * @return mixed
     */
    public function temp(){
        if(request()->isGet()){
            $type=input('get.type')?input('get.type'):'day';
            $data = [];
            $date = [];
            $devicesn = input('get.devicesn');
            $list = $this->monitor->getMonitor($devicesn,$type);
            foreach ($list as $k=>$v){
                $date[$k] = $v['create_time'];
                $data[$k] = $v['c_temp'];
            }
            if($type == "day")
                $content ="今天";
            elseif($type == "week")
                $content="本周";
            else
                $content="本月";
            return $this->fetch('',[
                'title'=>'温度数据渲染图',
                'content' =>$content,
                'data' => json_encode($data),
                'date' => json_encode($date),
                'type'=>$type,
                'devicesn' => $devicesn,
            ]);
        }
        $this->error('数据错误');
    }

	public function split($data)
    {
		foreach ($data as $k => $v)
		{
					# code...
			
		}		
	}

    /**
     * 数据监控警报模块
     */
    public function warning()
    {
        $list = model('Device')->select();
        foreach ($list as $v){
           $res = db('Warning')->where('c_devicesn',$v['c_devicesn'])->find();
           $relay = db('Relay')->where('c_devicesn',$v['c_devicesn'])->find();
           if(empty($res)){
                $v['warning'] = 0;
            }else{
                $v['warning'] = 1;
            }
            if(empty($relay)){
                $v['relay'] = 0;
            }else{
                $v['relay'] = 1;
            }
        }
        return $this->fetch('',[
            'title' => '异常警报监控',
            'list' => $list,
        ]);
    }

    /**
     * 电压电流温度监控模块
     */
    public function cvtwarn(){
        $sn = input('get.devicesn');
        if(empty($sn)){
            $this->error('缺少设备编号');
        }
        $list = db('Warning')->where('c_devicesn',$sn)->paginate();
       return $this->fetch('',[
           'title' => '异常数据监控',
            'list' => $list,
               'sn' => $sn,
           ]
       );
    }

    /**
     * 继电器开合统计
     */
    public function relaywarn()
    {
        $sn = input('get.devicesn');
        if(empty($sn)){
            $this->error('缺少设备编号');
        }
        $list = db('Relay')->where('c_devicesn',$sn)->paginate();
        return $this->fetch('',[
                'title' => '继电器数据监控',
                'list' => $list,
                'sn' => $sn,
            ]
        );
    }

}
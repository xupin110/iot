<?php



class Device
{
    protected $client;
//    private $data = [
//            "DeviceSn" => "127.0.0.1",
//            "RequestControl" => "1",
//            "Relay" => [
//                "1" => "0",
//                "2" => "0",
//                "3" => "0",
//            ],
//            "Vdc" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
//            "Current" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
//            "Temp" => "40",
//            "Lng" => "135.2342342",
//            "Lat" => "23.9978979",
//            "ConnectType" => "GPS",
//        ];
private $data = [
                "DeviceSn"=>"cnki-232423",
                "RequestControl"=>"1",
                "Relay"=>[
                "1"=>"0",
                "2"=>"0",
                "3"=>"0"
                ],
                "Vdc"=>[["Type"=>"DC","Value"=>"4","No"=>"1"],["Type"=>"AC","Value"=>"4","No"=>"2"]],
                "Current"=>[["Type"=>"DC","Value"=>"4","No"=>"1"],["Type"=>"AC","Value"=>"4","No"=>"2"]],
                "Temp"=>"40",
                "Lng"=>"135.2342342",
                "Lat"=>"23.9978979",
                "ConnectType">"wifi"
                ];
    public function __construct(){
        // 实例化客户端
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
    //     $this->client->set([
    //         'open_eof_check' => true, //打开EOF检测
    //         'package_eof' => "\r\n", //设置EOF
    // //     // 'open_length_check' => 1,
    // //     // 'package_max_length' => 2465792, //2M默认最大长度,
    // //     // 'package_length_type' => 'N',
    // //     // 'package_body_offset' => 16,
    // //     // 'package_length_offset' => 0,
    // ]);
        // 注册回调函数
        $this->client->on('Connect', [$this, 'onConnect']);
        $this->client->on('Receive', [$this, 'onReceive']);
        $this->client->on('Error', [$this, 'onError']);
        $this->client->on('Close', [$this, 'onClose']);

        //发起连接
         $this->client->connect('47.95.220.109', 8090, 30);
        //$this->client->connect('127.0.0.1', 8901, 30);
        // print_r($this->client);

    }
  

    // 注册连接成功回调
    public function onConnect(\swoole_client $cli)
    {
        $data = $this->data;
        // $data['Relay'] = [
        //         "1" => "0",
        //         "2" => "0",
        //         "3" => "0"
        //     ];
        print_r($data);
        $cli->send($this->data($data));
    }
    public function data($data = []){
        // $data = [
        //     "DeviceSn" => "127.0.0.1",
        //     "RequestControl" => "1",
        //     "Relay" => [
        //         "1" => "0",
        //         "2" => "0",
        //         "3" => "0",
        //     ],
        //     "Vdc" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
        //     "Current" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
        //     "Temp" => "40",
        //     "Lng" => "135.2342342",
        //     "Lat" => "23.9978979",
        //     "ConnectType" => "GPS",
        // ];
        $uid = 0;
        $serid = intval(strval(strstr(microtime(), ' ', true) * 1000 * 1000) . rand(100, 999));
        $length = strlen(serialize($data));
        $type = 1;
        $data = serialize($data);
        $body = pack('NNNN',$length,$type,$uid,$serid).$data; 
        return $body;       
    }

    // 注册数据接收回调
    public function onReceive(\swoole_client $cli, $data)
    {
        $a = substr($data, 16);
        $b = unserialize($a);
        print_r($b);
        if(isset($b['Relay']))
        {
            if($b['Relay']['1'] == '1'){
                $ret = $this->data;
                $ret['Relay']['1'] = '1';
                $ret = $this->data($ret);
                echo 'open-'.PHP_EOL;
                print_r($ret);
                $cli->send($ret);            
            }else if ($b['Relay']['1'] == '0') {
                # code...
                $ret =$this->data;
                $ret['Relay']['1'] = '0';
                $ret = $this->data($ret);
                echo "close".PHP_EOL;
                print_r($ret);
                $cli->send($ret); 
            }

        }

        //     print_r($b);
        //     print_r($b['errno']);
        //     print_r($b['call']);
        // if($b['errno'] == '8010'){
        // $data = [
        //     "DeviceSn" => "127.0.0.1",
        //     "RequestControl" => "1",
        //     "Relay" => [
        //         "1" => "0",
        //         "2" => "0",
        //         "3" => "0",
        //     ],
        //     "Vdc" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
        //     "Current" => [['Type' => 'DC', 'Value' => '4', "No" => "1"], ['Type' => 'AC', 'Value' => '4', "No" => "2"]],
        //     "Temp" => "40",
        //     "Lng" => "135.2342342",
        //     "Lat" => "23.9978979",
        //     "ConnectType" => "GPS",
        // ];
        // $uid = 0;
        // $serid = intval(strval(strstr(microtime(), ' ', true) * 1000 * 1000) . rand(100, 999));
        // $length = strlen(serialize($data));
        // $type = 1;
        // $data = serialize($data);
        // $body = pack('NNNN',$length,$type,$uid,$serid).$data;
        // $cli->send($body);
        
        // }


    }

    // 注册连接失败回调
    public function onError(\swoole_client $cli)
    {
        echo "Connect failed\n";
    }

    // 注册连接关闭回调
    public function onClose(\swoole_client $cli)
    {
        echo "Connection close\n";
        // $this->reConnect();
    }
    public function reConnect(){
        // $this->client->connect('127.0.0.1', 8901, 0.5);
    }
}

$obj = new Device();

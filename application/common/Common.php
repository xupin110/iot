<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-1-22
 * Time: 上午10:58
 */

namespace app\common;

use think\Controller;

class Common extends Controller {
	public $config = [];
	/**
	 * 工具类执行构造
	 * */
	public function __construct() {
		//执行上级父类构造
		parent::__construct();
		//获取公用配置信息
		$this->setConfig();
	}

	/**
	 * 实例化微信类
	 * @return WxClient 返回微信接口对象
	 * */
	public final function wxlin() {
		static $wxlin;
		if (isset($wxlin)) {
			return $wxlin;
		}

		#实例化微信接口类
		$wxlin = new WxClient($this->config['appid'], $this->config['appsecret']);
		#配置微信接口对象
		$wxlin->config['way'] = C('WINXIN_WAY');
		$wxlin->config['chepath'] = APP_PATH . 'Runtime/Cache';
		$wxlin->config['redis'] = array(
			'host' => C('REDIS_HOST'),
			'port' => C('REDIS_PORT'),
			'pass' => C('REDIS_AUTH'),
		);
		$wxlin->config['access_token_name'] = "access_token-{$this->config['appid']}";
		$wxlin->config['jsapi_ticket_name'] = "jsapi_ticket-{$this->config['appid']}";
		$wxlin->run();
		return $wxlin;
	}

	/**
	 * 取得优户对象
	 * @return YouHu
	 */
	public final function youhu() {
		static $_youhu;
		if (!isset($_youhu)) {
			$_youhu = new YouHu();
		}
		return $_youhu;
	}

	/**
	 * 获取授权access_token和当前openid
	 * @access public
	 * @param string $appid 公共号APPID
	 * @param string $appsecret 公众号APPSECRET
	 * @param bool $scope 授权方式
	 * @param callable|null $callback
	 * @return string
	 */
	public final function getWxClientUser($appid = NULL, $appsecret = NULL, $scope = false, callable $callback = NULL) {
		//公众号信息
		// $appid = $appid ?: $this->config['appid'];
		// $appsecret = $appsecret ?: $this->config['appsecret'];
		$wx_pay = C('WX_PAY');
		$appid = $wx_pay['appid'];
		$appsecret = $wx_pay['appsecret'];
		$scope_val = $scope ? 'snsapi_userinfo' : 'snsapi_base';
		$code = I('get.code');
		$self_url = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
		//dump($self_url);die;
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$self_url}&response_type=code&scope={$scope_val}&state=STATE&connect_redirect=1#wechat_redirect";
		//开启session
		session_start();
		$wxuserinfo = &$_SESSION['wxuser'];
		if (!isset($wxuserinfo)) {
			//当前access_token验证失败,则重新拉取access信息
			$wxuser = $this->wxlin()->oauth2($code, $appid, $appsecret);
			if (!$wxuser) {
				//code拉取失败，code验证失败重定向重新拉取
				header("location:{$url}");
				exit;
			}
			$wxuserinfo = $wxuser;
			if (isset($callback)) {
				$callback($wxuser);
			}
		}
		return true;
	}

	/**
	 * 设置配置信息
	 * @access private
	 * */
	private final function setConfig() {

		$this->config['name'] = '充电能源管理';
		$this->assign('_config', $this->config);
	}

	//导出到excel封装
	public function exportExcel($fileName = 'table', $expCellName, $expTableData) {
		header('Content-Type:text/html; charset=utf-8');
		import("Vendor.PHPExcel.PHPExcel");
		import("Vendor.PHPExcel.Writer.Excel5");
		import("Vendor.PHPExcel.IOFactory.php");

		$xlsTitle = iconv('utf-8', 'gb2312', $fileName); //文件名称
		$xlsName = $fileName . date('_Y-m-d-H-i'); //or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);

		$objPHPExcel = new \PHPExcel();
		$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1'); //合并单元格
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $fileName . '  Export time:' . date('Y-m-d H:i:s'));
		for ($i = 0; $i < $cellNum; $i++) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
		}
		// Miscellaneous glyphs, UTF-8
		for ($i = 0; $i < $dataNum; $i++) {
			for ($j = 0; $j < $cellNum; $j++) {
				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
			}
		}
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
		header("Content-Disposition:attachment;filename=$xlsName.xls"); //attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
}
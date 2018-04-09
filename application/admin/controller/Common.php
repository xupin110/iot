<?php
namespace app\admin\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Upload;

class Common extends Base {

	// 报错信息
	protected $error = '';

	//upload文件上传
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-03
	 * @param    string      $path [description]
	 * @return   [type]            [description]
	 */
	public function upload($path = '') {
		$upload = new Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->rootPath = './Public/Admin/upload_img/'; // 设置附件上传根目录
		$upload->savePath = '/' . $path . '/'; // 设置附件上传（子）目录
		$upload->subName = false;
		// 上传文件
		$info = $upload->upload();
		if (!$info) {
// 上传错误提示错误信息
			echo json_encode(['status' => 0, 'message' => $upload->getError()]);
			exit;
		} else {
// 上传成功
			//$this->success('上传成功！');
			foreach ($info as $file) {
				$url = '/Public/Admin/upload_img' . $file['savepath'] . $file['savename']; //路径
			}
			//写入数据库
			echo json_encode(['status' => 1, 'message' => '上传成功', 'data' => ['url' => $url]]);
		}
	}

	public function upload5($path = '') {
		$upload = new Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->rootPath = './Public/Admin/uploadify5/'; // 设置附件上传根目录
		$upload->savePath = '/' . $path . '/'; // 设置附件上传（子）目录
		$upload->subName = false;
		// 上传文件
		$info = $upload->upload();
		if (!$info) {
// 上传错误提示错误信息
			echo json_encode(['status' => 0, 'message' => $upload->getError()]);
			exit;
		} else {
// 上传成功
			//$this->success('上传成功！');
			foreach ($info as $file) {
				$url = '/Public/Admin/uploadify5' . $file['savepath'] . $file['savename']; //路径
			}
			//写入数据库
			echo json_encode(['status' => 1, 'message' => '上传成功', 'data' => ['url' => $url]]);
		}
	}
	//点击图片删除
	public function clickremove() {
		//接受ajax传过来的地址
		$url = I('get.url');
		//删除本地文件
		if (unlink('.' . $url)) {
			echo json_encode(['status' => 1, 'message' => '删除成功']);
		} else {
			echo json_encode(['status' => 0, 'message' => '删除失败']);
		}
	}

	//导出到excel封装
	public function exportExcel($fileName = 'table', $expCellName, $expTableData) {
		$xlsTitle = iconv('utf-8', 'gb2312', $fileName); //文件名称
		$xlsName = $fileName . date("_Y.m.d_H.i.s"); //or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		import("Vendor.PHPExcel.PHPExcel");
		import("Vendor.PHPExcel.Writer.Excel5");
		import("Vendor.PHPExcel.IOFactory.php");
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

	/**
	 * 获取上级接口调用失败错误信息
	 * @return string
	 * */
	public function getError() {
		return $this->error;
	}

	/**
	 * 生成二维码
	 * @param  string  $url 跳转地址
	 * @param  int  $id  参数
	 * @param  string  $logo  logo图地址
	 * @param  integer $level
	 * @param  integer $size  生成图片大小
	 * @return String
	 */

	public function qrCode($url = "", $id, $logo = '', $level = 3, $size = 300) {
		header("Content-Type: text/html; charset=utf-8");
		//容错级别
		$errorCorrectionLevel = intval($level);
		//生成图片大小
		$matrixPointSize = intval($size);
		// 生成文件夹目录
		$path = 'admin/qrcode/';
		//文件夹不存在，先生成文件夹
		if (!file_exists($path)) {
			if (!mkdir($path)) {
				$this->error = '文件夹生成失败，没有权限.';
				return false;
			}
		}
		$pathdate = 'admin/qrcode/' . date('Y-m-d', time());
		//文件夹不存在，先生成文件夹
		if (!file_exists($pathdate)) {
			if (!mkdir($pathdate)) {
				$this->error = '文件夹生成失败，没有权限.';
				return false;
			}
		}
		//第二个参数false的意思是不生成图片文件，如果你写上‘device.png’则会在根目录下生成一个png格式的图片文件
		$pathQr = $pathdate . '/' . 'device' . $id . '.png';
		$this->createQrcode($url, $logo, $pathQr, $errorCorrectionLevel, $matrixPointSize);
		return $pathQr;
	}

	public function createQrcode($url, $logo, $pathQr, $errorCorrectionLevel, $matrixPointSize) {

		$qrCode = new QrCode($url);
		$qrCode->setSize($matrixPointSize);

		// Set advanced options
		$qrCode->setWriterByName('png');
		$qrCode->setMargin(10);
		$qrCode->setEncoding('UTF-8');
		$qrCode->setErrorCorrectionLevel('high');
		$qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
		$qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
		$qrCode->setLabel('Scan the code', 16, 'admin/library/font-awesome-4.5.0/fonts/FontAwesome.otf', 'right');
		$qrCode->setLogoPath($logo);
		$qrCode->setLogoWidth(150);
		// $qrCode->setRoundBlockSize(true);
		$qrCode->setValidateResult(false);

		// Directly output the QR code
		header('Content-Type: ' . $qrCode->getContentType());
		// echo $qrCode->writeString();

		// Save it to a file
		$res = $qrCode->writeFile($pathQr);

		return $res;

	}

}
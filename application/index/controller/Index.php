<?php
namespace app\index\controller;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class Index {
	public function index() {

		$qrCode = new QrCode('Life is too short to be generating QR codes');
		$qrCode->setSize(300);

		// Set advanced options
		$qrCode->setWriterByName('png');
		$qrCode->setMargin(10);
		$qrCode->setEncoding('UTF-8');
		$qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
		$qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
		$qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
		$qrCode->setLabel('Scan the code', 16, 'admin/library/font-awesome-4.5.0/fonts/FontAwesome.otf', 'right');
		$qrCode->setLogoPath('admin/images/qw_logo.png');
		$qrCode->setLogoWidth(150);
		// $qrCode->setRoundBlockSize(true);
		$qrCode->setValidateResult(false);

		// Directly output the QR code
		header('Content-Type: ' . $qrCode->getContentType());
		// echo $qrCode->writeString();

		// Save it to a file
		$qrCode->writeFile(__DIR__ . '/qrcode.png');

		// Create a response object
		// $response = new QrCodeResponse($qrCode);
	}

	public function hello($name = 'ThinkPHP5') {
		return 'hello,' . $name;
	}
}

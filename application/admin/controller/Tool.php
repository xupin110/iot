<?php
namespace app\admin\controller;
use think\File;
use think\Request;

class Tool extends Base {

	/*验证码输出*/
	public function Verify() {
		$Verify = new \think\Verify();
		$Verify->length = 4;
		$Verify->fontSize = 22;
		$Verify->imageW = 255;
		$Verify->entry();
	}
// 	//上传图片
	// 	public function upload() {
	// 		$upload = new \think\Upload(); // 实例化上传类
	// 		$upload->maxSize = 3145728; // 设置附件上传大小
	// 		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
	// 		$upload->rootPath = './Public/'; // 设置附件上传根目录
	// 		$upload->savePath = '/uploadimg/'; // 设置附件上传（子）目录
	// 		$upload->subName = array('date', 'Ymd');
	// 		$upload->hash = true;
	// 		// 上传文件
	// 		$info = $upload->upload();
	// 		//dump($info);die;
	// 		if (!$info) {
	// // 上传错误提示错误信息
	// 			$this->json(array('error' => 1, 'message' => $upload->getError()));
	// 		} else {
	// //上传成功！带路径。
	// 			$url = 'http://' . $_SERVER['SERVER_NAME'];
	// 			$realPath = $url . '/Public' . $info['imgFile']['savepath'] . $info['imgFile']['savename'];
	// 			$this->json(array('error' => 0, 'url' => $realPath), 'json');

// 		}
	// 	}
	public function upload() {
		//通过tp5 request的方法获得上传的文件
		$files = request()->file('imgFile');
		//
		$files_path = $files->move('upload');

		if ($files_path && $files_path->getPathname()) {

			return json(['error' => 0, 'url' => '\\' . $files_path->getPathname()]);

		} else {
			return json(['error' => 1, 'message' => 'upload error']);
		}

	}

	//上传视频
	public function uploadVideo() {
		$upload = new \think\Upload(); // 实例化上传类
		$upload->maxSize = 0; // 设置附件上传大小
		$upload->exts = array('rm', 'rmvb', 'avi', 'mp4', '3gp'); // 设置附件上传类型
		$upload->rootPath = './Public/'; // 设置附件上传根目录
		$upload->savePath = '/Video/'; // 设置附件上传（子）目录
		$upload->subName = array('date', 'Ymd');
		// 上传文件
		$info = $upload->upload();
		//dump($info);die;
		if (!$info) {
// 上传错误提示错误信息
			$this->json(array('error' => 1, 'message' => $upload->getError()), 'json');
		} else {
//上传成功！带路径。
			$url = 'http://' . $_SERVER['SERVER_NAME'];
			$realPath = $url . '/Public' . $info['videoFile']['savepath'] . $info['videoFile']['savename'];
			//dump($realPath);die;
			$this->json(array('error' => 0, 'url' => $realPath), 'json');

		}
	}
	/**
	 * guid生成
	 */
	public function guid() {
		if (function_exists('com_create_guid')) {
			return com_create_guid();
		} else {
			mt_srand((double) microtime() * 10000);
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);
			$uuid = chr(123)
			. substr($charid, 0, 8) . $hyphen
			. substr($charid, 8, 4) . $hyphen
			. substr($charid, 12, 4) . $hyphen
			. substr($charid, 16, 4) . $hyphen
			. substr($charid, 20, 12)
			. chr(125);
			$uuid1 = rtrim($uuid, "}");
			$guid = ltrim($uuid1, "{");
			return $guid;
		}
	}
	public function uuid() {
		$pid = getmypid(); //进程id。在同一台机器下高并发时，极易得到相同的毫秒
		time_nanosleep(0, 1000); //延时1000纳秒=1毫秒。同一进行连续使用本函数时，可能得到相同的毫秒，于是需要这个延时来保证每次得到的毫秒未被使用。
		$timetick = microtime(TRUE) * 1000; //微秒
		$uuid = hash('ripemd160', $pid . '+' . $timetick);
		return $uuid;
	}

	/**
	 * 编辑器格式转换
	 */
	public function quotes($content) {
		//如果magic_quotes_gpc=Off，那么就开始处理
		if (!get_magic_quotes_gpc()) {
			//判断$content是否为数组
			if (is_array($content)) {
				//如果$content是数组，那么就处理它的每一个单无
				foreach ($content as $key => $value) {
					if ($key['details'] = 'details') {
						$content[$key] = addslashes($value);
					}
				}
			} else {
				//如果$content不是数组，那么就仅处理一次
				addslashes($content);
			}
		} else {
			//如果magic_quotes_gpc=On，那么就不处理
		}
		//返回$content
		return $content;
	}

}
?>
<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-03-01
 * @define  the file is the entrance of the system
 */
if (!extension_loaded('swoole')) {
	echo "Please install the Swoole expansion\n";
	exit();
}
if (!extension_loaded('pcre')) {
	echo "Please install the pcre expansion\n";
	exit();
}
define('SERVICE', true);
define('WEBPATH', __DIR__);
define('SWOOLE_SERVER', true);
date_default_timezone_set("Asia/Shanghai");
function getRunPath() {
	$path = Phar::running(false);
	if (empty($path)) {
		return __DIR__;
	} else {
		return dirname($path) . "/../crontab_log";
	}

}

const LOAD_SIZE = 8192; //最多载入任务数量
const TASKS_SIZE = 1024; //同时运行任务最大数量
const ROBOT_MAX = 128; //同时挂载worker数量
const WORKER_NUM = 4; //worker进程数量
const TASK_NUM = 4; //task进程数量

define("CENTRE_PORT", 8901);
define('DEBUG', 'on');
define("CENTER_HOST", "127.0.0.1");
$env = 'dev';
define('ENV_NAME', $env);
define('PUBLIC_PATH', '/website/iot/');
/**
 * require the swoole_framework
 */
require_once PUBLIC_PATH . 'libs/lib_config.php';
/**
 * require the thinkphp
 */
require PUBLIC_PATH . '/thinkphp/base.php';
think\Container::get('app')->run()->send();

Swoole::$php->config->setPath(__DIR__ . '/configs/' . ENV_NAME); //共有配置
Swoole::$php->config->setPath(__DIR__ . '/configs'); //共有配置
Swoole\Loader::addNameSpace('App', __DIR__ . '/App');
Swoole\Loader::addNameSpace('Lib', __DIR__ . '/Lib');
Swoole\Loader::addNameSpace('Device', __DIR__ . '/Device');
Swoole\Loader::addNameSpace('model', __DIR__ . '/model');

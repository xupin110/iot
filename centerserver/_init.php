<?php
if (!extension_loaded('swoole')){
    echo "Please install the Swoole expansion\n";
    exit();
}
if (!extension_loaded('pcre')){
    echo "Please install the pcre expansion\n";
    exit();
}
define('SERVICE', true);
define('WEBPATH', __DIR__);
define('SWOOLE_SERVER', true);
date_default_timezone_set("Asia/Shanghai");
function getRunPath()
{
    $path = Phar::running(false);
    if (empty($path)) return __DIR__;
    else return dirname($path)."/../crontab_log";
}

const LOAD_SIZE = 8192;//最多载入任务数量
const TASKS_SIZE = 1024;//同时运行任务最大数量
const ROBOT_MAX = 128;//同时挂载worker数量
const WORKER_NUM = 4;//worker进程数量
const TASK_NUM = 4;//task进程数量

define("CENTRE_PORT",8901);
$env = get_cfg_var('env.name');
if ($env == "product")
{
    define('DEBUG', 'off');
    define("CENTER_HOST","127.0.0.1");
}elseif ($env == "test"){
    define('DEBUG', 'on');
    define("CENTER_HOST","127.0.0.1");
} else {
    $env = 'dev';
    define('DEBUG', 'on');
    define("CENTER_HOST","127.0.0.1");
}

define('ENV_NAME', $env);

define('PUBLIC_PATH', '/website/iot/');
require_once PUBLIC_PATH.'libs/lib_config.php';

// define('APP_PATH', __DIR__ . '/app/');
require PUBLIC_PATH . '/thinkphp/base.php';
think\Container::get('app')->run()->send();
// require_once PUBLIC_PATH.'libs/lib_config.php';

Swoole::$php->config->setPath(__DIR__ . '/configs/' . ENV_NAME);//共有配置
Swoole::$php->config->setPath(__DIR__ . '/configs');//共有配置
Swoole\Loader::addNameSpace('App', __DIR__ . '/App');
Swoole\Loader::addNameSpace('Lib', __DIR__ . '/Lib');
Swoole\Loader::addNameSpace('Device', __DIR__ . '/Device');



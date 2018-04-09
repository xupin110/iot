<?php
/**
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-19
 * Time: 下午2:27
 */

namespace App;

use Lib\Donkeyid;
use Lib\LoadTasks;
use Lib\Util;

class Tasks
{

    /**
     * 获取分组列表
     * @return array
     */
    public static function getGroups($uid = "")
    {
        echo "APP ------ Tasks ----------getGroups".PHP_EOL;
        $table = table("crongroup");
        $table->primary = "gid";
        $list = [];
        if (empty($uid)) {
            $list = $table->gets(["order" => "gid asc"]);
        } else {
            $t = table("group_user");
            $t->select = "*";
            $gids = $t->gets(["uid" => $uid]);
            $tmp = [];
            foreach ($gids as $gid) {
                $tmp[] = $gid["gid"];
            }
            if (!empty($tmp)) {
                $list = $table->gets(["in" => ["gid", $tmp]]);
            }
        }

        if (empty($list)) {
            $list = [];
        } else {
            $data = [];
            foreach ($list as $value) {
                $data[$value["gid"]] = $value["gname"];
            }
            $list = $data;
        }
        return $list;
    }

    /**
     * 获取单个分组
     * @param $gid
     * @return array
     */
    public static function getGroup($gid)
    {
        echo "APP ------ Tasks ----------getGroup".PHP_EOL;
        
        $table = table("crongroup");
        $table->primary = "gid";
        $data = $table->get($gid);
        if (!$data->exist()) {
            return Util::errCodeMsg(101, "不存在");
        }
        $t = table("group_user");
        $t->select = "uid";
        $uids = $t->gets(["gid" => $gid]);
        $da = [];
        foreach ($uids as $v) {
            $da[] = $v["uid"];
        }
        return Util::errCodeMsg(0, "", ["gid" => $gid, "gname" => $data["gname"], "uids" => $da]);
    }

    /**
     * 添加分组
     * @param $group
     * @return array
     */
    public static function addGroup($group)
    {
        echo "APP ------ Tasks ----------addGroup".PHP_EOL;
        if (empty($group)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        $table = table("crongroup");
        $table->primary = "gid";
        $uids = $group["uids"];
        unset($group["uids"]);
        if (!($gid = $table->put($group))) {
            return Util::errCodeMsg(102, "添加失败");
        }
        $t = table("group_user");
        foreach ($uids as $uid) {
            $t->put(["gid" => $gid, "uid" => $uid]);
        }
        return Util::errCodeMsg(0, "保存成功", $gid);
    }

    /**
     * 修改分组
     * @param $gid
     * @param $group
     * @return array
     */
    public static function updateGroup($gid, $group)
    {
        echo "APP ------ Tasks ----------updateGroup".PHP_EOL;
        if (empty($gid) || empty($group)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        $table = table("crongroup");
        $table->primary = "gid";
        $uids = [];
        if (isset($group["uids"])) {
            $uids = $group["uids"];
            unset($group["uids"]);
        }
        if (!$table->set($gid, $group)) {
            return Util::errCodeMsg(102, "更新失败");
        }
        $t = table("group_user");
        $t->dels(["gid" => $gid]);
        foreach ($uids as $uid) {
            $t->put(["gid" => $gid, "uid" => $uid]);
        }
        return Util::errCodeMsg(0, "更新成功", $gid);
    }

    /**
     * 删除分组
     * @param $gid
     * @return array
     */
    public static function deleteGroup($gid)
    {
        echo "APP ------ Tasks ----------deleteGroup".PHP_EOL;
        if (empty($gid)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        if (table("crontab")->count(["gid" => $gid]) > 0) {
            return Util::errCodeMsg(101, "该分组下有定时任务，不能删除");
        }
        $table = table("crongroup");
        $table->primary = "gid";
        if (!$table->del($gid)) {
            return Util::errCodeMsg(102, "删除失败");
        }
        $t = table("group_user");
        $t->dels(["gid" => $gid]);
        return Util::errCodeMsg(0, "删除成功");
    }


    /**
     * 获取任务列表
     * @return array
     */
    public static function getList($gets = [], $page = 1, $pagesize = 10)
    {
        echo "APP ------ Tasks ----------getList".PHP_EOL;

        // //页数
        if (!empty($pagesize)) {
            $gets['pagesize'] = intval($pagesize);
        } else {
            $gets['pagesize'] = 20;
        }
        if (isset($gets["agentid"]) && !empty($gets["agentid"])) {
            $gets["where"] = "agents REGEXP '(^{$gets["agentid"]}$)|(,{$gets["agentid"]})|({$gets["agentid"]},)'";
            unset($gets["agentid"]);
        }
        $gets['page'] = !empty($page) ? $page : 1;
        $pager = "";
        $list = table("crontab")->gets($gets, $pager);
        $tasks = LoadTasks::getTasks();
        $group = self::getGroups();
        foreach ($list as &$task) {
            $tmp = $tasks->get($task["id"]);
            $task["runStatus"] = $tmp["runStatus"];
            $task["runTimeStart"] = $tmp["runTimeStart"];
            $task["runUpdateTime"] = $tmp["runUpdateTime"];
            if (isset($group[$task["gid"]])) {
                $task["gname"] = $group[$task["gid"]];
            }
        }
        return ["total" => $pager->total, "rows" => $list];

    }

    /**
     * 获取单个任务
     * @param $id
     * @return array
     */
    public static function get($id)
    {
        echo "APP ------ Tasks ----------get".PHP_EOL;
        $tasks = table("crontab");
        $task = $tasks->get($id);
        if (!$task->exist($id)) {
            return Util::errCodeMsg(101, "不存在");
        }
        $data["id"] = $id;
        $data["gid"] = $task["gid"];
        $data["taskname"] = $task["taskname"];
        $data["rule"] = $task["rule"];
        $data["runnumber"] = $task["runnumber"];
        $data["timeout"] = $task["timeout"];
        $data["execute"] = $task["execute"];
        $data["status"] = $task["status"];
        $data["runuser"] = $task["runuser"];
        $data["manager"] = $task["manager"];
        $data["agents"] = $task["agents"];
        $group = self::getGroups();
        if (isset($group[$task["gid"]])) {
            $data["gname"] = $group[$task["gid"]];
        }
        return Util::errCodeMsg(0, "", $data);
    }

    /**
     * 添加任务
     * @param $task
     * @return array
     */
    public static function add($task)
    {
        echo "APP ------ Tasks ----------add".PHP_EOL;
        if (empty($task)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        $task["id"] = Donkeyid::getInstance()->dk_get_next_id();
        $ids = LoadTasks::saveTasks([$task]);
        if ($ids === false) {
            return Util::errCodeMsg(102, "添加失败");
        }
        return Util::errCodeMsg(0, "保存成功", $ids);
    }

    /**
     *  修改任务
     * @param $id
     * @param $task
     * @return array
     */
    public static function update($id, $task)
    {
        echo "APP ------ Tasks ----------update".PHP_EOL;
        if (empty($id) || empty($task)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        if (!LoadTasks::updateTask($id, $task)) {
            return ["code" => 102, "msg" => "更新失败"];
        }
        return Util::errCodeMsg(0, "更新成功");
    }

    /**
     * 删除任务
     * @param $id
     * @return array
     */
    public static function delete($id)
    {
        echo "APP ------ Tasks ----------delete".PHP_EOL;
        if (empty($id)) {
            return Util::errCodeMsg(101, "参数为空");
        }
        if (!LoadTasks::delTask($id)) {
            return Util::errCodeMsg(102, "删除失败");
        }
        return Util::errCodeMsg(0, "删除成功");
    }


    /**
     * 获取即将运行和已经运行的任务
     */
    public static function getRuntimeTasks($page = 1, $size = 20)
    {
        echo "APP ------ Tasks ----------getRuntimeTasks".PHP_EOL;
        $start = ($page - 1) * $size;
        $end = $start + $size;
        $data = [];
        $list = \Lib\Tasks::$table;
        // print_r($list);
        $tasks = LoadTasks::getTasks();
        // print_r($tasks);
        $n = 0;
        foreach ($list as $id => $rb) {
            $n++;
            if ($n <= $start) {
                continue;
            }
            if ($n > $end) {
                break;
            }
            $tmp = $tasks->get($rb["id"]);
            $rb["taskname"] = $tmp["taskname"];
            $data[$id] = $rb;
        }
        return ["total" => count($list), "rows" => $data];
    }

}
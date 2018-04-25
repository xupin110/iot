<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/4/24
 * Time: 21:54
 */

namespace app\admin\model;
use think\Model;


class Monitor extends Model
{
    public $startDay;
    public $startWeek;
    public $startMonth;
    public $endDay;
    /**
     * 初始化当天数据
     * 初始化当周数据
     * 初始化当月数据
     */
    public function initialize(){
        $this->startDay = strtotime('00:00:00');
        $this->startWeek = strtotime('-1 week');
        $this->startMonth = strtotime('-1 month');
        $this->endDay = time();
    }

    /**
     * @param $devicesn
     * @return array|\PDOStatement|string|\think\Collection
     * 获取一天之类的电流数据
     */
    public function getMonitor($devicesn,$type){
        switch ($type){
            case "day":
                $where[] = ['create_time','between',[$this->startDay,$this->endDay]];
                break;
            case "week":
                        $where[] = ['create_time','between',[$this->startWeek,$this->endDay]];
                        break;
            case "month":
                $where[] = ['create_time','between',[$this->startMonth,$this->endDay]];
                break;
            default:
                $where[] = ['create_time','between',[$this->startDay,$this->endDay]];
        }
        $where[] = ['c_devicesn','=',$devicesn];
        return $this->where($where)->select();
    }
}
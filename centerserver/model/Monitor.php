<?php
namespace model;
class Monitor {
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-09
	 * @return   [type]      [封装的monitor数据库类]
	 */
	protected static $monitor;
	public static function getInstance() {
		self::$monitor = new self();
		return self::$monitor;

	}
	/**
	 * @param    [type]      $where [where condition]
	 * @return   [type]             [return all list]
	 */
	public static function getAllMonitors($where = []) {
		return db('monitor')->where($where)->select();
	}
	/**
	 * @param    [type]      $where [condition]
	 * @return   [type]             [return one res]
	 */
	public static function getOneMonitor($where) {
		return db('monitor')->where($where)->find();
	}
	public static function updateMonitor($data) {
		return db('monitor')->update($data);
	}
}
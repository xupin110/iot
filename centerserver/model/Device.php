<?php
namespace model;
class Device {
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-09
	 * @return   [type]      [封装的device数据库类]
	 */
	protected static $device;
	public static function getInstance() {
		self::$device = new self();
		return self::$device;

	}
	/**
	 * @param    [type]      $where [where condition]
	 * @return   [type]             [return all list]
	 */
	public static function getAllDevices($where = []) {
		return db('Device')->where($where)->select();
	}
	public static function getOneColumns($where = [], $column = []){
		return db('Device')->where($where)->column($column);
	}
	/**
	 * @param    [type]      $where [condition]
	 * @return   [type]             [return one res]
	 */
	public static function getOneDevice($where) {
		return db('Device')->where($where)->find();
	}
	public static function updateDevice($data) {
		return db('Device')->update($data);
	}
	public static function insertDevice($data){
		return db('Device')->insertGetId($data);
	}
}
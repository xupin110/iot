IOT 物联网开发 (基于Swoole扩展)
==============
1.概述
--------------
+ 基于tp5.1 框架
+ 根据实际项目开发，用于工作中
+ web后台 +socket服务器 +硬件端数据处理
+ 后台实时刷新 硬件端的状态
+ 本项目借鉴了开源项目 donkey 并加以修改
+ 请使用swoole扩展2.1.0+


2.架构图
--------------
![](https://raw.githubusercontent.com/Lxido/iot/master/public/gitimg/IOT.png)

3.后台开发
--------------

    
4.socket服务器开发
-----------


5.模拟充电桩设备
----------

6.安装依赖
----------

    安装二维码扩展
    composer require endoriod/qr-code

    安装基于swoole的官方框架
    `composer require matyhtf/swoole_framework`
7.开发常见问题
----------
> web端怎么和socket服务器通信
> 
> socket怎么和设备通信
> 
> 数据传输协议怎么拟定

## Introduction

This is a plugin for PMBlog. PMBlog is a static blog generator which is written by PHP. Before use this plugin, you should [download](http://upyun.gitcafe.com/lizheming/PMBlog) & install this program. After enable this plugin, you can upload your blog to your qiniu space.

## Requirement

 - PHP >= 5.2  
 - cURL

## HowTo

 1. Create A File Space in Qiniu
 1. In [this page](https://portal.qiniu.com/setting/key) to create and get your own Accesskey & SecretKey
 1. Update `Upyun/config.php` base on the info you get before.

## Notification

If you enable this plugin, it may take long time to generate your blog, please be patient.

## 简介
这是一个PMBlog程序的插件。PMBlog是一个PHP语言编写的静态博客生成器。使用这个插件之前你必须[下载](http://upyun.gitcafe.com/lizheming/PMBlog) 安装这个程序。启用这个插件之后，你能将程序生成的博客上传到你的七牛云空间去。

## 条件
 - PHP>=5.2
 - cURL

## 如何使用

 1. 在七牛上创建一个文件类空间
 1. 在[密钥设置页面](https://portal.qiniu.com/setting/key)中创建获取自己的AccessKey和SecretKey
 1. 根据以上获得的信息修改`Qiniu/config.php`插件内的空间账号信息。

## 提醒

启用插件后由于要上传文件到空间中所以生成博客的时间可能会变长，请耐心等待。

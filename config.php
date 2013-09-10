<?php
$site = array(
	'title' => 'PMBlog',	//Blog title
	'url' => 'https://github.com/lizheming/PMBlog/',	//Blog URL
	'config' => array(
		'template' => 'default',	//Blog theme
		'dateformat' => 'Y-m-d H:i:s',	//Blog time format, if you don't know what it is, don't change it!
		'doc' => 'md',	//define which folder to be your post's storage
		'html' => 'html',	//define which folder to be the last blog's storage
		'posts_per_page' =>	'3'	//define how posts display in one page
		//'scripts_per_page' => ''	//you can put the third comment system code in here
	)
);

//导航栏
$menu = array(
	array('title' => 'Archive', 'description'=> '', 'url' => $site['url'].'/page/archive.html'),
	array('title' => 'GuestBook', 'description'=> '', 'url' => $site['url'].'/page/guestbook.html')
);
//友情链接
$link = array(
	array('title' => 'PMBlog','description' =>'PMBlog官方博客','url'=>'http://github.com/lizheming/pmblog'),
	array('title' => '怡红院落', 'description' =>'怡红公子','url'=>'http://blog.imnerd.org')
);


//社会化评论代码{请放在两个EOD之间}
$comment = <<<EOD

EOD;

?>
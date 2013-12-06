<?php
$site = array(
        'title' => 'PMBlog',        //博客标题
        'url' => 'http://lizheming.github.io/PMBlog',        //博客地址
        'config' => array(
                'template' => 'default',        //博客主题
                'dateformat' => 'Y-m-d H:i:s',        //默认时间格式，如果不知道怎么改就不用动了！
                'doc' => 'md',        //日志存放文件夹
                'html' => 'html',        //静态文件输出文件夹
                'posts_per_page' =>        '3',//每页显示多少篇文章
                'tag' => true,        //是否输出标签导航页
                'category' => true, //是否输出分类导航页
                'router' => array(
					'post' => '/{year}/{month}/{day}',
					'page' => '/'
                )
                //'scripts_per_page' => ''        //you can put the third comment system code in here
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
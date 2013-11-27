<?php

$config['site_title'] = 'PMBlog';						//Site title
$config['base_url'] = 'http://localhost/PMBlog';	//Base URL
$config['theme'] = 'default';							//Set the theme

define('CONTENTS_DIR', 'usr/contents');					//Set name for Markdown document
define('SITE_DIR', '../');								//Set name for Site document
define('THEMES_DIR', 'usr/themes');						//Set name for Theme document
define('PLUGINS_DIR', 'usr/plugins');					//Set name for Plugin document

/*If you don't kown what them is, you may do nothing change below*/
define('ROOT_DIR', realpath(dirname(__FILE__)).'/');	//Set ROOT Dir, please don't modify!
$config['date_format'] = 'Y-m-d H:i:s';					//Set the PHP date format
$config['posts_per_page'] = 5;							//Set the contents' number display in per index page
$config['post_router'] = '';							//Set URL style for post, it supports four parameters: {year}, {month}, {day}, {category}
$config['page_router'] = '';							//Set URL style for page, it supports three parameters: {year}, {month}, {day} 

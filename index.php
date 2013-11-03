<?php

date_default_timezone_set('Asia/Shanghai');
$pagestartime=microtime(); //mark a begin runtime to calculate this page's runtime
set_time_limit(0);
//check update!
if(extension_loaded('cURL'))
{
	$version = 3.3;
	$curl = curl_init('https://rawgithub.com/lizheming/PMBlog/master/version.json');
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$check = curl_exec($curl);
	curl_close($curl);
	$check = json_decode($check, true);
	if($check[0]['version'] > $version)
	{
		die('PMBlog好像发布新版本咯，<a href="http://github.com/lizheming/PMBlog" title="PMBlog">点击这里</a>去下载新版本再来更新博客吧！<p>更新信息： </p>'.$check[0]['description']);
	}	
}


include 'config.php';
include 'lib/parse.php';


$dir = array_map(
		create_function(
			'$item', 
			'return "./$item/";'
		), array(
			'md' => $site['config']['doc'], 
			'tmp' => 'tmp/'.$site['config']['template'], 
			'html' => $site['config']['html']
		)
	);
	
/*
//delete all output file 
@unlink($dir['html'].'rss.xml');
@unlink($dir['html'].'index.html');
foreach(glob($dir['html'].'*.html') as $html_doc) {
        if(preg_match('/\/\d{1,}\.html/i', $html_doc)) @unlink($html_doc);
}

*/
DDir($dir['html'].'/tag');
DDir($dir['html'].'/category');

$data = $tags = $categories = array();

foreach(daily($dir['md'].'*') as $item) {
	$post = new parse($item);
	$status = $post->status();
	$date = $post->date();
	//if post's status is draft and post's date is lower than time now, then skip
	if(!$status or $date >= time()) continue;
	$post->image();
	//get post's other infomations
	$log['type'] = $post->type();
	$log['filename'] = $post->doc_title();
	$router = str_replace(
		array('{year}','{month}','{day}'),
		array(date('Y', $date), date('m', $date), date('d', $date)),
		$site['config']['router'][$log['type']]
	);
	$log = array_merge($log, array(
		'title'=> $post->title(),
		'date'=> date($site['config']['dateformat'], $date),
		'content' => $post->text(),
		'template' => $post->tmp(),
		'tags' => $post->tags(),
		'custom' => $post->custom()
	));

	//pages haven't abstract and tag category's access
	if($log['type'] == 'post') {
		$abstract = explode('<!--more-->', $log['content']);
		
		$log['read_more'] = false;
		if(isset($abstract[1])) $log['read_more'] = true;

		$log['opening'] = $abstract[0];
		
		$dirname = dirname($item).'/';
		$dirname = str_replace($dir['md'], '', $dirname);
		$dirname = explode('/', $dirname);
		$dirname = array_filter($dirname);
		$log['categories'] = $post->categories();
		if($log['categories'][0] != '未分类') {
			$log['categories'] = array_merge($log['categories'], $dirname);
		} else {
			if(count($dirname) > 0)	$log['categories'] = $dirname;
		}
		/*post URL replace {category}
		  the url setting maynot have {category}, then post.filepath will return just only one item
		  if it has, then loop to replace {category} and push to array post.filepath
		  the first category will be the part of the post.url
		*/
		if(preg_match('/{category}/', $router)) {
			$log['filepath'] = array();
			foreach($log['categories'] as $item) {
				$log['filepath'][] = str_replace('{category}', $item, $router).'/'.$log['filename'].'.html';
			}
		} else {
			$log['filepath'] = array($router.'/'.$log['filename'].'.html');
		}
		$log['url'] = $site['url'].$log['filepath'][0];
		
		foreach($log['tags'] as $item) $tags[$item][] = $log;
		foreach($log['categories'] as $item) {
			$categories[$item][] = $log;
		}
	} else {
		$log['filepath'] = $router.'/'.$log['filename'].'.html';
		$log['url'] = $site['url'].$log['filepath'];
		unset($log['read_more']);
		unset($log['opening']);
		unset($log['categories']);
		unset($log['tags']);
	}

	$data[$log['type']][] = $log;

}

//sort all by time from the latest to the oldest!
$srt = create_function('$a,$b', 'if ($a[\'date\'] == $b[\'date\']) return 0;return ($a[\'date\'] < $b[\'date\']) ? 1 : -1;');
usort($data['post'], $srt);
foreach(array_keys($tags) as $i) usort($tags[$i], $srt);
foreach(array_keys($categories) as $i) usort($categories[$i], $srt);

/*
 *
 *
 $data = array(
	'post' => array(
		array(
			'title'=> '',
			'date'=> '',
			'url'=> '',
			'type'=> '',
			'filename'=> '',
			'filepath'=> '',
			'opening'=> '',
			'read_more' => '',
			'content'=> '',
			'template'=> '',
			'tags'=> array(),
			'categories'=> array(),
			'custom' => array()
		),
		array()....
	),
	'page' => array(
		array(
			'title'=> '',
			'date'=> '',
			'url'=> '',
			'type'=> '',
			'filename'=> '',
			'filepath'=> '',
			'content'=> '',
			'template'=> '',
			'custom' => array()
		),
		array()...
	)
 )
 *
 *
 *
 */
 
 //Get Archive
function PCOUNT($array) {
	$count = 0;
	foreach($array as $item) {
		$count += count($item);
	}
	return $count;
}
foreach($data['post'] as $key => $post) {
	$date = strtotime($post['date']);
	$title = $post['title'];
	$url = $post['url'];
	$arch[date('Y',$date)][date('n', $date)][date('j', $date)][] = array('title' => $title, 'url' => $url);	
}
$Archive = '<div class="car-container car-collapse"><a href="#" class="car-toggler">展开全部</a><ul class="car-list">';
foreach($arch as $kyear => $year) {
	foreach($year as $kmonth => $month) {
		$Archive .= '<li><span class="car-yearmonth">'.$kmonth.'月 '.$kyear.' <span title="Post Count">('.PCOUNT($month).')</span></span>';
		$Archive .= '<ul class="car-monthlisting">';
		foreach($month as $kday => $day) {
			foreach($day as $item) {
				$Archive .= '<li>'.$kday.': <a href="'.$item['url'].'">'.$item['title'].'</a></li>';
			}
		}
		$Archive .= '</ul>';
		$Archive .= '</li>';
	}
}
$Archive .= "</ul></div>
<style type=\"text/css\">.car-collapse .car-yearmonth { cursor: s-resize; }.car-monthlisting {overflow:hidden;} </style>
<script type=\"text/javascript\">
window.onload = function() {
	var collapse = document.getElementsByClassName('car-collapse')[0];
	var monthlisting = collapse.getElementsByClassName('car-monthlisting');
	var yearmonth = collapse.getElementsByClassName('car-yearmonth');
	for(i=1,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'none';
	monthlisting[0].style.display = 'block';
	for(i=0,l=yearmonth.length;i<l;i++) {
		yearmonth[i].onclick = function() {
			var obj = this.nextSibling, display = obj.style.display;
			if(display == 'block') {
				obj.style.height = obj.scrollHeight;
				var hide = setInterval(function() {
					obj.style.height = (parseInt(obj.style.height) - 10)+'px';
					if(parseInt(obj.style.height) <= 10) {
						obj.style.height = 'auto';
						obj.style.display = 'none';
						clearInterval(hide);
					}
				}, 10);
			} else {
				obj.style.height = '0';
				obj.style.display = 'block';
				var items = obj.getElementsByTagName('li'), Height = items.length*items[0].scrollHeight;
				var show = setInterval(function(){
					obj.style.height = (parseInt(obj.style.height) + 10)+'px';
					if(parseInt(obj.style.height)>Height) {
						clearInterval(show);
					}
				}, 10);
			}
		}
	}
	
	document.getElementsByClassName('car-toggler')[0].onclick = function() {
		if(this.innerText == '展开全部') {
			this.innerText = '折叠全部';
			for(i=0,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'block';
		} else {
			this.innerText = '展开全部';
			for(i=0,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'none';
		}
	}	
}
</script>";

//count the post's infomation, such as the post count and the rencent post
//recent post
$RecentPost = array();
for($i=0;$i<10;$i++) {
	if(isset($data['post'][$i])) $RecentPost[$i] = $data['post'][$i];
	else break;
	unset($RecentPost[$i]['abstract']);
	unset($RecentPost[$i]['content']);
}
//TagCloud 
if(count($tags)) 
{
$TagCloud = array();
foreach($tags as $key => $tag) {
	$TagCloud[] = array(
		'title' => $key,
		'url' => $site['url'].'/tag/'.$key,
		'length' => count($tag)
	);
}
}
//CategoryCloud
if(count($categories)) 
{
$CategoryCloud = array();
foreach($categories as $key =>$category) {
	$CategoryCloud[] = array(
		'title' => $key,
		'url' => $site['url'].'/category/'.$key,
		'length' => count($category)
	);
}
}

$sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";


Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem($dir['tmp']);
$twig = new Twig_Environment($loader, array('autoescape'=>false));

//The Page HTML Output
if(count($data['page'])) 
{
foreach($data['page'] as $key => $post):
	$file = $post['template'];
	if(!file_exists($dir['tmp'].$file))	$file = 'page.html';
	$template = $twig->loadTemplate($file);
	$html = $template->render(compact('site', 'menu', 'link', 'comment', 'RecentPost', 'Archive', 'TagCloud', 'CategoryCloud', 'post'));
	mkfolder(dirname($dir['html'].$post['filepath']));
	file_put_contents($dir['html'].$post['filepath'], $html);
	$sitemap .= "
	 <url>
	  <loc>".$site['url'].$post['filepath']."</loc>
	  <priority>1.0</priority>
	  <changefreq>monthly</changefreq>
	  <lastmod>".date('c', strtotime($post['date']))."</lastmod>
	 </url>";
endforeach;
}

//The Post HTML Output
if(count($data['post'])) 
{
foreach($data['post'] as $key => $post):
	//preg from template
	if(isset($data['post'][$key+1])) {
		$NextPost = $data['post'][$key+1];
		$post['next'] = array('title' => $NextPost['title'], 'url'=> $NextPost['url']);
	} else {
		$post['next'] = '';
	}
	if(isset($data['post'][$key-1])) {
		$PrevPost = $data['post'][$key-1];
		$post['prev'] = array('title' => $PrevPost['title'], 'url'=> $PrevPost['url']);
	} else {
		$post['prev'] = '';
	}
	$file = $post['template'];
	if(!file_exists($dir['tmp'].$file))	$file = 'post.html';
	$template = $twig->loadTemplate($file);
	$html = $template->render(compact('site', 'menu', 'link', 'comment', 'RecentPost', 'Archive', 'TagCloud', 'CategoryCloud', 'post'));
	foreach($post['filepath'] as $filepath) {
		if(DIRECTORY_SEPARATOR == '\\')	$filepath = iconv('utf-8', 'gbk', $filepath);
		mkfolder($dir['html'].dirname($filepath));
		file_put_contents($dir['html'].$filepath, $html);
	}
	$sitemap .= "
	 <url>
	  <loc>".$site['url'].$post['filepath'][0]."</loc>
	  <priority>1.0</priority>
	  <changefreq>monthly</changefreq>
	  <lastmod>".date('c', strtotime($post['date']))."</lastmod>
	 </url>";
endforeach;
}

//index
foreach(paginator($data['post'],'','') as $paginator) {
	$posts = $paginator['object_list'];
	$template = $twig->loadTemplate('index.html');
	$html = $template->render(compact('site','menu','link','comment','RecentPost','Archive','TagCloud','CategoryCloud','posts','paginator'));
	$folder = $dir['html'].'page/'.$paginator['page'];
	mkfolder($folder);
	file_put_contents($folder.'/index.html', $html);
	$sitemap .= "<url>
		<loc>".$paginator['page_url'].".html</loc>
		<priority>0.8</priority>
		<changefreq>daily</changefreq>
		<lastmod>".date('c')."</lastmod>
	</url>";
	echo $paginator['page_url'].'<br>';
}
copy($dir['html'].'page/1/index.html', $dir['html'].'index.html');
$sitemap .= "<url><loc>".$site['url']."/index.html</loc><priority>0.8</priority><changefreq>daily</changefreq><lastmod>".date('c')."</lastmod></url>";

//output category
if($site['config']['category']) 
{
foreach($categories as $key => $category) :
	if(DIRECTORY_SEPARATOR == '\\')	$key = iconv('utf-8', 'gbk', $key);
	$folder = $dir['html'].'category/'.$key;
	mkfolder($folder);
	foreach(paginator($category, 'category', $key) as $paginator) :
		$posts = $paginator['object_list'];
		$template = $twig->loadTemplate('index.html');
		$html = $template->render(compact('site','menu','link','comment','RecentPost','Archive','TagCloud','CategoryCloud','posts','paginator'));
		mkfolder($folder.'/page/'.$paginator['page']);
		file_put_contents($folder.'/page/'.$paginator['page'].'/index.html', $html);
		$sitemap .= "<url>
			<loc>".$paginator['page_url']."</loc>
			<priority>0.8</priority>
			<changefreq>daily</changefreq>
			<lastmod>".date('c')."</lastmod>
		</url>";
		echo $paginator['page_url'].'<br>';
	endforeach;
	copy("$folder/page/1/index.html", "$folder/index.html");
	$sitemap .= "<url><loc>".$site['url']."/category/".$key."/index.html</loc><priority>0.8</priority><changefreq>daily</changefreq><lastmod>".date('c')."</lastmod></url>";
endforeach;
}

//output tags
if($site['config']['tag']) 
{
foreach($tags as $key => $tag):
	if(DIRECTORY_SEPARATOR == '\\')	$key = iconv('utf-8', 'gbk', $key);
	$folder = $dir['html'].'tag/'.$key;
	mkfolder($folder);
	foreach(paginator($tag, 'tag', $key) as $paginator):
		$posts = $paginator['object_list'];
		$template = $twig->loadTemplate('index.html');
		$html = $template->render(compact('site','menu','link','comment','RecentPost','Archive','TagCloud','CategoryCloud','posts','paginator'));
		mkfolder($folder.'/page/'.$paginator['page']);
		file_put_contents($folder.'/page/'.$paginator['page'].'/index.html', $html);
		$sitemap .= "<url>
			<loc>".$paginator['page_url']."</loc>
			<priority>0.8</priority>
			<changefreq>daily</changefreq>
			<lastmod>".date('c')."</lastmod>
		</url>";
		echo $paginator['page_url'].'<br>';
	endforeach;
	copy("$folder/page/1/index.html", "$folder/index.html");
	$sitemap .= "<url><loc>".$site['url']."/tag/".$key."/index.html</loc><priority>0.8</priority><changefreq>daily</changefreq><lastmod>".date('c')."</lastmod></url>";
endforeach;
}

/*sitemap*/
$sitemap .= '</urlset>';
if(DIRECTORY_SEPARATOR == '\\') $sitemap = @iconv('gbk', 'utf-8', $sitemap);
file_put_contents($dir['html'].'sitemap.xml', $sitemap);


//RSS and Atom
$loader = new Twig_Loader_Filesystem('./lib/');
$twig = new Twig_Environment($loader, array('autoescape'=>false));
$atom = $twig->loadTemplate('atom.xml');
$posts = array_slice($data['post'], 0, 20);
file_put_contents($dir['html'].'atom.xml', $atom->render(compact('posts', 'site')));
$rss = $twig->loadTemplate('rss.xml');
file_put_contents($dir['html'].'rss.xml', $rss->render(compact('posts', 'site')));


$pageendtime = microtime(); 
$starttime = explode(" ",$pagestartime); 
$endtime = explode(" ",$pageendtime); 
$totaltime = $endtime[0]-$starttime[0]+$endtime[1]-$starttime[1]; 
$timecost = sprintf("%s",$totaltime); 
?>
页面运行时间:<?php echo $timecost; ?> 秒，<span id="time">3</span>秒后跳转到首页！
<script type="text/javascript">window.onload = function() {window.setInterval("var _t = document.getElementById('time');_t.innerHTML = _t.innerHTML-1;", 1000);window.setInterval("window.location.href = '<?php echo $dir['html']; ?>'", 3000);}</script>
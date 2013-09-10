<?php
include_once 'markdown.php';
include_once 'Twig/Autoloader.php';
//include_once 'output.php';
function CheckFolder($path)	{
	if(!is_writable($path)) mkdir($path, 0777);
}

class parse {
	var $doc;
	var $text;
	function __construct($doc) {
		$this->doc = $doc;	
		$f = file_get_contents($doc);
		if($f) {
			$this->text = $f;
		} else {
			$this->text = '';
		}
	}
	function title() {
		$preg = '/^title\:(.*)/im';
		preg_match_all($preg, $this->text, $match);
		if(isset($match[1][0])) {
			return trim($match[1][0]);
		} else {
			$preg = '/^#(.*?)$/im';
			$n = preg_match_all($preg, $this->text, $match);
			if($n == 1) {
				$title = trim($match[1][0]);
				if(substr($title,-1) == '#') $title = substr($title, 0, -1);
				$this->text = str_replace($match[0][0], '', $this->text);
				return trim($title);
			}
			return date('Ymd', $this->date());
		}
	}
	function doc_title() {
		return pathinfo($this->doc, PATHINFO_FILENAME);
	}
	function date() {
		$preg = '/^date\:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);
		if($n!=0) {
			return strtotime($match[1][0]);
		} else {
			return $this->doc_date();
		}
	}
	function doc_date() {
		$date = filectime($this->doc);
		file_put_contents($this->doc, 'date: '.date('Y-m-d H:i:s', $date).PHP_EOL.PHP_EOL.$this->text);
		return $date; //返回文件创建的时间
	}
	function status() {
		$preg = '/^status\:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);
		if($n!=0 && @trim($match[1][0]) == 'draft') {
			return false;
		} else {
			return true;
		}
	}
	function tags() {
		$preg = '/^tags:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);
		$tags = array();
		if($n!=0)	{
			$tags = explode(',',$match[1][0]);
			foreach($tags as $k => $tag) $tags[$k] = trim($tag);
		}
		return $tags;
	}
	function categories() {
		$preg = '/^category:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);
		$categories = array();
		if($n != 0) {
			if(trim($match[1][0]) != '') {
				$categories = explode(',', $match[1][0]);
				foreach($categories as $k => $category) $categories[$k] = trim($category);
			} else {
				$categories[] = '未分类';
			}
		} else {
			$categories[] = '未分类';
		}
		return $categories;
	}
	function url() { //重做定义，url地址即为md文件名
		return $this->doc_title().'.html';
	}
	function text() {
		$preg = '/^(title|status|tags|date|type|template|category):.*/im';
		return Markdown(trim(preg_replace($preg, '', $this->text)));
	}
	function type() {
		$preg = '/^type:.*page/im';
		if(preg_match($preg, $this->text)) {
			return 'page';
		} else {
			return 'post';
		}
	}
	function tmp() {
		$preg = '/^template:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);
		if($n!=0):
			return trim($match[1][0]).'.html';
		else:
			if($this->type() != 'page'):
				return 'blog.html';
			else:
				return 'page.html';
			endif;
		endif;
	}
	function image() {
		$preg = "/<img src=\"(.+?)\".*?>/";
		$n = preg_match_all($preg, $this->text(), $match);
	}
}
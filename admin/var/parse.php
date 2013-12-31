<?php
include_once 'markdown.php';
include_once 'function.php';

class parse {
	var $doc;
	var $text;
	function __construct($doc) {
		$this->doc = $doc;	
		
		$f = file_get_contents($doc);
		if(json_encode($f) == 'null') 
			$f = mb_convert_encoding($f, 'UTF-8', array('GBK', 'BIG-5'));
		
		$this->text = $f ? $f : '';
	}
	function title() {
		$preg = '/^title\:(.*)/im';
		preg_match_all($preg, $this->text, $match);
		if(isset($match[1][0])) {
			return trim($match[1][0]);
		} else {
			$preg = '/^#([^#].*?)$/im';
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
	function doc() {
		return $this->doc;
	}
	function doc_title() {
		return pathinfo($this->doc, PATHINFO_FILENAME);
	}
	function doc_extension() {
		return pathinfo($this->doc, PATHINFO_EXTENSION);
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

	/**
	 * 文章状态
	 *
	 * @return string 0:发布 1:草稿 2:预约
	 */
	function status() {
		$preg = '/^status\:(.*)/im';
		$n = preg_match_all($preg, $this->text, $match);

		if($n!=0 && @trim($match[1][0]) == 'draft') return 1;
		
		if($this->date() < time())	{
			return 0;
		} else {
			return 2;
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
				$categories[] = 'unclassified';
			}
		} else {
			$categories[] = 'unclassified';
		}
		return $categories;
	}
	function url() { //重做定义，url地址即为md文件名
		return $this->doc_title().'.html';
	}
	function text() {
		$preg = '/^(title|status|tags|date|type|template|category|custom-.*):.*/im';
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
				return 'post.html';
			else:
				return 'page.html';
			endif;
		endif;
	}
	function image() {
		$preg = "/<img src=\"(.+?)\".*?>/";
		$n = preg_match_all($preg, $this->text(), $match);
		if($n!=0) {
			return $match[1][0];
		} else {
			return 'None';
		}
	}
	function custom() {
		$preg = '/^(custom-.*?):(.*?)$/im';
		$n = preg_match_all($preg, $this->text, $match);
		if($n!=0) {
			$custom = array();
			for($i=0;$i<$n;$i++) {
				$name = substr($match[1][$i], 7);
				$custom[$name] = $match[2][$i];
			}
			return $custom;
		}
		return false;
	}
}
<?php
/**
 * A online editor plugin for PMBlog
 *
 * @author 公子
 * @version 0.1.1
 * @link http://github.com/lizheming
 */
class Editor {
	private $config, $hidden_posts;

	function __construct() {
		$hidden_posts = array('post' => array(), 'page'=>array());
		$this->hidden_posts = $hidden_posts;
	}

	function config_loaded($config) {
		$this->config = $config;
		if(isset($_GET['edit'])) {
			/* show editor */
			$docpath = urldecode($_GET['edit']);
			$file = ROOT_DIR.CONTENTS_DIR.'/'.$docpath;
			$content = 'title:新文章';
			$newfile = true;
			if($docpath && file_exists($file)){
				$content = file_get_contents($file);
				if(json_encode($content) == 'null'){
					$content = mb_convert_encoding($content, 'utf-8', 'gbk');
				}
				$newfile = false;
			}
			if(!$docpath){
				$docpath = '?set a new file name?.md';
			}
			include_once PLUGINS_DIR."/Editor/Editor.html";
			die();
		}

		if(isset($_GET['rm'])) {
			$file = urldecode($_GET['rm']);
			
			if(!unlink($file)) $this->die_info('删除出错，请重新删除或手动删除');
		}

		if(isset($_POST['docpath']) && isset($_POST['editortext'])) {
			$newfile = $_POST['newfile'] == 'true';
			$name = DIRECTORY_SEPARATOR == '\\' ? iconv('utf-8', 'gbk', $_POST['docpath']) : $_POST['docpath'];
			$name = preg_replace('/[\<\>\*\?]/m','_',$name);
			$file = ROOT_DIR.CONTENTS_DIR.'/'.$name;
			$dirname = dirname($file);
			if($newfile){
				if(file_exists($file)){
					$this->die_info("<script>alert(\'已经有同名文档 “".$name."” 了，请换个名字保存！\');window.location.href=\'?edit\';</script>");
				}
				$this->mkdir($dirname);
			}
			$res = file_put_contents($file, $_POST['editortext']);
			echo "<script>localStorage.text='';</script>";
			if(!$res) $this->die_info('文章保存失败');
		}
	}

	function after_get_contents(&$data) {
		$post = array_merge($data['post'], $this->hidden_posts['post']);
		$page = array_merge($data['page'], $this->hidden_posts['page']);
		usort($post, create_function('$a,$b', 'if ($a[\'date\'] == $b[\'date\']) return 0;return ($a[\'date\'] < $b[\'date\']) ? 1 : -1;'));
		$all = compact('post', 'page');

		if(isset($_GET['show'])) {
			include_once "Show.html";
			die();
		}
	}

	function get_hidden_post_meta(&$post) {
		$hidden = array(
			'type'=>$post->type(),
			'docpath'=>$post->doc(),
			'filename'=>$post->doc_title(),
			'extension'=>$post->doc_extension(),
			'title'=>$post->title(),
			'status'=>$post->status(),
			'url'=>$post->url(),
			'date'=>date($this->config['config']['dateformat'], $post->date()));
		
		$this->hidden_posts[$hidden['type']][] = $hidden;
	}
	function die_info ( $value='')
	{
		$html = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/></head><body><div>{$value}</div></body></html>";
		die($html);
	}
	function mkdir($path)
	{
		return is_writeable($path) || mkdir($path, 0777, true);
	}
}

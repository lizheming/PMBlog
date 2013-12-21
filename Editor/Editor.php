<?php
/**
 * A online editor plugin for PMBlog
 *
 * @author 公子
 * @version 0.0.1
 * @link http://github.com/lizheming
 */
class Editor {
	function config_loaded($config) {
		if(isset($_GET['edit'])) {
			/* show editor */
			$name = trim($_GET['edit']);
			$file = CONTENTS_DIR.'/'.$name.'.md';
			$content = 'date:' . date('Y-m-d H:i:s').PHP_EOL.'title:';
			$newfile = true;
			if($name != '' && file_exists($file)){
				$content = file_get_contents($file);
				$newfile = false;
			}
			include_once PLUGINS_DIR."/Editor/Editor.html";
			die();
		}

		if(isset($_POST['name']) && isset($_POST['editortext'])) {
			$newfile = $_POST['newfile'] == 'true';
			$name = DIRECTORY_SEPARATOR == '\\' ? iconv('utf-8', 'gbk', $_POST['name']) : $_POST['name'];
			$name = preg_replace('/[\/\\\<\>\*\?]/m','_',$name);
			$file = CONTENTS_DIR.'/'.$name.'.md';
			if($newfile && file_exists($file)){
				echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"><script>alert('已经有一篇名为 “".$name."” 的文章了，还请换个名字保存！');window.location.href='?edit';</script>";
				die();
			}else{
				$res = file_put_contents($file, $_POST['editortext']);
				echo "<script>localStorage.text='';</script>";
				if(!$res) die('文章保存失败');
			}
			
		}
	}

	function after_get_contents($data) {
		if(isset($_GET['show'])) {
			include_once "Show.html";
			die();
		}
	}
}

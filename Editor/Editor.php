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
			include_once PLUGINS_DIR."/Editor/Editor.html";
			die();
		}

		if(isset($_POST['editorname']) && isset($_POST['editortext'])) {
			$name = DIRECTORY_SEPARATOR == '\\' ? iconv('utf-8', 'gbk', $_POST['editorname']) : $_POST['editorname'];
			$res = file_put_contents(CONTENTS_DIR.'/'.$name.'.md', $_POST['editortext']);
			echo "<script>localStorage.text='';</script>";
			if(!$res) die('文章保存失败');
		}
	}
}
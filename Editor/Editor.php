<?php
/**
 * A online editor plugin for PMBlog
 *
 * @author 公子
 * @version 0.0.2
 * @link http://github.com/lizheming
 */
class Editor {
	private $config, $hidden_posts;

	function __construct() {
		$this->hidden_posts = array('post' => array(), 'page'=>array());
	}

	function config_loaded($config) {
		$this->config = $config;

        if(isset($_GET['edit']))
            return $this->editor($_GET['edit']);
        else if(isset($_GET['rm']))
            return $this->remove(urldecode($_GET['rm']));
        else if(isset($_POST['slug']) && isset($_POST['content']))
            return $this->save($_POST);
	}
    function save($post) {
        $filepath = $post['file'];
        unset($post['file']);
        if($post['template'] == "") $post['template'] = $post['type'].".html";

        $text = "";
        foreach($post as $k => $v) {
            if(is_array($v)) $v = implode(', ', $v);
            if($k != 'content') $text .= "$k: $v  ".PHP_EOL;
        }
        $text .= $post['content'];

        if($filepath == "") {
            $filename = DIRECTORY_SEPARATOR == '\\' ? iconv('utf-8', 'gbk', $post['slug']) : $post['slug'];
            $filename = preg_replace('/[\<\>\*\?]/m','_', $filename);
            $filepath = ROOT_DIR.CONTENTS_DIR."/$filename.md";
            $this->mkdir(dirname($filepath));   
        } else {
            $filepath = DIRECTORY_SEPARATOR == '\\' ? iconv('utf-8', 'gbk', $filepath) : $filepath;
            $filepath = ROOT_DIR.CONTENTS_DIR."/$filepath";
        }
        $res = file_put_contents($filepath, $text);  
        if($res) echo "<script>localStorage.PMBLOG_POST='';</script>";
        else die("save faile, please try again!");
    }
    function remove($file) {
        $path = ROOT_DIR.CONTENTS_DIR."/$file";
        if(!unlink($file)) die("删除出错请重新删除");
    }
    function editor($file = "") {
        $path = ROOT_DIR.CONTENTS_DIR.'/'.urldecode($file);
        if($file != "" && file_exists($path)) {
            include ROOT_DIR."/var/parse.php";
            $content = new parse($path);
            $post = array(
                "title" => $content->title(),
                "tags" => $content->tags(),
                "slug" => str_replace(".html", "", $content->url()),
                "categories" => $this->category($file, $content->categories()),
                "content" => trim(preg_replace('/^(slug|title|tags|status|date|type|template|category):.*/im', '', $content->text)),
                "type" => $content->type(),
                "template" => $content->tmp(),
                "date" => $content->date(),
                "status" => $content->status()!=1?'public':'draft'
            );
        }
        include PLUGINS_DIR."/Editor/Editor.html";
        die();
    }
    function category($path, $categories){
        if(json_encode($path) == null) 
            $path = mb_convert_encoding($path, 'utf-8', 'gbk');
        $dirname = str_replace(ROOT_DIR.CONTENTS_DIR, '', dirname($path).'/');
        $dirname = explode('/', $dirname);
        $dirname = array_filter($dirname, create_function('$v', 'return $v && v==".";'));
        if($categories[0] != 'unclassified') {
            $categories = array_merge($categories, $dirname);
        } else if(!empty($dirname)) $categories = $dirname;
        return $categories;
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

	function get_hidden_post_meta(&$post) 
	{
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

	function DieInfo ( $value='')
	{
		$html = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/></head><body><div>{$value}</div></body></html>";
		die($html);
	}

	function mkdir($path)
	{
		return is_writeable($path) || mkdir($path, 0777, true);
	}
}

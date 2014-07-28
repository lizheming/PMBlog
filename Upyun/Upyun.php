<?php
/**
 * PMBlog plugin to upload your blog to Upyun
 * How To Use It?
 * 1. Create A File Space in Upyun
 * 2. Update Upyun/config.php with your File Space Infomation
 * 
 * If you enable this plugin, it may take long time to generate your blog.
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.0.1
 */

class Upyun {
    private $upyun, $directory;

    /**
     * Upyun init
     * 
     * @param array blog info
     * @return none
     */
    function config_loaded(&$site) {
        include $site['config']['plugins']."/Upyun/config.php"; 
        if(!$bucketname || !$username || !$password) {
            die("Upyun plugin is enabled, you should complement your spaces infomation in Upyun/config.php");
        }
        $this->directory = $site['config']['html'];
        $this->upyun = new UYun($bucketname, $username, $password);    
    }

    /**
     * upload file to upyun after rendering
     *
     * @param array posts
     * @param string file location
     * @return none
     */
    function after_file_put_contents($posts, $file) {
        if(is_dir($file)) $file .= '/index.html';
        $this->upyun->writeFile($posts['filepath'][0], file_get_contents($file), true);
    }

    /**
     * add filepath for index file 
     *
     * @param array index posts
     * @return none
     */
    function paginator_loaded(&$paginator) {
        foreach($paginator as $key => &$pagi) 
            $pagi['filepath'] = array("/page/$key/index.html");
    }

    /**
     * at last, upload some rest file
     *
     * @return none;
     */
    function end() {
        $arr = array('index.html', 'rss.xml', 'atom.xml');
        foreach($arr as $item) {
            $this->upyun->writeFile("/$item", file_get_contents($this->directory."/$item"), true);
        }
    }
}
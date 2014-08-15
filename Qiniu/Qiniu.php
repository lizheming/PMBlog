<?php
/**
 * PMBlog plugin to upload your blog to Qiniu
 * How To Use It?
 * 1. Create A File Space in Qiniu
 * 2. Update Qiniu/config.php with your File Space Infomation
 * 
 * If you enable this plugin, it may take long time to generate your blog.
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.0.2
 */

class Qiniu {
    private $directory;
    private $bucket;
    private $ak;
    private $sk;
    private $baseUrl;
    private $requests;

    public function __constructor() {
        $this->requests = array();
    }

    public function config_loaded(&$site) {
        include $site['config']['plugins']."/Qiniu/config.php";
        if(!$bucket || !$QINIU_ACCESS_KEY || !$QINIU_SECRET_KEY) {
            die("Qiniu plugin is enabled, you should complement your spaces infomation in Qiniu/config.php");
        }
        $this->directory = $site['config']['html'];
        $this->bucket = $bucket;
        $this->ak = $QINIU_ACCESS_KEY;
        $this->sk = $QINIU_SECRET_KEY;
        $this->baseUrl = $site['url'];
    }  

    public function after_file_put_contents($posts, $file) {
        if(is_dir($file)) $file .= "/index.html";
        if(isset($posts['page_url'])) {
            $filename = $this->filename($posts['page_url']);
            $this->push($filename, $file);
            $this->push($fielname."/index.html", $file);
        } else {
            $this->push($this->filename($posts['url']), $file);
            if(is_dir($file)) $this->push($this->filename($posts['url']."/index.html"), $file);
        }
    }

    public function after_output_rss($variable, $twig) {
        $arr = array('index.html', 'rss.xml', 'atom.xml');
        foreach($arr as $item) $this->push($item, $this->directory."/$item");

        if(is_dir($this->directory."/tag")) {
            foreach(PMBlog::get_files($this->directory."/tag") as $item) {
                $this->push(str_replace($this->directory, "", $item), $item);
            }
        }
        if(is_dir($this->directory."/category")) {
            foreach(PMBlog::get_files($this->directory."/category") as $item)
                $this->push(str_replace($this->directory, "", $item), $item);
        }

        $mh = curl_multi_init();
        foreach($this->requests as $request) curl_multi_add_handle($mh, $request);
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);
        foreach($this->requests as $request) curl_multi_remove_handle($mh, $request);
        curl_multi_close($mh);
    }

    public function push($fielname, $filepath) {
        $curl = curl_init('http://upload.qiniu.com/');
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
            "key" => $filename,
            "token" => $this->token($filename),
            "file" => $this->curl_file_create(realpath($filepath))
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("content-type: multipart/form-data"));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $this->requests[] = $curl;
        return $curl;
    }

    public function token($filename) {
        $encodedPutPolicy = $this->base64_encode(json_encode(array(
            "scope" => $this->bucket.":$filename",
            "deadline" => time()+3600,
            "saveKey" => $filename
        )));
        $encodeSign = $this->base64_encode(hash_hmac("sha1", $encodedPutPolicy, $this->sk, true));
        return $this->ak.":$encodeSign:$encodedPutPolicy";
    }

    public function curl_file_create($filename, $mimetype = '', $postname = '') {
        if (!function_exists('curl_file_create')) {
            return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
        } else return curl_file_create($filename, $mimetype, $postname);
    }

    public function base64_encode($text) {
        return str_replace(
            array('+', '/'),
            array('-', '_'),
            base64_encode($text)
        );
    }

    public function parse_url($url) {
        $url=str_replace('\\','/',$url);
        $last='';
        while($url!=$last){
            $last=$url;
            $path=preg_replace('/\/[^\/]+\/\.\.\//','/',$url);
        } 
        $last='';
        while($url!=$last){
            $last=$url;
            $url=preg_replace('/([\.\/]\/)+/','/',$url);
        }
        return $url;
    }

    public function filename($url) {
        return str_replace(
            $this->parse_url($this->baseUrl),
            "",
            $this->parse_url($url)
        );
    }
}
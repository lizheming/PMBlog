<?php 
class Update {
    function start() {
        if(!extension_loaded('cURL')) die();
        $url = "https://raw.githubusercontent.com/wiki/lizheming/PMBlog/Changelog.md";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $check = curl_exec($curl);
        curl_close($curl);
        preg_match_all('/^(.*?)$/m', $check, $m);
        $temp = explode(' ', $m[1][0]);
        if((float)$temp[1] > PMBlog::$version) 
            die('PMBlog has new version! <a href="http://github.com/lizheming/PMBlog" title="PMBlog">Click Here</a>to get it!');
    }
}
?>
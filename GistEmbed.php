<?php
/**
 * PMBlog's plugin to show gist just use gist url in your post
 *
 * @author lizheming
 * @link http://zh.eming.li
 * @version 1.0.0
 */

class GistEmbed {
	function after_get_post_meta(&$post) 
	{
		$preg = "/^.*?gist\.github\.com\/.*$/m";
		preg_match_all($preg, $post['content'], $match);

		foreach($match[0] as $v) {
			$v = strip_tags($v);
			$html = "<script src=\"$v.js\"></script>";
			$post['content'] = str_replace($v, $html , $post['content']);
		}

		$abstract = explode('<!--more-->', $post['content']);
		$post['opening'] = $abstract[0];
	}	
}
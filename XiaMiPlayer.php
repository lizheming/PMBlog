<?php
/**
 *
 * PMBlog's plugin to embed xiami.com music by song url in your post!
 *
 * @author lizheming
 * @link http://zh.eming.li
 * @version 1.0.0
 */

class XiaMiPlayer {
	function after_get_post_meta(&$post) 
	{
		$preg = "/^.*?xiami\.com\/song\/(\d+).*$/m";
		preg_match_all($preg, $post['content'], $match);

		foreach($match[1] as $k => $v) {
			$html = '<iframe src="http://imnerd.org/lab/search/show.html#'.$v.'" style="width:255px;height:35px;" frameborder="0" scrolling="no"></iframe>';
			$post['content'] = str_replace($match[0][$k], $html, $post['content']);
		}

		$abstract = explode('<!--more-->', $post['content']);
		$post['opening'] = $abstract[0];
	}
}
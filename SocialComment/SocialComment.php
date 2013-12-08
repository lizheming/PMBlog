<?php
/**
 * PMBlog plugin to add comment functionaly for PMBlog
 * How To Use It?
 * 1. Add your SocialCommentId in the SocialComment/config.php
 * 2. Add {{comment}} to the appropriate position in the template.
 * 
 * If the SocialCommentSystem you used is not include in this plugin, pleas email me to update it!
 *
 * @author lizheming
 * @mail i@imnerd.org
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
 
class SocialComment {
	private $id, $type;

	function config_loaded(&$site) {
		include $site['config']['plugins']."/SocialComment/config.php";
		if($duoshuo_shortname != '') {
			$this->id = $duoshuo_shortname;
			$this->type = 'duoshuo';
		} elseif ( $disqus_shortname != '') {
			$this->id = $disqus_shortname;
			$this->type = 'disqus';
		} else {
			die('It seems you have lanuched SocialComment Plugin but not set your SocialComment Id, please tell us your SocialComment Id in SocialComment/config.php before you run the program.');
		}
	}

	function after_get_variables(&$variables) {
		switch($this->type) {
			case 'duoshuo':
				$comment = "<div class=\"ds-thread\"></div>
<script type=\"text/javascript\">var duoshuoQuery = {short_name:\"{$this->id}\"};(function() {var ds = document.createElement('script');ds.type = 'text/javascript';ds.async = true;ds.src = 'http://static.duoshuo.com/embed.js';ds.charset = 'UTF-8';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ds);})();</script>";
			break;

			case 'disqus':
				$comment = "<div id=\"disqus_thread\"></div>
    <script type=\"text/javascript\">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = '{$this->id}'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href=\"http://disqus.com/?ref_noscript\">comments powered by Disqus.</a></noscript>
    <a href=\"http://disqus.com\" class=\"dsq-brlink\">comments powered by <span class=\"logo-disqus\">Disqus</span></a>";
			break;

			default:
				$comment="";
			break;
		}

		$variables['comment'] = $comment;
	}

}
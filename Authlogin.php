<?php
/**
 * PMBlog plugin to add login for your panel
 * 
 * 
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */

class Authlogin {
	/**
	 * Change default username and password by yourself.
	 */
	private $username = "admin";
	private $password = "lizheming.com";

	public function plugins_loaded() {
		/* Check user's name and password is default */
		if($this->username == 'admin' && $this->password == 'admin') 
			die('You have default username and password, modify them by yourself before run PMBlog.');
		
		$AUTHUSER = ( @$_SERVER['PHP_AUTH_USER'] == $this->username );
		$AUTHPW = ( @$_SERVER['PHP_AUTH_PW'] == $this->password );
		if(!$AUTHUSER || !$AUTHPW) {
		    header('WWW-Authenticate: Basic realm="PMBlog Panel"');
		    header('HTTP/1.1 401 Unauthorized');
		    die();
		}
	}
}
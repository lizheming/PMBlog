<?php

/*
 *PMBlog plugin to generate an XML sitemap.
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
 
class Sitemap {
	private $sitemap;

	public function __construct() {
			$this->sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
	}

	public function after_file_put_contents(&$post) {
		if(isset($post['url'])) {
			$this->sitemap .= "<url>
		  <loc>{$post['url']}</loc>
		  <priority>1.0</priority>
		  <changefreq>monthly</changefreq>
		  <lastmod>".date('c', strtotime($post['date']))."</lastmod>
			</url>";
		} else {
			$this->sitemap .= "<url>
		  <loc>{$post['page_url']}</loc>
		  <priority>1.0</priority>
		  <changefreq>monthly</changefreq>
		  <lastmod>".date('c')."</lastmod>
			</url>";
		}
	}

	public function end() {
		$this->sitemap .= "<url>
		  <loc>rss.xml</loc>
		  <priority>1.0</priority>
		  <changefreq>monthly</changefreq>
		  <lastmod>".date('c')."</lastmod>
			</url>";
		$this->sitemap .= "<url>
		  <loc>atom.xml</loc>
		  <priority>1.0</priority>
		  <changefreq>monthly</changefreq>
		  <lastmod>".date('c')."</lastmod>
			</url>";

		$this->sitemap .= '</urlset>';


		file_put_contents(SITE_DIR.'/sitemap.xml', $this->sitemap);
	}
}
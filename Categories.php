<?php
/**
 * PMBlog's plugin to Provides categories functionality for it.
 * Add site.categories.category and CategoryCloud for the template.
 * site.categories.CATEGORY : It stores all post include CATEOGRY, its structor is same as posts.
 * CategoryCloud : It stores all categorynames. How to use it?
 * Example: 
 *     {% for category in CategoryCloud %}
 *        {{category.title}}        {# Show categoryname #}
 *        {{category.url}}            {# show categoryURL #}
 *        {{category.length}}            {# show categoryPostLength #}
 *    {% endfor %}
 *
 *    And this plugin add category index page for your blog. All index pages save in the directory named 'category'
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.3
 */

class Categories {
    private $categories = array();

    function after_get_post_meta(&$post) {
        if($post['type'] == 'post' && isset($post['categories'])) {
            foreach($post['categories'] as $category) {
                $category = strtolower($category);
                $this->categories[$category][] = $post;
            }
        }
    }

    function after_get_variables(&$variables) {
        $categoryclouds = array();
        foreach($this->categories as $category => $posts) {
            usort($posts, array($this, 'category_sort'));
            $categorycloud = array('title'=> $category, 'url'=> $variables['site']['url'].'/category/'.parse::stringToPinyin($category, '-'), 'length'=> count($posts));
            $categoryclouds[] = $categorycloud;
            $this->categories[$category] = $posts;
        }
        $variables['site']['categories'] = $this->categories;
        $variables['categoryCloud'] = $categoryclouds;
    }

    function after_output_index($variables, $twig) {    
        foreach($this->categories as $category => $posts) {
            $variables['site']['category'] = $category;
            $category = parse::stringToPinyin($category, '-');
            foreach($this->paginator($category, $posts, $variables['site']) as $paginator) {
                $template = 'index.html';
                $vars = $variables;
                $vars['posts'] = $paginator['object_list'];
                $vars['paginator'] = $paginator;
                $html = $twig->render($template, $vars);
                Categories::file_put_contents("{$variables['site']['config']['html']}category/$category/page/{$paginator['page']}/index.html", $html);
            }
            copy("{$variables['site']['config']['html']}category/$category/page/1/index.html", "{$variables['site']['config']['html']}category/$category/index.html");
        }
    }

    function paginator($key, $post, $site) {
        $perpage = $site['config']['posts_per_page'];
        $total_pages = ceil(count($post)/$perpage);
        $paginator = array();
        for($i=0;$i<$total_pages;$i++) 
        {
            $has_next = $has_previous = true;
            if(($i+2) > $total_pages) $has_next = false;
            if($i<1) $has_previous = false; 
            $pagpost = array_slice($post, $i*$perpage, $perpage);
            $prefix_url = "{$site['url']}/category/$key";
            $paginator[$i+1] =  array(
                'per_page' => $perpage,
                'object_list'=>$pagpost, 
                'total_pages'=> $total_pages, 
                'page' => $i+1, 
                'prefix_url' => $prefix_url,
                'page_url' => $prefix_url.'/page/'.($i+1),
                'previous_page' => $i,
                'pre_page' => $i, 
                'previous_page_url' => $prefix_url.'/page/'.$i,
                'pre_page_url' => $prefix_url.'/page/'.$i, 
                'next_page' => $i+2, 
                'next_page_url' => $prefix_url.'/page/'.($i+2), 
                'has_next' => $has_next, 
                'has_previous' => $has_previous,
                'has_pre' => $has_previous
            );
        }
        return $paginator;
    }
    
    public function file_put_contents($file, $data)
    {
        $path = dirname($file);
        is_writeable($path) || mkdir($path, 0777, true);
        return file_put_contents($file, $data);
    }

    public function category_sort($a, $b) 
    {
        if ($a['date'] == $b['date']) return 0;
        return ($a['date'] < $b['date']) ? 1 : -1;
    }
}
?>
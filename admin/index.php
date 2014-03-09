<?php
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

/**
 * @author lizheming
 * @link http://github.com/lizheming/PMBlog
 * @license http://opensource.org/licenses/MIT
 * @version 4.0
 */
class PMBlog {
    private $plugins, $site;
    public $version = 4.0;

    public function __construct() 
    {    
        //Load Plugins
        $this->load_plugins();
        $this->run_hooks('plugins_loaded');

        //Load the settings
        $this->site = $this->get_config();
        $this->run_hooks('config_loaded', array(&$this->site));

        /**
         *Clean Site
         *It may be supported in the future!
         */

        //get_contents
        $this->run_hooks('before_get_contents');
        $data = $this->get_contents();
        $this->run_hooks('after_get_contents', array(&$data));

        //get_variables
        $this->run_hooks('before_get_variables');
        $variables = array(
            'site' => array_merge(array('posts'=> $data['post'], 'pages'=> $data['page']), $this->site)
        );
        $this->run_hooks('after_get_variables', array(&$variables));

        include $this->site['config']['root'].'/var/Twig/Autoloader.php';
        Twig_Autoloader::register();
        $twig_config = array('autoescape' => false);

        $this->run_hooks('before_create_twig_environment', array(&$this->site['config']['template'], &$twig_config));
        $loader = new Twig_Loader_Filesystem($this->site['config']['template']);
        $twig = new Twig_Environment($loader, $twig_config);
        $this->run_hooks('twig_loaded', array(&$variables, &$twig));

        //generate post
        $this->run_hooks('before_output_post', array(&$data['post']));
        if(!empty($data['post']))
        {
            foreach($data['post'] as $key => $post) {
                $post['next'] = isset($data['post'][$key+1]) ? array('title' => $data['post'][$key+1]['title'], 'url'=> $data['post'][$key+1]['url']) : '';    
                $post['prev'] = isset($data['post'][$key-1]) ? array('title' => $data['post'][$key-1]['title'], 'url'=> $data['post'][$key-1]['url']) : '';

                $template = file_exists("{$this->site['config']['template']}/{$post['template']}") ? $post['template'] : 'post.html';
                $twig_vars = array_merge(array('post'=>$post), $variables);
                $this->run_hooks('before_render', array(&$twig_vars, &$twig, &$template));
                $html = $twig->render($template, $twig_vars);
                $this->run_hooks('after_render', array(&$html));

                PMBlog::mkdir(dirname($this->site['config']['html'].$post['filepath'][0]));
                file_put_contents($this->site['config']['html'].$post['filepath'][0], $html);
                $this->run_hooks('after_file_put_contents', array(&$post, $this->site['config']['html'].$post['filepath'][0]));
            }
        }
        $this->run_hooks('after_output_post', array(&$variables, &$twig));

        //generate page
        $this->run_hooks('before_output_page', array(&$data['page']));
        if(!empty($data['page'])) 
        {
            foreach($data['page'] as $post)
            {
                $template = file_exists("{$this->site['config']['template']}/{$post['template']}") ? $post['template'] : 'page.html';
                $twig_vars = array_merge(array('post'=>$post), $variables);
                $this->run_hooks('before_render', array(&$twig_vars, &$twig, &$template));
                $html = $twig->render($template, $twig_vars);
                $this->run_hooks('after_render', array(&$html));

                PMBlog::mkdir(dirname($this->site['config']['html'].$post['filepath'][0]));
                file_put_contents($this->site['config']['html'].$post['filepath'][0], $html);
                $this->run_hooks('after_file_put_contents', array(&$post, $this->site['config']['html'].$post['filepath'][0]));
            }
        }
        $this->run_hooks('after_output_page', array(&$variables, &$twig));

        //generate index
        $this->run_hooks('before_output_index', array(&$data['post']));
        if(!empty($data['post']))
        {
        foreach($this->paginator($data['post']) as $paginator) 
        {
            $template = 'index.html';
            $twig_vars = array_merge(array('posts'=>$paginator['object_list'], 'paginator' => $paginator), $variables);
            $this->run_hooks('before_render', array(&$twig_vars, &$twig, &$template));
            $html = $twig->render($template, $twig_vars);
            $this->run_hooks('after_render', array(&$html));
            
            $index_path = "{$this->site['config']['html']}/page/{$paginator['page']}";
            PMBlog::mkdir($index_path);
            file_put_contents("$index_path/index.html", $html);
            $this->run_hooks('after_file_put_contents', array(&$paginator, &$index_path));
        }
        copy("{$this->site['config']['html']}/page/1/index.html", "{$this->site['config']['html']}/index.html");
        }
        $this->run_hooks('after_output_index', array(&$variables, &$twig));

        //RSS
        $loader = new Twig_Loader_Filesystem($this->site['config']['root'].'var');
        $twig = new Twig_Environment($loader, array('autoescape' =>false));
        $atom = $twig->loadTemplate('atom.xml');
        $posts  = array_slice($data['post'], 0, 20);
        file_put_contents($this->site['config']['html'].'atom.xml', $atom->render(compact('posts', $variables)));
        $rss = $twig->loadTemplate('rss.xml');
        file_put_contents($this->site['config']['html'].'rss.xml', $rss->render(compact('posts', 'site')));

        $this->run_hooks('end');
    }

    /**
     * paginator
     *
     * @param array post
     */
    public function paginator($post) 
    {
        $perpage = $this->site['config']['posts_per_page'];
        $total_pages = ceil(count($post)/$perpage);
        for($i=0;$i<$total_pages;$i++) 
        {
            $has_next = $has_previous = true;
            if(($i+2) > $total_pages) $has_next = false;
            if($i<1) $has_previous = false; 
            $pagpost = array_slice($post, $i*$perpage, $perpage);
            $paginator[$i+1]['prefix_url'] = $this->site['url'];
            $paginator[$i+1] =  array(
                'per_page' => $perpage,
                'object_list'=>$pagpost, 
                'total_pages'=> $total_pages, 
                'page' => $i+1, 
                'page_url' => $paginator[$i+1]['prefix_url'].'/page/'.($i+1),
                'previous_page' => $i,
                'pre_page' => $i, 
                'previous_page_url' => $paginator[$i+1]['prefix_url'].'/page/'.$i,
                'pre_page_url' => $paginator[$i+1]['prefix_url'].'/page/'.$i, 
                'next_page' => $i+2, 
                'next_page_url' => $paginator[$i+1]['prefix_url'].'/page/'.($i+2), 
                'has_next' => $has_next, 
                'has_previous' => $has_previous,
                'has_pre' => $has_previous
            );
        }

        $this->run_hooks('paginator_loaded', array(&$paginator));
        return $paginator;
    }

    /**
     * mkdir if dir doesn't exist.
     *
     * @param string directory's path
     */
    public function mkdir($path)
    {
        return is_writeable($path) || mkdir($path, 0777, true);
    }

    /**
     * Get content
     *
     * @return array all contents (posts & pages)
     */
    protected function get_contents() {
        include $this->site['config']['root']."/var/parse.php";
        foreach($this->get_files($this->site['config']['doc']) as $item) {
            $this->run_hooks('before_get_post_meta');

            $post = new parse($item);

            $log['status'] = $post->status();
            $date = $post->date();
            $log['date'] = date($this->site['config']['dateformat'], $date);
            
            if($log['status']) {
                $this->run_hooks('get_hidden_post_meta', array(&$post));
                continue;
            }
            $log['type'] = $post->type();
            $log['docpath'] = $post->doc();
            $log['filename'] = $post->doc_title();
            $log['extension'] = $post->doc_extension();
            $log['title'] = $post->title();
            $log['content'] = $post->text();
            $log['template'] = $post->tmp();
            $log['tags'] = $post->tags();
            $log['custom'] = $post->custom();
            if($log['type'] == 'post') {
                $log['cover'] = $post->image();
                $abstract = explode('<!--more-->', $log['content']);
                $log['read_more'] = isset($abstract[1]) ? true : false;
                $log['opening'] = $abstract[0];

                $dirname = dirname($item).'/';
                $dirname = str_replace($this->site['config']['doc'], '', $dirname);
                $dirname = explode('/', $dirname);
                $dirname = array_filter($dirname);
                $log['categories'] = $post->categories();
                if($log['categories'][0] != 'unclassified') {
                    $log['categories'] = array_merge($log['categories'], $dirname);
                } else {
                    if(!empty($dirname))
                        $log['categories'] = $dirname;
                }
            } else {
                unset($log['read_more']);
                unset($log['opening']);
                unset($log['categories']);
                unset($log['tags']);
            }

            $router = str_replace(
                array('{year}', '{month}', '{day}'),
                array(date('Y', $date), date('m', $date), date('d', $date)),
                $this->site['config']['router'][$log['type']]
            );
            if($log['type'] == 'post' && preg_match('/{category}/', $router)) {
                $log['filepath'] = array();
                foreach($log['categories'] as $category) {
                    $log['filepath'][] = str_replace('{category}', $category, $router) . "/{$log['filename']}.html";
                }
            } else {
                $log['filepath'] = array("$router/{$log['filename']}.html");
            }

            $log['url'] = $this->site['url'].$log['filepath'][0];


            $this->run_hooks('after_get_post_meta', array(&$log));
            $data[$log['type']][] = $log;
        }

        if(!isset($data['post'])) $data['post'] = array();
        if(!isset($data['page'])) $data['page'] = array();
        
        //$this->run_hooks('before_sort_contents');
        usort($data['post'], create_function('$a,$b', 'if ($a[\'date\'] == $b[\'date\']) return 0;return ($a[\'date\'] < $b[\'date\']) ? 1 : -1;'));
        //$this->run_hooks('after_sort_contents', array(&$data['post']));

        return $data;
    }

    /**
     * Clean site
     * Every file should be started with './'
     * It won't clean directory
     *
     * @param array file or directory wanna delete
     */
    public function clean($directory)
    {
        foreach(glob($directory.'/*') as $item) {
            if(is_file($item)) {
                unlink($item);
            } else {
                $this->clean($item);
            }
        }
    }

    /**
     * Load any plugins
     */
    protected function load_plugins()
    {
        global $config;
        $actived_plugins = trim($config['actived_plugins']);
        $active_all = $actived_plugins == '*';
        $actived_plugins = array_filter(explode(',', $actived_plugins));
        $this->plugins = array();
        $plugins = $this->get_files(ROOT_DIR.PLUGINS_DIR, '.php');
        if(!empty($plugins)) {
            foreach($plugins as $plugin) {
                include_once "$plugin";
                $plugin_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($plugin));
                if(($active_all || in_array($plugin_name,$actived_plugins)) && class_exists($plugin_name)) {
                    $obj = new $plugin_name;
                    $this->plugins[] = $obj;
                }
            }
        }
    }

    /**
     * Helper function to recusively get all files in a directory
     *
     * @param string $directory start directory
     * @param string $ext optional limit to file extensions
     * @return array the matched files
     */ 
    public function get_files($directory, $ext = '')
    {
        $array_items = array();
        if($handle = opendir($directory)){
        while(false !== ($file = readdir($handle))){
            if(preg_match("/^(^\.)/", $file) === 0){
            if(is_dir($directory. "/" . $file)){
                $array_items = array_merge($array_items, $this->get_files("$directory/$file", $ext));
            } else {
                $file = "$directory/$file";
                if(!$ext || strstr($file, $ext)) $array_items[] = preg_replace("/\/\//si", "/", $file);
            }
            }
        }
        closedir($handle);
        }
        return $array_items;
    }

    /**
     * Processes any hooks and runs them
     *
     * @param string $hook_id the ID of the hook
     * @param array $args optional arguments
     */
    protected function run_hooks($hook_id, $args = array())
    {
        if(!empty($this->plugins)){
            foreach($this->plugins as $plugin){
                if(is_callable(array($plugin, $hook_id))){
                    call_user_func_array(array($plugin, $hook_id), $args);
                }
            }
        }
    }

    /**
     * Loads the config
     *
     * @return array $config an array of config values
     */
    protected function get_config()
    {    
        global $config;
        define('SITE_DIR', $config['site_dir']);
        $site['title'] = $config['site_title'];
        $site['url'] = $config['base_url'];
        $site['config'] = array(
            'template' => ROOT_DIR.THEMES_DIR.'/'.$config['theme'].'/',    
            'dateformat' => $config['date_format'],    
            'root' => ROOT_DIR,
            'doc' => ROOT_DIR.CONTENTS_DIR.'/',    
            'html' => ROOT_DIR.SITE_DIR.'/',    
            'plugins' => ROOT_DIR.PLUGINS_DIR.'/',
            'posts_per_page' =>    $config['posts_per_page'],    
            'router' => array(
                'post' => $config['post_router'], 
                'page' => $config['page_router']
            )
        );
        return $site;
    }
}

$StartTime = microtime(true); 
/**
 * Check the config file
 */
if(file_exists("config.php"))
    include_once "config.php";
else 
    die("It seems you haven't create config.php, please set your config in the config.php linke the config.example.php file before you run this program.");

/**
 *Run the program
 */
$PMBlog = new PMBlog();

/**
 *Check update
 */
if(extension_loaded('cURL')) {
    $curl = curl_init('https://rawgithub.com/lizheming/PMBlog/master/version.json');
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $check = curl_exec($curl);
    curl_close($curl);
    $check = json_decode($check, true);
    if($check[0]['version'] > $PMBlog->version) {
    die('PMBlog has new version! <a href="http://github.com/lizheming/PMBlog" title="PMBlog">Click Here</a>to get it!<p>What\'s someting new： </p>'.$check[0]['description']);
    }    
}

/**
 *Output Runtime
 */
$EndTime = microtime(true); 
printf('It costs %.2fs to generate your <a href="'.SITE_DIR.'">blog</a>.',$EndTime - $StartTime);
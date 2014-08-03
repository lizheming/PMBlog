<?php 
class Hello {
    private $time;
    function __construct() {
        $this->time = microtime(true);
    }

    function end() {
        $time = microtime(true) - $this->time;
        printf('耗时 %.2fs', $time);
    }
}
?>
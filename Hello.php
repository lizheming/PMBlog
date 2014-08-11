<?php 
class Hello {
    private $time;
    function __construct() {
        $this->time = microtime(true);
    }

    function end() {
        $time = microtime(true) - $this->time;
        printf('Generation costs %.2fs', $time);
    }
}
?>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sib
 * Date: 14.10.13
 * Time: 17:40
 * To change this template use File | Settings | File Templates.
 */

class CacherImpl implements Zend_Cache_Backend_Interface {

    protected $callback;

    public function __construct($callback) {
        if ( is_callable($callback) ) {
            $this->callback = $callback;
        } else {
            $this->callback = $this->callbackDummy;
        }
    }

    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array()) {}

    public function remove($id) {}

    public function test($id) {}

    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        return call_user_func($this->callback, $id, $data);
    }

    public function load($id, $doNotTestCacheValidity = false)
    {
        return call_user_func($this->callback, $id);
    }

    public function setDirectives($directives) {}

    protected function callbackDummy($k, $v)
    {
        return null;
    }

} // CacherImpl class
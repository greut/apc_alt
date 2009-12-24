<?php

if(!function_exists("apc_add")) {

    if(!defined("APC_ALT_DIR")) {
        define ("APC_ALT_DIR", "tmp"); // must be writable (0777)
    }
    if(!defined("APC_ALT_EXT")) {
        define ("APC_ALT_EXT", ".apc_alt.cache");
    }


    function _apc_filename($key) {
        return APC_ALT_DIR."/".$key.APC_ALT_EXT;
    }

    function apc_add($key, $var, $ttl=0) {
        // like apc_store but doesn't override
        // an existing value
        $filename = _apc_filename($key);
        if(!file_exists($filename)) {
            return apc_store($key, $var, $ttl);
        }
        return false;
    }

    function apc_clear_cache($cache_type=null) {
        $files = glob(APC_ALT_DIR."/*".APC_ALT_EXT);
        foreach($files as $file) {
            @unlink($file);
        }
        return true;
    }

    function apc_delete($key) {
        $filename = _apc_filename($key);
        if(file_exists($filename)) {
            return @unlink($filename);
        } else {
            return false;
        }
    }

    function apc_fetch($key, &$success) {
        $success = false;
        $filename = _apc_filename($key);
        if(file_exists($filename)) {
            $content = unserialize(file_get_contents($filename));
            if($content["ttl"] === 0 || $content["ttl"] > time()) {
                $success = true;
                return $content["var"];
            } else {
                apc_delete($key);
            }
        }
        return null;
    }
    
    function apc_store($key, $var, $ttl=0) {
        $filename = _apc_filename($key);
        $data = array("ttl" => $ttl ? time()+$ttl : $ttl,
                      "var" => $var);

        return file_put_contents($filename, serialize($data), LOCK_EX);
    }
}

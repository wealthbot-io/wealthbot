<?php

if (!function_exists('apc_add')) {
    function apc_add($key, $var = null, $ttl = 0) { return apcu_add($key, $var, $ttl); }
    function apc_cache_info($limited = false) { return apcu_cache_info('user', $limited); }
    function apc_cas($key, $old, $new) { return apcu_cas($key, $old, $new); }
    function apc_clear_cache() { return apcu_clear_cache('user'); }
    function apc_dec($key, $step = 1, &$success = false) { return apcu_dec($key, $step, $success); }
    function apc_delete($key) { return apcu_delete($key); }
    function apc_exists($keys) { return apcu_exists($keys); }
    function apc_fetch($key, &$success = false) { return apcu_fetch($key, $success); }
    function apc_inc($key, $step = 1, &$success = false) { return apcu_inc($key, $step, $success); }
    function apc_sma_info($limited = false) { return apcu_sma_info($limited); }
    function apc_store($key, $var = null, $ttl = 0) { return apcu_store($key, $var, $ttl); }
}
if (!class_exists('APCIterator', false) && class_exists('APCUIterator', false)) {
    class APCIterator extends APCUIterator
    {
        public function __construct($search = null, $format = APC_ITER_ALL, $chunk_size = 100, $list = APC_LIST_ACTIVE)
        {
            parent::__construct('user', $search, $format, $chunk_size, $list);
        }
    }
}
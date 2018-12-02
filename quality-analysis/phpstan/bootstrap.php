<?php

if (!function_exists('opcache_invalidate')) {
    function opcache_invalidate($script, $force = false)
    {
    }
}

require sprintf('%s/../../vendor/autoload.php', __DIR__);

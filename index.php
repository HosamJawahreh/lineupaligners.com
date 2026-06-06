<?php

/**
 * Fallback front controller when the host document root is the project folder
 * instead of public/. Prefer setting the vhost document root to /public when possible.
 */
require __DIR__.'/public/index.php';

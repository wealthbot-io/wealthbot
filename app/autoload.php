<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
AnnotationDriver::registerAnnotationClasses();

return $loader;

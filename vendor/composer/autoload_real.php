<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInite80dcf208f04ed92efcf2d39b75b990c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInite80dcf208f04ed92efcf2d39b75b990c', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInite80dcf208f04ed92efcf2d39b75b990c', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInite80dcf208f04ed92efcf2d39b75b990c::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

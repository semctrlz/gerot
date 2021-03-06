<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit87e0b5e75e8b7f6b7b556ec0139a68b6
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zware\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zware\\' => 
        array (
            0 => __DIR__ . '/..' . '/zware/php-classes/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Slim' => 
            array (
                0 => __DIR__ . '/..' . '/slim/slim',
            ),
        ),
        'R' => 
        array (
            'Rain' => 
            array (
                0 => __DIR__ . '/..' . '/rain/raintpl/library',
            ),
        ),
    );

    public static $classMap = array (
        'EasyPeasyICS' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/EasyPeasyICS.php',
        'PHPMailer' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
        'PHPMailerOAuth' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauth.php',
        'PHPMailerOAuthGoogle' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauthgoogle.php',
        'POP3' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.pop3.php',
        'SMTP' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.smtp.php',
        'ntlm_sasl_client_class' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/ntlm_sasl_client.php',
        'phpmailerException' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit87e0b5e75e8b7f6b7b556ec0139a68b6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit87e0b5e75e8b7f6b7b556ec0139a68b6::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit87e0b5e75e8b7f6b7b556ec0139a68b6::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit87e0b5e75e8b7f6b7b556ec0139a68b6::$classMap;

        }, null, ClassLoader::class);
    }
}

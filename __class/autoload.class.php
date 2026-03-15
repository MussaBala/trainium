<?php

/**
 * @copyright  : HAKILITY
 */

/**
 * Charge les classes de manière automatique
 *
 * @updated 20230405
 */
spl_autoload_register(
    function ( $class ) {

        include_once( $class . '.class.php' );
        //include_once( __DIR__ . '/functions.php' );

    }
);


// ✅ Ajout de Composer Autoload
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
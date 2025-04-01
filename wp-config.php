<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '{cOT0vK}*5FN88tFz1o>iVwzi^c[N5k+dr:&&NC/&`7nCN(?bl0(@FbAGMqN=^tq' );
define( 'SECURE_AUTH_KEY',   'I2A~x03~:%}3_)`0;,s@]&,v![^?}M]d_Lf/c>1#=,fj=`Udg}|gyzSAzXc^4)$<' );
define( 'LOGGED_IN_KEY',     'Nj2>kDp;kkRYjblek1kS.:%1^RE8#Ng!bWiXEmO(p*y#*]m&_Qi=N:lM5}>Gi=4$' );
define( 'NONCE_KEY',         'T 2{~NMui|qjD>VGu3,CQnDkCUtd,^*_lqU.oRn.C-5a*;GEkee()Ba`Ntt-46Yv' );
define( 'AUTH_SALT',         'FJ.BIQd.|8LEcVV2?,zDrI@MiK38Y.?r+azH y#2FDpi0w<6^+<Eb(TP~eu5 AWV' );
define( 'SECURE_AUTH_SALT',  'ZD^wN`O!^]<J(QV:hRPp9-5k[V3sl`>30%mUOFbhc0rB.)qYN]-1jE]R6Q;|>qXi' );
define( 'LOGGED_IN_SALT',    ')3`% jF5qK7(ec:wPYC^/h(m<iU_FuH3stp|j|36T|pqd7>n(r$Mu^$CnrN#[+[{' );
define( 'NONCE_SALT',        'n5Ru:E_T!G75#RxPWqG?#e5d;1{R0^NI&x: %uFVXj4P_md=C#MFmkQTv[7J}DB$' );
define( 'WP_CACHE_KEY_SALT', '2xTPW:tW44[EGiv2,mmi<Aj%:]}Xp[m]l$n>4v+zbx9hXwy8;De!g,fL`=0=MkJ3' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

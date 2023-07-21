<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'organic' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Mg+=EiimS-+=>?IA2hht^ZGXm*)Mnbki&qzk}jJejn f4#)Yb)FcdLJheFX,T_.u' );
define( 'SECURE_AUTH_KEY',  'Rk%#Ukny?YF]TXScO!);s31xM:nt*d2:KC~VMzrf7)4[p`Mq@,M) xr<P}?@}7/f' );
define( 'LOGGED_IN_KEY',    'g1O<`ewnP]#Z+xd6;>O8~i2Bj0XWR&-9byYTn^M6iS)%;$2q1P$9!=),`6UXYU$=' );
define( 'NONCE_KEY',        '^+6J!pqxp!UV!-9D10OUE>#gWUE)O8q0^8]]lRG]7?)pe|zqPr{;ZC#>IF>d3cr^' );
define( 'AUTH_SALT',        'e;VHN:&HA|(-,X}[ah*`kB37Laa|HC];I`9{=(Y$r*Y$R~hRwXu;_5<,IdPaXX,F' );
define( 'SECURE_AUTH_SALT', 'OO#jj|`i}VrWD&tX(iUL=l~.&pPGXPh$9D}T%Th[wa^KWJ^8;51OV[*?QO7U9qOc' );
define( 'LOGGED_IN_SALT',   'h=z@OVQZL(g9(-XNz9XBY3bY6>#<q!.9<I%`^]u&;;$>A|tn0[&4_bOg`e8ga%WX' );
define( 'NONCE_SALT',       '7{e!oj|.oo}iMM=UzL^l4e_{%J1|ex,:GhgxQslW+0|7d1l<$pX7NJJ$9fBx7RIv' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

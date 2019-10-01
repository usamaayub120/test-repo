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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'laundrycare' );

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

define( 'WP_MEMORY_LIMIT', '256M' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Hf[j=iERZI(NmUCExb2?kV@_?*PJGWgLl03}:(b^1o_f{L gn?8`q>kwT0n)0ZyZ' );
define( 'SECURE_AUTH_KEY',  'sMsG! JVwA|J:S32:(1z[1?_4Z&uXNn=H#ls~X4/vD7}%E}f)2uRC6k:Q{L>f{r4' );
define( 'LOGGED_IN_KEY',    '^3.Q.Q-SfEVd@qE{t8eW4SFwl[.UqnC^ROW=s(vp@h0[]DXX6N~-GxV1r=lzx j2' );
define( 'NONCE_KEY',        '/F8a*Uf(0UrF*,s<C IpUu`30k]tKIH!*E0$bI>18lTqrC)&u;Axz_OgUx~%&fY!' );
define( 'AUTH_SALT',        '$Wa-vW[GY.Wvs*xwk!?>pE7`?I7J_#3!!hqd?B^.[Af<7*iGIQ(RsCARNjn3F]mL' );
define( 'SECURE_AUTH_SALT', 'c(YOh=vO+)H5xZbiK?j($-[qr)]9V$0L;T-^irIV9YkTI36Uyd(.}:Ke (yH?Rjy' );
define( 'LOGGED_IN_SALT',   'b/d@cw@NAmh[tuW1A9AS&cTjP<NPS)x-C }xKBvJRyQOdGz=4abu5LJ0heJC9|UD' );
define( 'NONCE_SALT',       'X[rO30^#@iI-K~QN$ZFfY##J9tTdF@XwM40P<F{HXsEDCX:+c$<;L#-T`rl/mVQH' );

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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

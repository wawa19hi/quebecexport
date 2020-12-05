<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'quebecexport' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '/O{]Xy``eUV_H~mm0H;2%nL0ctKGZXES3hH5eA@<:%BZ}7@:6K)vIw4%D+<{DzsI' );
define( 'SECURE_AUTH_KEY',  '(RIT2xg-mK?sv<#1,GFj=T!aH}o_;6vS^@_j1|gKScN? yu=[Xl)3fcVtitwe`T2' );
define( 'LOGGED_IN_KEY',    'so{PxV>#x-$nn^`B%&98:[hE8Y749]Qo{y=-tJUiVpY#Itl`,wgGK&E~j] j+Bqg' );
define( 'NONCE_KEY',        'fz19Vzn?mA0XO^]XE#A,99FDla63^IBAt.S+rFI1 0Ga0Ii.)_?w9n>5qSoJEAcI' );
define( 'AUTH_SALT',        '$PKsro,4+49by(J.3QWS]NC0I}DbKBi{*%g>ZX+Q^XM2k]gRO|Y>Rn6D5agmok!T' );
define( 'SECURE_AUTH_SALT', 'ySHJZ@eZQSt_V,Q#*tPR~<4k.g]0 Z5tjniP[73SwE1rDcAH6jM07SJ(QcqP&de]' );
define( 'LOGGED_IN_SALT',   '/|C)Ww`SDiI*3GP@;Xh8B[=/W`f[!~MsgOjxfv_Jb^2AflgW_6fCh/hVjhHAttd]' );
define( 'NONCE_SALT',       '>@)v*]k%VMAM2Heu$X`32$sdWx{a,Nsl<IBRjX3-D[`^~KD7zua^%G5F!2w7Wzt/' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );

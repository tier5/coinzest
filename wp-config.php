<?php
define('DB_NAME', 'coinzest_dev');
define('DB_USER', 'coinzest_dev');
define('DB_PASSWORD', 'Le8kEjau3.e');
define('DB_HOST', 'localhost');

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('AUTH_KEY',         '>/A9]]sb%}.Px^Z-5iXg7x|-Q/Hxm<MLl9[<>wJ[7m,|Rg~KTeg@BeDmMX^h7UX.');
define('SECURE_AUTH_KEY',  'eBJ)NPC<-lG7U!m1M})(?EnAe`9[#o$>1|),XcW|:a{pw%rq*ojKN?_pIb.DsN*O');
define('LOGGED_IN_KEY',    '9qKa-$)-XWM>8aLFmG#DMM%9V${+2Ce?(c@}4n9Sapagb9Rcp(iZb%?EbyXN]~d~');
define('NONCE_KEY',        'doOK/4FMnAR~JcMv@oVf9<X)%4OT1TAPUF1A&2kAR1}$|d#RUFr0pfJwf{{bKc&]');
define('AUTH_SALT',        'H5PI/9!:q@Rz~K6_UfR7>j&&|;s=k4?TTx,+>*G_UgQyLib|{Q,kY>}`X.HkM:@:');
define('SECURE_AUTH_SALT', '5@a#Etyo~uQ;t$a^ry9/K4EZ;301%TmqSkH}(FA|?+ykEh:UK*AX*WCcc+v`sS-j');
define('LOGGED_IN_SALT',   'LHgbTo~6NB};~gc)?`X&[LwH!(e/_f G2i+7bAdZnLJ_Fvp}Wff}#[m(&I1_]}^A');
define('NONCE_SALT',       '`aM:|o]rFQ-?=x= D!.4nVjbOBPA9|@kdEuF/QG,Vu+/_;jtbKw0Kt9u.Z1Rzien');
$table_prefix  = 'wp_';
define('WP_DEBUG', false);
if (!defined('ABSPATH')) {
  define('ABSPATH', dirname(__FILE__) . '/');
}
require_once(ABSPATH . 'wp-settings.php');

<?php

$databases          = array();
$update_free_access = FALSE;
$drupal_hash_salt   = '';
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.cookie_lifetime', 2000000);

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'drupal7',
  'username' => 'drupal7',
  'password' => 'drupal7',
  'host' => 'database',
  'prefix' => '',
  'collation' => 'utf8_general_ci',
);

$conf['404_fast_paths_exclude'] = '/\/(?:styles)\//';
$conf['404_fast_paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$conf['404_fast_html'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

/**

Redis (phpredis) Configuration

 **/
// Checking if Redis is running, only locally.
// For remote servers comment the if() statement.
if (file_exists('/var/run/redis/redis-server.pid')) {
  $redis_up = TRUE;
}

if (file_exists('sites/all/modules/entitycache/entitycache.info')) {
  $entity_cache = TRUE;
}

if (!empty($redis_up)) {

  // Required configurations.
  $conf['lock_inc'] = 'sites/all/modules/redis/redis.lock.inc';
  $conf['cache_backends'][] = 'sites/all/modules/redis/redis.autoload.inc';
  $conf['redis_client_interface'] = 'PhpRedis';
  $conf['redis_client_base'] = 1;
  $conf['redis_client_host'] = '127.0.0.1';
  $conf['redis_client_port'] = '6379';
  // Uncomment this line if Redis is locally running via socket.
  // $conf['redis_cache_socket'] = '/var/run/redis/redis.sock';
  // $conf['cache_prefix'] = 'mysite_';

  // Optional not redis specific.
  // $conf['cache_lifetime'] = 0;
  // $conf['page_cache_max_age'] = 0;
  // $conf['page_cache_maximum_age'] = 0;
  $conf['page_cache_invoke_hooks'] = TRUE;
  $conf['page_cache_without_database'] = FALSE;
  // $conf['redis_client_password'] = 'isfoobared';

  // Cache bins.
  $conf['cache_default_class'] = 'Redis_Cache';
  $conf['cache_class_cache_bootstrap'] = 'Redis_Cache';
  $conf['cache_class_cache'] = 'Redis_Cache';
  $conf['cache_class_cache_menu'] = 'Redis_Cache';
  $conf['cache_class_cache_block'] = 'Redis_Cache';
  $conf['cache_class_cache_views'] = 'Redis_Cache';
  $conf['cache_class_cache_views_data'] = 'Redis_Cache';
  $conf['cache_field'] = 'Redis_Cache';
  $conf['cache_class_cache_field'] = 'Redis_Cache';
  $conf['cache_class_cache_image'] = 'Redis_Cache';
  $conf['cache_class_cache_libraries'] = 'Redis_Cache';
  $conf['cache_class_cache_metatag'] = 'Redis_Cache';
  $conf['cache_class_cache_search_api_solr'] = 'Redis_Cache';

  // Always Database Cache.
  $conf['cache_class_cache_form'] = 'DrupalDatabaseCache';

  // Entity Cache.
  if (!empty($entity_cache)) {
    $conf['cache_entity_node'] = 'Redis_Cache';
    $conf['cache_entity_fieldable_panels_pane'] = 'Redis_Cache';
    $conf['cache_entity_file'] = 'Redis_Cache';
    $conf['cache_entity_taxonomy_term'] = 'Redis_Cache';
    $conf['cache_entity_taxonomy_vocabulary'] = 'Redis_Cache';
  }

}

/**
 * custom little bit to define if we're proxied or not
 **/

if(strpos($_SERVER['SERVER_NAME'], 'app1') !== FALSE || strpos($_SERVER['SERVER_NAME'], 'app2') || strpos($_SERVER['SERVER_NAME'], 'uat') !== FALSE) {
  DEFINE('PROXIED', TRUE);
}
else {
  DEFINE('PROXIED', FALSE);
}

DEFINE('DEV', TRUE);
DEFINE('PRODUCTION', FALSE);
DEFINE('UAT', FALSE);



/**
 * Session Proxy + Redis  (danderson, 2015-05-19)
 *
 * Store session data in Redis cache instead of in MySQL
 *
 * The intention is to alleviate as much load as possible from the MySQL
 * server. MySQL disk writes are (as of this writing) the achilles heel of
 * the Telus Commerce Drupal application.
 *
 * Resources:
 *  http://drupal.stackexchange.com/questions/125932/use-redis-for-drupal-sessions
 *  http://blog.arvixe.com/drupal7-redis-php-sessions/
 *
 */
if (!empty($redis_up)) {
  if (file_exists('sites/all/modules/session_proxy/session.inc')) {
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', 'tcp://' . $conf['redis_client_host'] . ':' . $conf['redis_client_port']);

    // Here is an example of usage the cache storage engine, using the Redis
    // module for storing sessions.
    // Actually no session locking applied in Drupal so race conditions on session
    // write operations (for the same user/session) can happen (like in default
    // Drupal sessions).
    // Replace core session management with Session Proxy API.
    $conf['session_inc'] = 'sites/all/modules/session_proxy/session.inc';
    //
    // do not forget to empty the sessions table in Drupal, not used anymore
    //
    // If you set this to TRUE, session management will be PHP native session
    // management. By doing this, all other parameters below will be ignored.
    $conf['session_storage_force_default'] = TRUE;

    // PHP class to use as session storage engine.
    // we use the 'Cache' storage backend to use Drupal7 cache backends
    // (or the same with Drupal6 & cache_backport module)'
    $conf['session_storage_class'] = 'SessionProxy_Storage_Cache';
    // Everything into 'session_storage_options' are arbitrary key value pairs,
    // each storage backend will define its own keys.
    // For cache backend, the only mandatory one is the class name that to use
    // as cache backend. This class must implement DrupalCacheInterface. If you
    // do not set this class 'DrupalDatabaseCache' will be used.
    $conf['session_storage_options']['cache_backend'] = 'Redis_Cache';
    // Tell Drupal to load the Redis backend properly (if not done previously in
    // settings), see the Redis module documentation for details about this.
    //$conf['cache_backends'][] = 'sites/all/modules/redis/redis.autoload.inc';
    //$conf['redis_client_interface'] = 'PhpRedis';
  }
}


// if(!isset($_COOKIE["prov"])) { setcookie("prov", "ON"); $_COOKIE["prov"] = "ON"; }; if(!isset($_COOKIE["lang"])) { setcookie("lang", "en"); $_COOKIE["lang"] = "en"; }; define("SET_DEFAULT_COOKIE_FOR_DEVELOPERS", TRUE);
if(!isset($_COOKIE["prov"])) { $developerDefaultProvLangCookieExpiry=time()+60*60*24*365; setcookie("prov", "ON", $developerDefaultProvLangCookieExpiry, "/"); $_COOKIE["prov"] = "ON"; }; if(!isset($_COOKIE["lang"])) { setcookie("lang", "en", $developerDefaultProvLangCookieExpiry, "/"); $_COOKIE["lang"] = "en"; }; define("SET_DEFAULT_COOKIE_FOR_DEVELOPERS_V2", TRUE);

define('COMMERCE_INSTALL_PATH', '/app');
define('SERVICES_WORKER_PATH', '/app/scripts/worker.php');

$settings['trusted_host_patterns'] = [
  '^127\.0\.0\.1$',
  '^nginx$',
  '^localhost$'
];

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'mysql_strong_password',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$config['system.logging']['error_level'] = 'verbose';

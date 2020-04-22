<?php

$settings['reverse_proxy'] = TRUE;

$settings['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);

$settings['trusted_host_patterns'] = [
  '^.+\.umd\.edu$',
];

$settings['update_free_access'] = FALSE;

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;

$settings['entity_update_backup'] = TRUE;

/**
 * Setting for Docker Deployment
 */

// Database
$databases['default']['default'] = [
    'driver' => 'pgsql',
    'database' => 'umdsandbox',
    'username' => 'drupaluser',
    'password' => '$DB_PASSWORD',
    'host' => 'db',
    'prefix' => '',
];

// Config and Content Directories
$config_directories['sync'] = '/app/web/demo/sync/config';

// Hash Salt
$settings['hash_salt'] = '$HASH_SALT';


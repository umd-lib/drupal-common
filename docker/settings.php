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
    'database' => 'drupaldb',
    'username' => 'drupaluser',
    'password' => '$DB_PASSWORD',
    'host' => 'db',
    'prefix' => '',
];

// Config and Content Directories
$config_directories['sync'] = '/app/web/staff-blog/web/config';

// Hash Salt
$settings['hash_salt'] = '$HASH_SALT';

// Digest
$settings['digest_user'] = '$DIGEST_USER';
$settings['digest_key'] = '$DIGEST_KEY';
$settings['digest_reply_to'] = '$DIGEST_REPLY_TO';


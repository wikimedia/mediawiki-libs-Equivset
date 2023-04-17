<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config-library.php';

$cfg['directory_list'][] = 'vendor/';

$cfg['exclude_analysis_directory_list'][] = 'vendor/';

return $cfg;

<?php

use Kopper\Config;

$config = array(
  'global' => array(
    'aws.region' => 'us-east-1',
    'env.prefix' => 'kopper'
  ),
  'local' => array(
    'aws.key' => get_cfg_var('kopper.aws.key'),
    'aws.secret' => get_cfg_var('kopper.aws.secret')
  )
);

Config::init($config);

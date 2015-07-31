<?php

namespace Kopper\Aws;

use Aws\ElastiCache\ElastiCacheClient;
use Kopper\Cache\LocalCache;

class Cache extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new ElastiCacheClient($this->config);
  }

  public function getEndpoints($name) {
    $key = 'global_cache_endpoints_' . $name;

    $endpoints = LocalCache::get($key);

    if (empty($endpoints) === true) {
      $endpoints = array();

      $cluster = $this->client->describeCacheClusters(array(
        'CacheClusterId' => $name,
        'ShowCacheNodeInfo' => true
      ));

      if (empty($cluster['CacheClusters']) === false) {
        foreach ($cluster['CacheClusters'] as $cluster) {
          if (empty($cluster['CacheNodes']) === false) {
            foreach ($cluster['CacheNodes'] as $node) {
              array_push($endpoints, $node['Endpoint']['Address']);
            }
          }
        }
      }

      LocalCache::set($key, $endpoints);
    }

    return $endpoints;
  }

}

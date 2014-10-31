<?php

namespace Kopper\Aws;

use Aws\S3\S3Client;

class File extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = S3Client::factory($this->config);
  }

  public function register() {
    $this->client->registerStreamWrapper();
  }

  public function getFile($bucket, $key) {
    $result = $this->client->getObject(array(
      'Bucket' => $bucket,
      'Key' => $key
    ));

    return $result['Body'];
  }

  public function saveFile($bucket, $key, $data, $contentType = null, $acl = 'public-read', $params = array()) {
    $defaultParams = array(
      'Bucket' => $bucket,
      'Key' => $key,
      'Body' => $data
    );

    if (empty($acl) === false) {
      $defaultParams['ACL'] = $acl;
    }

    if (empty($contentType) === true) {
      $contentType = self::determineMimeType($key);
    }

    $defaultParams['ContentType'] = $contentType;

    $result = $this->client->putObject(array_merge($defaultParams, $params));

    return empty($result) === false;
  }

  public function copyFile($bucket, $source, $destination, $contentType = null, $acl = 'public-read', $params = array()) {
    $defaultParams = array(
      'Bucket' => $bucket,
      'CopySource' => "$bucket/$source",
      'Key' => $destination
    );

    if (empty($acl) === false) {
      $defaultParams['ACL'] = $acl;
    }

    if (empty($contentType) === true) {
      $contentType = self::determineMimeType($source);
    }

    $defaultParams['ContentType'] = $contentType;

    $result = $this->client->copyObject(array_merge($defaultParams, $params));

    return empty($result) === false;
  }

  public function deleteFile($bucket, $key) {
    $this->client->deleteObject(array(
      'Bucket' => $bucket,
      'Key' => $key
    ));
  }

  public function listFiles($bucket, $prefix = null, $delimiter = null) {
    $params = array(
      'Bucket' => $bucket
    );

    if (empty($prefix) == false) {
      $params['Prefix'] = $prefix;
    }

    if (empty($delimiter) == false) {
      $params['Delimiter'] = $delimiter;
    }

    $result = $this->client->listObjects($params);
    $contents = $result['Contents'];

    while ($result['IsTruncated'] == true) {
      $last = $contents[count($contents) - 1];
      $params['Marker'] = $last['Key'];

      $result = $this->client->listObjects($params);
      $contents = array_merge($contents, $result['Contents']);
    }

    return $contents;
  }

  public static function determineMimeType($path) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $type = empty(self::$mimeTypesMap[$ext]) ? null : self::$mimeTypesMap[$ext];

    return $type;
  }

  public static $mimeTypesMap = array(
    'avi' => 'video/avi',
    'bmp' => 'image/bmp',
    'conf' => 'text/plain',
    'css' => 'text/css',
    'exe' => 'application/octet-stream',
    'gif' => 'image/gif',
    'gzip' => 'application/x-gzip',
    'html' => 'text/html',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'js' => 'application/x-javascript',
    'json' => 'application/json',
    'mov' => 'video/quicktime',
    'mp3' => 'audio/mpeg',
    'mp4' => 'video/mp4',
    'png' => 'image/png',
    'swf' => 'application/x-shockwave-flash',
    'tiff' => 'image/tiff',
    'txt' => 'text/plain',
    'wav' => 'audio/wav',
    'webm' => 'video/webm',
    'xml' => 'text/xml',
    'xsl' => 'text/xsl',
    'zip' => 'application/zip'
  );

}

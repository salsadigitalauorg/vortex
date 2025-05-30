<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\File;
use Symfony\Component\Yaml\Yaml;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class Services extends AbstractHandler {

  const CLAMAV = 'clamav';

  const VALKEY = 'valkey';

  const SOLR = 'solr';

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    if (!$this->isInstalled()) {
      return NULL;
    }

    try {
      $dc = Yaml::parseFile($this->dstDir . '/docker-compose.yml');
    }
    catch (\Exception $exception) {
      return NULL;
    }

    $services = [];

    if (isset($dc['clamav'])) {
      $services[] = self::CLAMAV;
    }

    if (isset($dc['solr'])) {
      $services[] = self::SOLR;
    }

    if (isset($dc['valkey'])) {
      $services[] = self::VALKEY;
    }

    sort($services);

    return $services;
  }

  /**
   * {@inheritdoc}
   */
  public function process(): void {
    if (!is_array($this->response)) {
      throw new \RuntimeException('Invalid response type.');
    }

    $v = $this->response;
    $t = $this->tmpDir;
    $w = $this->webroot;

    // Batch process token removal operations for better performance
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($v): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      
      // Handle ClamAV tokens
      if (in_array(self::CLAMAV, $v)) {
        $content = File::removeToken($content, '#;< !SERVICE_CLAMAV', '#;> !SERVICE_CLAMAV', TRUE);
      } else {
        $content = File::removeToken($content, '#;< SERVICE_CLAMAV', '#;> SERVICE_CLAMAV', TRUE);
      }
      
      // Handle Solr tokens
      if (in_array(self::SOLR, $v)) {
        $content = File::removeToken($content, '#;< !SERVICE_SOLR', '#;> !SERVICE_SOLR', TRUE);
      } else {
        $content = File::removeToken($content, '#;< SERVICE_SOLR', '#;> SERVICE_SOLR', TRUE);
      }
      
      // Handle Valkey tokens
      if (in_array(self::VALKEY, $v)) {
        $content = File::removeToken($content, '#;< !SERVICE_VALKEY', '#;> !SERVICE_VALKEY', TRUE);
      } else {
        $content = File::removeToken($content, '#;< SERVICE_VALKEY', '#;> SERVICE_VALKEY', TRUE);
      }
      
      $file_info->setContent($content);
      return $file_info;
    });
    
    File::runTaskDirectory($t);
    
    // Handle non-token operations (file removals and specific file content changes)
    if (!in_array(self::CLAMAV, $v)) {
      File::rmdir($t . DIRECTORY_SEPARATOR . '.docker/config/clamav');
      @unlink($t . DIRECTORY_SEPARATOR . '.docker/clamav.dockerfile');
      @unlink($t . DIRECTORY_SEPARATOR . $w . DIRECTORY_SEPARATOR . 'sites/default/includes/modules/settings.clamav.php');
      @unlink($t . DIRECTORY_SEPARATOR . 'tests/behat/features/clamav.feature');
      File::replaceContent($t . DIRECTORY_SEPARATOR . 'docker-compose.yml', 'command: database:3306 clamav:3310', 'command: database:3306');
      File::replaceContent($t . DIRECTORY_SEPARATOR . 'composer.json', '/\s*"drupal\/clamav":\s*"[^\"]+",?\n/', "\n");
    }

    if (!in_array(self::SOLR, $v)) {
      File::rmdir($t . DIRECTORY_SEPARATOR . '.docker/config/solr');
      @unlink($t . DIRECTORY_SEPARATOR . '.docker/solr.dockerfile');
      @unlink($t . DIRECTORY_SEPARATOR . $w . DIRECTORY_SEPARATOR . 'sites/default/includes/modules/settings.solr.php');
      @unlink($t . DIRECTORY_SEPARATOR . 'tests/behat/features/search.feature');
      File::replaceContent($t . DIRECTORY_SEPARATOR . 'composer.json', '/\s*"drupal\/solr":\s*"[^\"]+",?\n/', "\n");
      File::replaceContent($t . DIRECTORY_SEPARATOR . 'composer.json', '/\s*"drupal\/search_api_solr":\s*"[^\"]+",?\n/', "\n");
      File::removeLine($t . DIRECTORY_SEPARATOR . '.ahoy.yml', 'VORTEX_HOST_SOLR_PORT=$(docker compose port solr 8983 2>/dev/null | cut -d : -f 2) \\');

      $locations = [
        $t . sprintf('/%s/modules/custom/*_search', $w),
        $t . sprintf('/%s/sites/all/modules/custom/*_search', $w),
        $t . sprintf('/%s/profiles/*/modules/*_search', $w),
        $t . sprintf('/%s/profiles/*/modules/custom/*_search', $w),
        $t . sprintf('/%s/profiles/custom/*/modules/*_search', $w),
        $t . sprintf('/%s/profiles/custom/*/modules/custom/*_search', $w),
      ];

      $path = File::findMatchingPath($locations);
      if ($path) {
        File::rmdir($path);
      }
    }

    if (!in_array(self::VALKEY, $v)) {
      File::rmdir($t . DIRECTORY_SEPARATOR . '.docker/config/valkey');
      @unlink($t . DIRECTORY_SEPARATOR . '.docker/valkey.dockerfile');
      @unlink($t . DIRECTORY_SEPARATOR . $w . DIRECTORY_SEPARATOR . 'sites/default/includes/modules/settings.redis.php');
      File::replaceContent($t . DIRECTORY_SEPARATOR . 'composer.json', '/\s*"drupal\/redis":\s*"[^\"]+",?\n/', "\n");
    }
  }

}

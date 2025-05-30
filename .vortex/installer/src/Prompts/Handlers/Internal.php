<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Config;
use DrevOps\Installer\Utils\Env;
use DrevOps\Installer\Utils\File;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class Internal extends AbstractHandler {

  public function discover(): null|string|bool|array {
    // Noop.
    return NULL;
  }

  public function process(): void {
    $version = (string) $this->config->get(Config::VERSION);
    
    $this->processDemoMode($this->responses, $this->tmpDir);

    // Batch process all directory operations for better performance
    $ignore_empty_line_processing = [
      '/web/sites/default/default.settings.php',
      '/web/sites/default/default.services.yml',
      '/.docker/config/solr/config-set/',
    ];
    $tmpDir = $this->tmpDir;
    
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($version, $ignore_empty_line_processing, $tmpDir): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      
      // Replace version placeholders
      $content = File::replaceContent($content, 'VORTEX_VERSION_URLENCODED', str_replace('-', '--', $version));
      $content = File::replaceContent($content, 'VORTEX_VERSION', $version);
      
      // Remove code required for Vortex maintenance
      $content = File::removeToken($content, '#;< VORTEX_DEV', '#;> VORTEX_DEV', TRUE);
      
      // Remove all other comments (equivalent to removeTokenInDir without parameters)
      $content = File::removeToken($content, '#;', '#;', FALSE);
      
      // Enable commented out code
      $content = File::replaceContent($content, '##### ', '');
      
      // Process empty lines (equivalent to the separate empty line processing step)
      // But exclude specific files that should not have empty line processing
      $relative_path = str_replace($tmpDir, '', $file_info->getPathname());
      if (!in_array($relative_path, $ignore_empty_line_processing)) {
        $content = File::replaceContent($content, '/(\n\s*\n)+/', "\n\n");
      }
      
      $file_info->setContent($content);
      return $file_info;
    });
    
    File::runTaskDirectory($this->tmpDir);

    if (file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'README.dist.md')) {
      rename($this->tmpDir . DIRECTORY_SEPARATOR . 'README.dist.md', $this->tmpDir . DIRECTORY_SEPARATOR . 'README.md');
    }

    // Remove Vortex internal files.
    File::rmdir($this->tmpDir . DIRECTORY_SEPARATOR . '.vortex');

    @unlink($this->tmpDir . '/.github/FUNDING.yml');
    @unlink($this->tmpDir . 'CODE_OF_CONDUCT.md');
    @unlink($this->tmpDir . 'CONTRIBUTING.md');
    @unlink($this->tmpDir . 'LICENSE');
    @unlink($this->tmpDir . 'SECURITY.md');

    // Remove Vortex internal GHAs.
    $files = glob($this->tmpDir . '/.github/workflows/vortex-*.yml');
    if ($files) {
      foreach ($files as $file) {
        @unlink($file);
      }
    }


    // Empty line processing is now handled in the batch processing above
  }

  protected function processDemoMode(array $responses, string $dir): void {
    if (is_null($this->config->get(Config::IS_DEMO_MODE))) {
      if ($responses[ProvisionType::id()] === ProvisionType::DATABASE) {
        $download_source = $responses[DatabaseDownloadSource::id()];
        $db_file = Env::get('VORTEX_DB_DIR', './.data') . DIRECTORY_SEPARATOR . Env::get('VORTEX_DB_FILE', 'db.sql');
        $has_comment = File::contains($this->dstDir . '/.env', 'Override project-specific values for demonstration purposes');

        // Enable Vortex demo mode if download source is file AND
        // there is no downloaded file present OR if there is a demo comment in
        // destination .env file.
        if ($download_source !== DatabaseDownloadSource::CONTAINER_REGISTRY) {
          if ($has_comment || !file_exists($db_file)) {
            $this->config->set(Config::IS_DEMO_MODE, TRUE);
          }
          else {
            $this->config->set(Config::IS_DEMO_MODE, FALSE);
          }
        }
        elseif ($has_comment) {
          $this->config->set(Config::IS_DEMO_MODE, TRUE);
        }
        else {
          $this->config->set(Config::IS_DEMO_MODE, FALSE);
        }
      }
      else {
        $this->config->set(Config::IS_DEMO_MODE, FALSE);
      }
    }

    if (!$this->config->get(Config::IS_DEMO_MODE)) {
      File::addTaskDirectory(function(ExtendedSplFileInfo $file_info): ExtendedSplFileInfo {
        $content = File::removeToken($file_info->getContent(), '#;< DEMO', '#;> DEMO', TRUE);
        $file_info->setContent($content);
        return $file_info;
      });
      File::runTaskDirectory($dir);
    }
  }

}

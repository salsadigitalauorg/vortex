<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Env;
use DrevOps\Installer\Utils\File;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class DatabaseDownloadSource extends AbstractHandler {

  const URL = 'url';

  const FTP = 'ftp';

  const ACQUIA = 'acquia';

  const LAGOON = 'lagoon';

  const CONTAINER_REGISTRY = 'container_registry';

  const NONE = 'none';

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    return Env::getFromDotenv('VORTEX_DB_DOWNLOAD_SOURCE', $this->dstDir);
  }

  /**
   * {@inheritdoc}
   */
  public function process(): void {
    if (!is_scalar($this->response)) {
      throw new \RuntimeException('Invalid response type.');
    }

    $type = $this->response;

    File::replaceContent($this->tmpDir . '/.env', '/VORTEX_DB_DOWNLOAD_SOURCE=.*/', 'VORTEX_DB_DOWNLOAD_SOURCE=' . $type);

    $types = [
      DatabaseDownloadSource::URL,
      DatabaseDownloadSource::FTP,
      DatabaseDownloadSource::ACQUIA,
      DatabaseDownloadSource::LAGOON,
      DatabaseDownloadSource::CONTAINER_REGISTRY,
    ];

    // Batch process all token removals for better performance
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($types, $type): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      
      foreach ($types as $t) {
        $token = 'DB_DOWNLOAD_SOURCE_' . strtoupper($t);
        if ($t === $type) {
          // Remove negated token (equivalent to removeTokenInDir with '!' prefix)
          $content = File::removeToken($content, '#;< !' . $token, '#;> !' . $token, TRUE);
        } else {
          // Remove normal token (equivalent to removeTokenInDir)
          $content = File::removeToken($content, '#;< ' . $token, '#;> ' . $token, TRUE);
        }
      }
      
      $file_info->setContent($content);
      return $file_info;
    });
    
    File::runTaskDirectory($this->tmpDir);
  }

}

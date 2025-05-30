<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Composer;
use DrevOps\Installer\Utils\Converter;
use DrevOps\Installer\Utils\File;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class MachineName extends AbstractHandler {

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    $value = Composer::getJsonValue('name', $this->dstDir . DIRECTORY_SEPARATOR . 'composer.json');

    if ($value && preg_match('/([^\/]+)\/(.+)/', (string) $value, $matches) && !empty($matches[2])) {
      return $matches[2];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function process(): void {
    if (!is_scalar($this->response)) {
      throw new \RuntimeException('Invalid response type.');
    }

    $v = (string) $this->response;
    $t = $this->tmpDir;

    // Batch process machine name replacements for better performance
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($v): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      $content = File::replaceContent($content, 'your_site', $v);
      $content = File::replaceContent($content, 'your-site', Converter::kebab($v));
      $content = File::replaceContent($content, 'YourSite', Converter::pascal($v));
      $file_info->setContent($content);
      return $file_info;
    });
    File::runTaskDirectory($t);

    File::renameInDir($t, 'your_site', $v);
  }

}

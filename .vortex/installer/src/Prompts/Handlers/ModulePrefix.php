<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Converter;
use DrevOps\Installer\Utils\File;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class ModulePrefix extends AbstractHandler {

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    $locations = [
      $this->dstDir . sprintf('/%s/modules/custom/*_core', $this->webroot),
      $this->dstDir . sprintf('/%s/sites/all/modules/custom/*_core', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/*/modules/*_core', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/*/modules/custom/*_core', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/custom/*/modules/*_core', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/custom/*/modules/custom/*_core', $this->webroot),
    ];

    $path = File::findMatchingPath($locations);

    return empty($path) ? NULL : str_replace('_core', '', basename($path));
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
    $w = $this->webroot;

    // Batch process all content replacements for better performance
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($v): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      $content = File::replaceContent($content, 'ys_core', $v . '_core');
      $content = File::replaceContent($content, 'ys_search', $v . '_search');
      $content = File::replaceContent($content, 'YsCore', Converter::pascal($v) . 'Core');
      $content = File::replaceContent($content, 'YsSearch', Converter::pascal($v) . 'Search');
      $content = File::replaceContent($content, 'YSCODE', Converter::cobol($v));
      $content = File::replaceContent($content, 'YSSEARCH', Converter::cobol($v));
      $file_info->setContent($content);
      return $file_info;
    });
    File::runTaskDirectory($t);

    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'ys_core', $v . '_core');
    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'ys_search', $v . '_search');
    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'YsCore', Converter::pascal($v) . 'Core');
  }

}

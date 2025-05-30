<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Composer;
use DrevOps\Installer\Utils\Env;
use DrevOps\Installer\Utils\File;
use AlexSkrypnyk\File\Internal\ExtendedSplFileInfo;

class Webroot extends AbstractHandler {

  const WEB = 'web';

  const DOCROOT = 'docroot';

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    $value = Env::getFromDotenv('WEBROOT', $this->dstDir);

    if (empty($value)) {
      // Try from composer.json.
      $extra = Composer::getJsonValue('extra', $this->dstDir . DIRECTORY_SEPARATOR . 'composer.json');
      if (!empty($extra)) {
        $value = $extra['drupal-scaffold']['drupal-scaffold']['locations']['web-root'] ?? NULL;
      }
    }

    return $value;
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

    if ($v === self::WEB) {
      return;
    }

    // Batch process all webroot replacements for better performance
    File::addTaskDirectory(function(ExtendedSplFileInfo $file_info) use ($v): ExtendedSplFileInfo {
      $content = $file_info->getContent();
      $content = File::replaceContent($content, sprintf('%s/', self::WEB), $v . '/');
      $content = File::replaceContent($content, sprintf('%s\/', self::WEB), $v . '\/');
      $content = File::replaceContent($content, sprintf(': %s', self::WEB), ': ' . $v);
      $content = File::replaceContent($content, sprintf('=%s', self::WEB), '=' . $v);
      $content = File::replaceContent($content, sprintf('!%s', self::WEB), '!' . $v);
      $content = File::replaceContent($content, sprintf('/\/%s\//', self::WEB), '/' . $v . '/');
      $content = File::replaceContent($content, sprintf('/\'\/%s\'/', self::WEB), "'/" . $v . "'");
      $file_info->setContent($content);
      return $file_info;
    });
    File::runTaskDirectory($t);
    rename($t . DIRECTORY_SEPARATOR . self::WEB, $t . DIRECTORY_SEPARATOR . $v);
  }

}

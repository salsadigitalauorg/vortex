<?php

declare(strict_types=1);

namespace DrevOps\Installer\Prompts\Handlers;

use DrevOps\Installer\Utils\Converter;
use DrevOps\Installer\Utils\File;

class ModulePrefix extends AbstractHandler {

  /**
   * {@inheritdoc}
   */
  public function discover(): null|string|bool|array {
    $locations = [
      $this->dstDir . sprintf('/%s/modules/custom/*_base', $this->webroot),
      $this->dstDir . sprintf('/%s/sites/all/modules/custom/*_base', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/*/modules/*_base', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/*/modules/custom/*_base', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/custom/*/modules/*_base', $this->webroot),
      $this->dstDir . sprintf('/%s/profiles/custom/*/modules/custom/*_base', $this->webroot),
    ];

    $path = File::findMatchingPath($locations);

    return empty($path) ? NULL : str_replace('_base', '', basename($path));
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

    File::replaceContentInDir($t . sprintf('/%s/modules/custom', $w), 'sw_base', $v . '_base');
    File::replaceContentInDir($t . sprintf('/%s/modules/custom', $w), 'sw_search', $v . '_search');
    File::replaceContentInDir($t . sprintf('/%s/themes/custom', $w), 'sw_base', $v . '_base');
    File::replaceContentInDir($t . '/scripts/custom', 'sw_base', $v . '_base');
    File::replaceContentInDir($t . '/scripts/custom', 'sw_search', $v . '_search');
    File::replaceContentInDir($t . sprintf('/%s/modules/custom', $w), 'SwBase', Converter::pascal($v) . 'Base');
    File::replaceContentInDir($t . sprintf('/%s/modules/custom', $w), 'SwSearch', Converter::pascal($v) . 'Search');
    File::replaceContentInDir($t, 'SWCODE', Converter::cobol($v));
    File::replaceContentInDir($t, 'SWSEARCH', Converter::cobol($v));

    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'sw_base', $v . '_base');
    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'sw_search', $v . '_search');
    File::renameInDir($t . sprintf('/%s/modules/custom', $w), 'SwBase', Converter::pascal($v) . 'Base');
  }

}

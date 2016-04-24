<?php

namespace kint;

/**
 * Class KintBootup
 *
 * @package kint
 */
class KintBootup
{
  /**
   * init
   */
  public static function initAll()
  {
    if (defined('KINT_DIR')) {
      return;
    }

    define('KINT_DIR', __DIR__ . '/');

    require KINT_DIR . 'config.default.php';

    # init settings
    if (!empty($GLOBALS['_kint_settings'])) {
      Kint::enabled($GLOBALS['_kint_settings']['enabled']);

      foreach ($GLOBALS['_kint_settings'] as $key => $val) {
        /** @noinspection PhpVariableVariableInspection */
        property_exists('Kint', $key) and Kint::$$key = $val;
      }

      unset($GLOBALS['_kint_settings'], $key, $val);
    }
  }
}
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
    define('KINT_PHP53', version_compare(PHP_VERSION, '5.3.0') >= 0);

    require KINT_DIR . '../../config.default.php';
    require KINT_DIR . 'inc/KintVariableData.php';
    require KINT_DIR . 'inc/KintParser.php';
    require KINT_DIR . 'inc/KintObject.php';
    require KINT_DIR . 'decorators/Kint_Decorators_Rich.php';
    require KINT_DIR . 'decorators/Kint_Decorators_Plain.php';

    if (is_readable(KINT_DIR . 'config.php')) {
      /** @noinspection PhpIncludeInspection */
      require_once KINT_DIR . 'config.php';
    }

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
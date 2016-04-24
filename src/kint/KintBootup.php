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
  public static function init()
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

  public function initFunctions()
  {
    if (!function_exists('d')) {
      /**
       * Alias of Kint::dump()
       *
       * @return string
       */
      function d()
      {
        if (!Kint::enabled()) {
          return '';
        }

        $_ = func_get_args();

        return call_user_func_array(array('kint\Kint', 'dump'), $_);
      }
    }

    if (!function_exists('dd')) {
      /**
       * Alias of Kint::dump()
       * [!!!] IMPORTANT: execution will halt after call to this function
       *
       * @return string
       * @deprecated
       */
      function dd()
      {
        if (!Kint::enabled()) {
          return '';
        }

        echo "<pre>Kint: dd() is being deprecated, please use ddd() instead</pre>\n";
        $_ = func_get_args();
        call_user_func_array(array('kint\Kint', 'dump'), $_);
        die;
      }
    }

    if (!function_exists('ddd')) {
      /**
       * Alias of Kint::dump()
       * [!!!] IMPORTANT: execution will halt after call to this function
       *
       * @return string
       */
      function ddd()
      {
        if (!Kint::enabled()) {
          return '';
        }

        $_ = func_get_args();
        call_user_func_array(array('kint\Kint', 'dump'), $_);
        die;
      }
    }

    if (!function_exists('s')) {
      /**
       * Alias of Kint::dump(), however the output is in plain html-escaped text and some minor visibility enhancements
       * added. If run in CLI mode, output is pure whitespace.
       *
       * To force rendering mode without auto-detecting anything:
       *
       *  Kint::enabled( Kint::MODE_PLAIN );
       *  Kint::dump( $variable );
       *
       * [!!!] IMPORTANT: execution will halt after call to this function
       *
       * @return string
       */
      function s()
      {
        $enabled = Kint::enabled();
        if (!$enabled) {
          return '';
        }

        if ($enabled === Kint::MODE_WHITESPACE) { # if already in whitespace, don't elevate to plain
          $restoreMode = Kint::MODE_WHITESPACE;
        } else {
          $restoreMode = Kint::enabled( # remove cli colors in cli mode; remove rich interface in HTML mode
              PHP_SAPI === 'cli' ? Kint::MODE_WHITESPACE : Kint::MODE_PLAIN
          );
        }

        $params = func_get_args();
        $dump = call_user_func_array(array('kint\Kint', 'dump'), $params);
        Kint::enabled($restoreMode);

        return $dump;
      }
    }

    if (!function_exists('sd')) {
      /**
       * @see s()
       *
       * [!!!] IMPORTANT: execution will halt after call to this function
       *
       * @return string
       */
      function sd()
      {
        $enabled = Kint::enabled();
        if (!$enabled) {
          return '';
        }

        if ($enabled !== Kint::MODE_WHITESPACE) {
          Kint::enabled(
              PHP_SAPI === 'cli' ? Kint::MODE_WHITESPACE : Kint::MODE_PLAIN
          );
        }

        $params = func_get_args();
        call_user_func_array(array('kint\Kint', 'dump'), $params);
        die;
      }
    }
  }
}
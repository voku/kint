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
        if (property_exists('Kint', $key)) {
          Kint::$$key = $val;
        }
      }

      unset($GLOBALS['_kint_settings'], $key, $val);
    }
  }

  public static function initFunctions()
  {
    /**
     * Alias of Kint::dump()
     *
     * @return string
     */
    function d()
    {
      return call_user_func_array(array('kint\Kint', 'dump'), func_get_args());
    }


    /**
     * Alias of Kint::dump()
     * [!!!] IMPORTANT: execution will halt after call to this function
     *
     * @return string
     */
    function dd()
    {
      if (!Kint::enabled()) {
        return '';
      }

      call_user_func_array(array('kint\Kint', 'dump'), func_get_args());
      exit();
    }

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

      call_user_func_array(array('kint\Kint', 'dump'), func_get_args());
      exit();
    }

    /**
     * Alias of Kint::dump(), however the output is delayed until the end of the script
     *
     * @see d();
     *
     * @return string
     */
    function de()
    {
      $stash = Kint::settings();
      Kint::$delayedMode = true;
      $out = call_user_func_array(array('kint\Kint', 'dump'), func_get_args());
      Kint::settings($stash);

      return $out;
    }

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
      if (!Kint::enabled()) {
        return '';
      }

      $stash = Kint::settings();

      if (Kint::enabled() !== Kint::MODE_WHITESPACE) {
        Kint::enabled(Kint::MODE_PLAIN);
        if (PHP_SAPI === 'cli' && Kint::$cliDetection === true) {
          Kint::enabled(Kint::MODE_CLI);
        }
      }

      $out = call_user_func_array(array('kint\Kint', 'dump'), func_get_args());

      Kint::settings($stash);

      return $out;
    }

    /**
     * @see s()
     *
     * [!!!] IMPORTANT: execution will halt after call to this function
     *
     * @return string
     */
    function sd()
    {
      if (!Kint::enabled()) {
        return '';
      }

      if (Kint::enabled() !== Kint::MODE_WHITESPACE) {
        Kint::enabled(Kint::MODE_PLAIN);
        if (PHP_SAPI === 'cli' && Kint::$cliDetection === true) {
          Kint::enabled(Kint::MODE_CLI);
        }
      }

      call_user_func_array(array('kint\Kint', 'dump'), func_get_args());
      exit();
    }

    /**
     * @see s()
     * @see de()
     *
     * @return string
     */
    function se()
    {
      if (!Kint::enabled()) {
        return '';
      }

      $stash = Kint::settings();

      Kint::$delayedMode = true;

      if (Kint::enabled() !== Kint::MODE_WHITESPACE) {
        Kint::enabled(Kint::MODE_PLAIN);
        if (PHP_SAPI === 'cli' && Kint::$cliDetection === true) {
          Kint::enabled(Kint::MODE_CLI);
        }
      }

      $out = call_user_func_array(array('kint\Kint', 'dump'), func_get_args());

      Kint::settings($stash);

      return $out;
    }

    /**
     * Alias of Kint::dump(), however the output is dumped to the javascript console and
     * added to the global array `kintDump`. If run in CLI mode, output is pure whitespace.
     *
     * To force rendering mode without autodetecting anything:
     *
     *  Kint::enabled( Kint::MODE_JS );
     *  Kint::dump( $variable );
     *
     * @return string
     */
    function j()
    {
      if (!Kint::enabled()) {
        return '';
      }

      $stash = Kint::settings();

      Kint::enabled(
          PHP_SAPI === 'cli' && Kint::$cliDetection === true ? Kint::MODE_CLI : Kint::MODE_JS
      );

      $out = call_user_func_array(array('kint\Kint', 'dump'), func_get_args());

      Kint::settings($stash);

      return $out;
    }

    /**
     * @see j()
     *
     * [!!!] IMPORTANT: execution will halt after call to this function
     *
     * @return string
     */
    function jd()
    {
      if (!Kint::enabled()) {
        return '';
      }

      Kint::enabled(
          PHP_SAPI === 'cli' && Kint::$cliDetection === true ? Kint::MODE_CLI : Kint::MODE_JS
      );

      call_user_func_array(array('kint\Kint', 'dump'), func_get_args());

      exit();
    }

    /**
     * @see j()
     * @see de()
     *
     * @return string
     */
    function je()
    {
      if (!Kint::enabled()) {
        return '';
      }

      $stash = Kint::settings();

      Kint::$delayedMode = true;

      Kint::enabled(
          PHP_SAPI === 'cli' && Kint::$cliDetection === true ? Kint::MODE_CLI : Kint::MODE_JS
      );

      $out = call_user_func_array(array('kint\Kint', 'dump'), func_get_args());

      Kint::settings($stash);

      return $out;
    }
  }

}

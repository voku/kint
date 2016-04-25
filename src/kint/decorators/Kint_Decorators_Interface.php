<?php

namespace kint\decorators;

use kint\inc\KintVariableData;

/**
 * Interface Kint_Decorators_Interface
 *
 * @package kint\decorators
 */
interface Kint_Decorators_Interface
{
  public static function init();

  public static function wrapStart();

  public static function decorateTrace();

  /**
   * @param $callee
   * @param $miniTrace
   * @param $prevCaller
   *
   * @return mixed
   */
  public static function wrapEnd($callee, $miniTrace, $prevCaller);

  /**
   * @param KintVariableData $kintVar
   * @param int              $level
   *
   * @return mixed
   */
  public static function decorate(KintVariableData $kintVar, $level = 0);
}

<?php

namespace kint\decorators;

use kint\inc\KintVariableData;

/**
 * Interface Kint_Decorators
 *
 * @package kint\decorators
 */
abstract class Kint_Decorators
{
  /**
   * @var bool
   */
  public static $firstRun = true;

  /** @noinspection PhpAbstractStaticMethodInspection */
  public abstract static function init();

  /** @noinspection PhpAbstractStaticMethodInspection */
  public abstract static function wrapStart();

  /** @noinspection PhpAbstractStaticMethodInspection */
  public abstract static function decorateTrace();

  /** @noinspection PhpAbstractStaticMethodInspection */
  /**
   * @param $callee
   * @param $miniTrace
   * @param $prevCaller
   *
   * @return mixed
   */
  public abstract static function wrapEnd($callee, $miniTrace, $prevCaller);

  /** @noinspection PhpAbstractStaticMethodInspection */
  /**
   * @param KintVariableData $kintVar
   * @param int              $level
   *
   * @return mixed
   */
  public abstract static function decorate(KintVariableData $kintVar, $level = 0);
}
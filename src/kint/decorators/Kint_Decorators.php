<?php

namespace kint\decorators;

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
  public abstract static function decorate();

  /** @noinspection PhpAbstractStaticMethodInspection */
  public abstract static function wrapEnd();
}
<?php

namespace kint\inc;

use voku\helper\UTF8;

/**
 * Class KintVariableData
 */
class KintVariableData
{
  /**
   * @var string
   */
  public $type;

  /**
   * @var string
   */
  public $access;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $operator;

  /**
   * @var int
   */
  public $size;

  /**
   * @var kintVariableData[] array of kintVariableData objects or strings; displayed collapsed, each element from
   * the array is a separate possible representation of the dumped var
   */
  public $extendedValue;

  /**
   * @var string inline value
   */
  public $value;

  /**
   * @var kintVariableData[] array of alternative representations for same variable, don't use in custom parsers
   */
  public $_alternatives;

  /**
   * @param string $value
   *
   * @return string
   */
  protected static function _detectEncoding(&$value)
  {
    return UTF8::str_detect_encoding($value);
  }

  /**
   * returns whether the array:
   *  1) is numeric and
   *  2) in sequence starting from zero
   *
   * @param array $array
   *
   * @return bool
   */
  protected static function _isSequential(array &$array)
  {
    return array_keys($array) === range(0, count($array) - 1);
  }

  /**
   * Get part of string
   *
   * @param string $string   <p>
   *                         The string being checked.
   *                         </p>
   * @param int    $start    <p>
   *                         The first position used in str.
   *                         </p>
   * @param int    $end      [optional] <p>
   *                         The maximum length of the returned string.
   *                         </p>
   * @param string $encoding [optional] &mbstring.encoding.parameter;
   *
   * @return string
   */
  protected static function _substr($string, $start, $end = null, $encoding = null)
  {
    if (!$encoding) {
      $encoding = self::_detectEncoding($string);
    }

    return mb_substr($string, $start, $end, $encoding);
  }
}

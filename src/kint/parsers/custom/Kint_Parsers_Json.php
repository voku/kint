<?php

namespace kint\parsers\custom;

use kint\inc\KintParser;

/**
 * Class Kint_Parsers_Json
 */
class Kint_Parsers_Json extends KintParser
{
  /**
   * @param mixed $variable
   *
   * @return bool
   */
  protected function _parse(&$variable)
  {
    if (
        is_object($variable)
        || is_array($variable)
        || (string)$variable !== $variable
        || !isset($variable[0])
        || ($variable[0] !== '{' && $variable[0] !== '[')
        || ($json = json_decode($variable, true)) === null
    ) {
      return false;
    }

    $val = (array)$json;
    if (empty($val)) {
      return false;
    }

    $this->value = KintParser::factory($val)->extendedValue;
    $this->type = 'JSON';

    return true;
  }
}

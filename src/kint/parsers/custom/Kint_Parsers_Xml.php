<?php

namespace kint\parsers\custom;

use kint\inc\KintParser;

/**
 * Class Kint_Parsers_Xml
 */
class Kint_Parsers_Xml extends KintParser
{
  /**
   * @param mixed $variable
   *
   * @return bool
   */
  protected function _parse(&$variable)
  {
    try {
      if (
          (string)$variable === $variable
          &&
          0 === strpos($variable, '<?xml')
      ) {
        $e = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($variable);
        libxml_use_internal_errors($e);
        if (empty($xml)) {
          return false;
        }
      } else {
        return false;
      }
    } catch (\Exception $e) {
      return false;
    }

    $this->value = KintParser::factory($xml)->extendedValue;
    $this->type = 'XML';

    return true;
  }
}

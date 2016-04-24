<?php

namespace kint\parsers\custom;

use kint\inc\KintParser;

/**
 * Class Kint_Parsers_ObjectIterateable
 */
class Kint_Parsers_ObjectIterateable extends KintParser
{
  /**
   * @param mixed $variable
   *
   * @return bool
   */
  protected function _parse(&$variable)
  {
    if (
        !is_object($variable)
        || !$variable instanceof \Traversable
        || stripos(get_class($variable), 'zend') !== false // zf2 PDO wrapper does not play nice
    ) {
      return false;
    }


    $arrayCopy = iterator_to_array($variable, true);

    if ($arrayCopy === false) {
      return false;
    }

    $this->value = KintParser::factory($arrayCopy)->extendedValue;
    $this->type = 'Iterator contents';
    $this->size = count($arrayCopy);
    
    return true;
  }
}
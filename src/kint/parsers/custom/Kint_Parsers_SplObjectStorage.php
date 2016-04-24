<?php

namespace kint\parsers\custom;

use kint\inc\KintParser;

/**
 * Class Kint_Parsers_SplObjectStorage
 */
class Kint_Parsers_SplObjectStorage extends KintParser
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
        ||
        !$variable instanceof \SplObjectStorage
    ) {
      return false;
    }

    /** @var $variable \SplObjectStorage */

    $count = $variable->count();
    if ($count === 0) {
      return false;
    }

    $variable->rewind();
    while ($variable->valid()) {
      $current = $variable->current();
      $this->value[] = KintParser::factory($current);
      $variable->next();
    }

    $this->type = 'Storage contents';
    $this->size = $count;
    
    return true;
  }
}
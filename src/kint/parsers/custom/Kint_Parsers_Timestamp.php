<?php

namespace kint\parsers\custom;

use kint\inc\KintParser;

/**
 * Class Kint_Parsers_Timestamp
 */
class Kint_Parsers_Timestamp extends KintParser
{
  /**
   * @param $variable
   *
   * @return bool
   */
  private static function _fits(&$variable)
  {
    if (
        is_object($variable)
        ||
        is_array($variable)
        ||
        (
            (string)$variable !== $variable
            &&
            (int)$variable !== $variable
        )
    ) {
      return false;
    }

    $len = strlen((int)$variable);

    return
        (
            $len === 9
            ||
            $len === 10 # a little naive
            ||
            (
                $len === 13
                &&
                substr($variable, -3) === '000'
            ) # also handles javascript micro timestamps
        )
        &&
        (string)(int)$variable == $variable;
  }

  /**
   * @param mixed $variable
   *
   * @return bool
   */
  protected function _parse(&$variable)
  {
    if (!self::_fits($variable)) {
      return false;
    }

    if (strlen($variable) === 13) {
      $variable = substr($variable, 0, -3);
    }

    $this->type = 'timestamp';
    # avoid dreaded "Timezone must be set" error
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    $this->value = @date('Y-m-d H:i:s', $variable);
  }
}
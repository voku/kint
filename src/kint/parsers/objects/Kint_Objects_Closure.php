<?php

namespace kint\parsers\objects;

use kint\inc\KintObject;
use kint\Kint;

/**
 * Class Kint_Objects_Closure
 */
class Kint_Objects_Closure extends KintObject
{
  /**
   * @param $variable
   *
   * @return array|bool
   */
  public function parse(&$variable)
  {
    if (!$variable instanceof \Closure) {
      return false;
    }

    $this->name = 'Closure';
    $reflection = new \ReflectionFunction($variable);
    $ret = array(
        'Parameters' => array(),
    );

    $val = $reflection->getParameters();
    if ($val) {
      foreach ($val as $parameter) {
        // todo http://php.net/manual/en/class.reflectionparameter.php
        $ret['Parameters'][] = $parameter->name;
      }

    }

    $val = $reflection->getStaticVariables();
    if ($val) {
      $ret['Uses'] = $val;
    }

    if (
        method_exists($reflection, 'getClousureThis')
        &&
        $val = $reflection->getClosureThis()
    ) {
      $ret['Uses']['$this'] = $val;
    }

    $val = $reflection->getFileName();
    if ($val) {
      $this->value = Kint::shortenPath($val) . ':' . $reflection->getStartLine();
    }

    return $ret;
  }
}

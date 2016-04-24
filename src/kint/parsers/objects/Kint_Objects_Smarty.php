<?php

namespace kint\parsers\objects;

use kint\inc\KintObject;

/**
 * Class Kint_Objects_Smarty
 */
class Kint_Objects_Smarty extends KintObject
{
  /**
   * @param $variable
   *
   * @return array|bool
   */
  public function parse(&$variable)
  {
    /** @noinspection PhpUndefinedClassInspection */
    if (
        !defined('Smarty::SMARTY_VERSION') # lower than 3.x
        ||
        !$variable instanceof Smarty
    ) {
      return false;
    }

    /** @noinspection PhpUndefinedClassInspection */
    $this->name = 'object Smarty (v' . substr(Smarty::SMARTY_VERSION, 7) . ')'; # trim 'Smarty-'

    $assigned = $globalAssigns = array();
    /** @noinspection PhpUndefinedFieldInspection */
    foreach ($variable->tpl_vars as $name => $var) {
      $assigned[$name] = $var->value;
    }

    /** @noinspection PhpUndefinedClassInspection */
    foreach (Smarty::$global_tpl_vars as $name => $var) {
      if ($name === 'SCRIPT_NAME') {
        continue;
      }

      $globalAssigns[$name] = $var->value;
    }

    /** @noinspection PhpUndefinedMethodInspection */
    return array(
        'Assigned'          => $assigned,
        'Assigned globally' => $globalAssigns,
        'Configuration'     => array(
            'Compiled files stored in' => isset($variable->compile_dir)
                ? $variable->compile_dir
                : $variable->getCompileDir(),
        ),
    );

  }
}

<?php

use kint\Kint;

/**
 * Class KintTest
 */
class KintTest extends PHPUnit_Framework_TestCase
{
  public function testCliMode()
  {
    $var = array(
      1,
      1.692,
      'lall',
      new \stdClass(),
      array('1', 1, '1'),
      null,
      'Iñtërnâtiônàlizætiøn'
    );

    ob_start();
    $output = '';
    Kint::enabled(Kint::MODE_CLI);
    $result = !Kint::dump($var);
    if ($result === true) {
      $output = ob_get_contents();
    }
    ob_end_clean();

    $expected = '┌─────────────────────────────────────────────────────────────────────────────┐
│                                   literal                                   │
└─────────────────────────────────────────────────────────────────────────────┘
array (7) [
    integer 1
    float 1.692
    string [ASCII] (4) "lall"
    stdClass (0)
    array (3) [
        string [ASCII] (1) "1"
        integer 1
        string [ASCII] (1) "1"
    ]
    NULL
    string [UTF-8] (20) "Iñtërnâtiônàlizætiøn"
]
════════════════════════════════════════════════════════════════════════════════
';

    self::assertSame($expected, $output);
  }

  public function testRichMode()
  {
    $var = array(
        1,
        1.692,
        'lall',
        new \stdClass(),
        array('1', 1, '1'),
        null,
        'Iñtërnâtiônàlizætiøn'
    );

    ob_start();
    $output = '';
    Kint::enabled(Kint::MODE_RICH);
    $result = !Kint::dump($var);
    if ($result === true) {
      $output = ob_get_contents();
    }
    ob_end_clean();

    $expected = '<div class="kint "><dl><dt class="kint-parent"><span class="kint-popup-trigger" title="Open in new window">&rarr;</span><nav></nav><var>array</var>(7)';

    self::assertContains($expected, $output);
  }
}

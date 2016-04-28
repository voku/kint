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

    $expected = '┌──────────────────────────────────────────────────────────────────────────────┐
│                                   literal                                    │
└──────────────────────────────────────────────────────────────────────────────┘
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

    self::assertEquals($expected, $output);
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

    $expected = '<div class="kint"><dl><dt class="kint-parent"><span class="kint-popup-trigger" title="Open in new window">&rarr;</span><nav></nav><var>array</var>(7) </dt><dd><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>integer</var>1</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[0]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>float</var>1.692</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[1]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>string [ASCII]</var>(4) "lall"</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[2]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>stdClass</var>(0) </dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[3]</dt></dl><dl><dt class="kint-parent"><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><span class="kint-popup-trigger" title="Open in new window">&rarr;</span><nav></nav><var>array</var>(3) </dt><dd><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>string [ASCII]</var>(1) "1"</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[4][0]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>integer</var>1</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[4][1]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>string [ASCII]</var>(1) "1"</dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[4][2]</dt></dl></dd><dt class="access-path"><div class="access-icon">&rlarr;</div>[4]</dt></dl><dl><dt><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><var>NULL</var></dt><dt class="access-path"><div class="access-icon">&rlarr;</div>[5]</dt></dl><dl><dt class="kint-parent"><span class="kint-access-path-trigger" title="Show access path">&rlarr;</span><span class="kint-popup-trigger" title="Open in new window">&rarr;</span><nav></nav><var>string [UTF-8]</var>(20) "I&#241;t&#235;rn&#226;ti&#244;n&#224;liz&#230;ti&#248;n"</dt><dd><pre>I&amp;#241;t&amp;#235;rn&amp;#226;ti&amp;#244;n&amp;#224;liz&amp;#230;ti&amp;#248;n</pre></dd><dt class="access-path"><div class="access-icon">&rlarr;</div>[6]</dt></dl></dd></dl></div>';

    self::assertEquals($expected, $output);
  }
}

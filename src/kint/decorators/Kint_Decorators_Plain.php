<?php

namespace kint\decorators;

use kint\inc\KintParser;
use kint\inc\KintVariableData;
use kint\Kint;

/**
 * Class Kint_Decorators_Plain
 */
class Kint_Decorators_Plain extends Kint_Decorators
{
  /**
   * @var bool
   */
  private static $_enableColors;

  /**
   * @var array
   */
  private static $_utfSymbols = array(
      '┌',
      '═',
      '┐',
      '│',
      '└',
      '─',
      '┘',
  );

  /**
   * @var array
   */
  private static $_htmlSymbols = array(
      "&#9484;",
      "&#9604;",
      "&#9488;",
      "&#9474;",
      "&#9492;",
      "&#9472;",
      "&#9496;",
  );

  /**
   * @param $callee
   *
   * @return string
   */
  private static function _buildCalleeString($callee)
  {
    if (Kint::enabled() === Kint::MODE_CLI) { // todo win/nix ?
      return "{$callee['file']}:{$callee['line']}";
    }

    $url = Kint::getIdeLink($callee['file'], $callee['line']);
    $shortenedName = Kint::shortenPath($callee['file']) . ':' . $callee['line'];

    if (Kint::enabled() === Kint::MODE_PLAIN) {
      if (strpos($url, 'http://') === 0) {
        $calleeInfo = "<a href=\"#\"onclick=\""
                      . "X=new XMLHttpRequest;"
                      . "X.open('GET','{$url}');"
                      . "X.send();"
                      . "return!1\">{$shortenedName}</a>";
      } else {
        $calleeInfo = "<a href=\"{$url}\">{$shortenedName}</a>";
      }
    } else {
      $calleeInfo = $shortenedName;
    }

    return $calleeInfo;
  }

  /**
   * @param string   $char
   * @param null|int $repeat
   *
   * @return string
   */
  private static function _char($char, $repeat = null)
  {
    switch (Kint::enabled()) {
      case Kint::MODE_PLAIN:
        $char = self::$_htmlSymbols[array_search($char, self::$_utfSymbols, true)];
        break;
      case Kint::MODE_CLI:
        break;
      case Kint::MODE_WHITESPACE:
      default:
        break;
    }

    return $repeat ? str_repeat($char, $repeat) : (string)$char;
  }

  /**
   * @param string $text
   * @param string $type "value", "type", "title"
   * @param bool   $nlAfter
   *
   * @return string
   */
  private static function _colorize($text, $type, $nlAfter = true)
  {
    $nlAfterChar = $nlAfter ? PHP_EOL : '';

    switch (Kint::enabled()) {
      case Kint::MODE_PLAIN:
        if (!self::$_enableColors) {
          return $text . $nlAfterChar;
        }

        switch ($type) {
          case 'value':
            $text = "<i>{$text}</i>";
            break;
          case 'type':
            $text = "<b>{$text}</b>";
            break;
          case 'title':
            $text = "<u>{$text}</u>";
            break;
        }

        return $text . $nlAfterChar;
        break;
      case Kint::MODE_CLI:
        if (!self::$_enableColors) {
          return $text . $nlAfterChar;
        }

        $optionsMap = array(
            'title' => "\x1b[36m", # cyan
            'type'  => "\x1b[35;1m", # magenta bold
            'value' => "\x1b[32m", # green
        );

        return $optionsMap[$type] . $text . "\x1b[0m" . $nlAfterChar;
        break;
      case Kint::MODE_WHITESPACE:
      default:
        return $text . $nlAfterChar;
        break;
    }
  }

  /**
   * @param KintVariableData $kintVar
   *
   * @return string
   */
  private static function _drawHeader(KintVariableData $kintVar)
  {
    $output = '';

    if ($kintVar->access) {
      $output .= ' ' . $kintVar->access;
    }

    if ($kintVar->name !== null && $kintVar->name !== '') {
      $output .= ' ' . KintParser::escape($kintVar->name);
    }

    if ($kintVar->operator) {
      $output .= ' ' . $kintVar->operator;
    }

    $output .= ' ' . self::_colorize($kintVar->type, 'type', false);

    if ($kintVar->size !== null) {
      $output .= ' (' . $kintVar->size . ')';
    }


    if ($kintVar->value !== null && $kintVar->value !== '') {
      $output .= ' ' . self::_colorize(
              $kintVar->value, # escape shell
              'value',
              false
          );
    }

    return ltrim($output);
  }

  /**
   * @param $text
   *
   * @return string
   */
  private static function _title($text)
  {
    $escaped = KintParser::escape($text);
    $lengthDifference = strlen($escaped) - strlen($text);

    return
        self::_colorize(
            self::_char('┌') . self::_char('─', 78) . self::_char('┐') . PHP_EOL
            . self::_char('│'),
            'title',
            false
        )

        . self::_colorize(str_pad($escaped, 78 + $lengthDifference, ' ', STR_PAD_BOTH), 'title', false)

        . self::_colorize(
            self::_char('│') . PHP_EOL
            . self::_char('└') . self::_char('─', 78) . self::_char('┘'),
            'title'
        );
  }

  /**
   * @param KintVariableData $kintVar
   * @param int              $level
   *
   * @return string
   */
  public static function decorate(KintVariableData $kintVar, $level = 0)
  {
    $output = '';
    if ($level === 0) {
      $name = $kintVar->name ?: 'literal';
      $kintVar->name = null;

      $output .= self::_title($name);
    }


    $space = str_repeat($s = '    ', $level);
    $output .= $space . self::_drawHeader($kintVar);


    if ($kintVar->extendedValue !== null) {
      $output .= ' ' . ($kintVar->type === 'array' ? '[' : '(') . PHP_EOL;


      if (is_array($kintVar->extendedValue)) {
        foreach ($kintVar->extendedValue as $v) {
          $output .= self::decorate($v, $level + 1);
        }
      } elseif (is_string($kintVar->extendedValue)) {
        /** @noinspection PhpToStringImplementationInspection */
        $output .= $space . $s . $kintVar->extendedValue . PHP_EOL; # "depth too great" or similar
      } else {
        /** @noinspection PhpParamsInspection */
        $output .= self::decorate($kintVar->extendedValue, $level + 1); // it's kintVariableData
      }
      $output .= $space . ($kintVar->type === 'array' ? ']' : ')') . PHP_EOL;
    } else {
      $output .= PHP_EOL;
    }

    return $output;
  }

  /**
   * @param array $traceData
   *
   * @return string
   */
  public static function decorateTrace(array $traceData = array())
  {
    $output = self::_title('TRACE');
    $lastStep = count($traceData);
    foreach ($traceData as $stepNo => $step) {
      $title = str_pad(++$stepNo . ': ', 4, ' ');

      $title .= self::_colorize(
          (isset($step['file']) ? self::_buildCalleeString($step) : 'PHP internal call'),
          'title'
      );

      if (!empty($step['function'])) {
        $title .= '    ' . $step['function'];
        if (isset($step['args'])) {
          $title .= '(';
          if (empty($step['args'])) {
            $title .= ')';
          }

          $title .= PHP_EOL;
        }
      }

      $output .= $title;

      if (!empty($step['args'])) {
        $appendDollar = $step['function'] === '{closure}' ? '' : '$';

        $i = 0;
        foreach ($step['args'] as $name => $argument) {
          $argument = KintParser::factory(
              $argument,
              $name ? $appendDollar . $name : '#' . ++$i
          );
          $argument->operator = $name ? ' =' : ':';
          $maxLevels = Kint::$maxLevels;
          if ($maxLevels) {
            Kint::$maxLevels = $maxLevels + 2;
          }
          $output .= self::decorate($argument, 2);
          if ($maxLevels) {
            Kint::$maxLevels = $maxLevels;
          }
        }
        $output .= '    )' . PHP_EOL;
      }

      if (!empty($step['object'])) {
        $output .= self::_colorize(
            '    ' . self::_char('─', 27) . ' Callee object ' . self::_char('─', 34),
            'title'
        );

        $maxLevels = Kint::$maxLevels;
        if ($maxLevels) {
          # in cli the terminal window is filled too quickly to display huge objects
          Kint::$maxLevels = Kint::enabled() === Kint::MODE_CLI
              ? 1
              : $maxLevels + 1;
        }
        $output .= self::decorate(KintParser::factory($step['object']), 1);
        if ($maxLevels) {
          Kint::$maxLevels = $maxLevels;
        }
      }

      if ($stepNo !== $lastStep) {
        $output .= self::_colorize(self::_char('─', 80), 'title');
      }
    }

    return $output;
  }

  /**
   * init
   *
   * @return string
   */
  public static function init()
  {
    self::$_enableColors =
        Kint::$cliColors
        && (DIRECTORY_SEPARATOR === '/' || getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON');

    return Kint::enabled() === Kint::MODE_PLAIN
        ? '<style>.-kint i{color:#d00;font-style:normal}.-kint u{color:#030;text-decoration:none;font-weight:bold}</style>'
        : '';
  }

  /**
   * @param $callee
   * @param $miniTrace
   * @param $prevCaller
   *
   * @return string
   */
  public static function wrapEnd($callee, $miniTrace, $prevCaller)
  {
    $lastLine = self::_colorize(self::_char("═", 80), 'title');
    $lastChar = Kint::enabled() === Kint::MODE_PLAIN ? '</pre>' : '';

    if (!Kint::$displayCalledFrom) {
      return $lastLine . $lastChar;
    }

    return $lastLine . self::_colorize('Called from ' . self::_buildCalleeString($callee), 'title') . $lastChar;
  }

  /**
   * @return string
   */
  public static function wrapStart()
  {
    if (Kint::enabled() === Kint::MODE_PLAIN) {
      return '<pre class="-kint">';
    }

    return '';
  }
}

<?php

namespace kint;

use kint\decorators\Kint_Decorators;
use kint\inc\KintParser;

/**
 * Class Kint
 *
 * @package kint
 */
class Kint
{
  // these are all public and 1:1 config array keys so you can switch them easily

  const MODE_CLI = 'c'; # stores mode and active statuses

  const MODE_PLAIN = 'p';

  const MODE_JS = 'j';

  const MODE_RICH = 'r';

  const MODE_WHITESPACE = 'w';

  public static $delayedMode;

  public static $returnOutput;

  public static $fileLinkFormat;

  public static $displayCalledFrom;

  public static $maxStrLength;

  public static $appRootDirs;

  public static $maxLevels;

  public static $theme;

  public static $expandedByDefault;

  public static $cliDetection;

  public static $cliColors;

  public static $aliases = array(
      'methods'   => array(
          array('kint\Kint', 'dump'),
          array('kint\Kint', 'trace'),
      ),
      'functions' => array(
          'kint\d',
          'kint\dd',
          'kint\ddd',
          'kint\de',
          'kint\s',
          'kint\sd',
          'kint\se',
          'kint\j',
          'kint\jd',
          'kint\je',
      ),
  );

  private static $_enabledMode;

  /**
   * returns parameter names that the function was passed, as well as any predefined symbols before function
   * call (modifiers)
   *
   * @param array $trace
   *
   * @return array( $parameters, $modifier, $callee, $previousCaller )
   */
  private static function _getCalleeInfo($trace)
  {
    $previousCaller = array();
    $miniTrace = array();
    $prevStep = array();

    # go from back of trace to find first occurrence of call to Kint or its wrappers
    /** @noinspection PhpAssignmentInConditionInspection */
    while ($step = array_pop($trace)) {

      if (self::_stepIsInternal($step)) {
        $previousCaller = $prevStep;
        break;
      } elseif (isset($step['file'], $step['line'])) {
        unset($step['object'], $step['args']);
        array_unshift($miniTrace, $step);
      }

      $prevStep = $step;
    }
    $callee = $step;

    if (
        !isset($callee['file'])
        ||
        !is_readable($callee['file'])
    ) {
      return array(null, null, $callee, $previousCaller, $miniTrace);
    }

    # open the file and read it up to the position where the function call expression ended
    $filePointer = fopen($callee['file'], 'r');
    $line = 0;
    $source = '';
    while (($row = fgets($filePointer)) !== false) {
      if (++$line > $callee['line']) {
        break;
      }
      $source .= $row;
    }
    fclose($filePointer);
    $source = self::_removeAllButCode($source);

    if (empty($callee['class'])) {
      $codePattern = $callee['function'];
    } else {
      if ($callee['type'] === '::') {
        $codePattern = $callee['class'] . "\x07*" . $callee['type'] . "\x07*" . $callee['function'];
      } else {
        $codePattern = ".*\x07*" . $callee['type'] . "\x07*" . $callee['function'];
      }
    }

    if ($codePattern) {
      // fix namespace for the regex
      $codePattern = str_replace('\\', '\\\\', $codePattern);
    }

    // TODO: if more than one call in one line - not possible to determine variable names
    // TODO: does not recognize string concat
    # get the position of the last call to the function
    preg_match_all(
        "
            [
            # beginning of statement
            [\x07{(]

            # search for modifiers (group 1)
            ([-+!@~]*)?

            # spaces
            \x07*

            # check if output is assigned to a variable (group 2) todo: does not detect concat
            (
                \\$[a-z0-9_]+ # variable
                \x07*\\.?=\x07*  # assignment
            )?

            # possibly a namespace symbol
            \\\\?

            # spaces again
            \x07*

            # main call to Kint
            ({$codePattern})

            # spaces everywhere
            \x07*

            # find the character where kint's opening bracket resides (group 3)
            (\\()

            ]ix",
        $source,
        $matches,
        PREG_OFFSET_CAPTURE
    );

    $modifiers = end($matches[1]);
    $assignment = end($matches[2]);
    $callToKint = end($matches[3]);
    $bracket = end($matches[4]);

    if (empty($callToKint)) {
      # if a wrapper is mis-configured, don't display the whole file as variable name
      return array(array(), $modifiers, $callee, $previousCaller, $miniTrace);
    }

    $modifiers = $modifiers[0];
    if ($assignment[1] !== -1) {
      $modifiers .= '@';
    }

    $paramsString = preg_replace("[\x07+]", ' ', substr($source, $bracket[1] + 1));
    # we now have a string like this:
    # <parameters passed>); <the rest of the last read line>

    # remove everything in brackets and quotes, we don't need nested statements nor literal strings which would
    # only complicate separating individual arguments
    $c = strlen($paramsString);
    $inString = $escaped = $openedBracket = $closingBracket = false;
    $i = 0;
    $inBrackets = 0;
    $openedBrackets = array();

    while ($i < $c) {
      $letter = $paramsString[$i];

      if (!$inString) {
        if ($letter === '\'' || $letter === '"') {
          $inString = $letter;
        } elseif ($letter === '(' || $letter === '[') {
          $inBrackets++;
          $openedBrackets[] = $openedBracket = $letter;
          $closingBracket = $openedBracket === '(' ? ')' : ']';
        } elseif ($inBrackets && $letter === $closingBracket) {
          $inBrackets--;
          array_pop($openedBrackets);
          $openedBracket = end($openedBrackets);
          $closingBracket = $openedBracket === '(' ? ')' : ']';
        } elseif (!$inBrackets && $letter === ')') {
          $paramsString = substr($paramsString, 0, $i);
          break;
        }
      } elseif ($letter === $inString && !$escaped) {
        $inString = false;
      }

      # replace whatever was inside quotes or brackets with untypeable characters, we don't
      # need that info. We'll later replace the whole string with '...'
      if (
          $inBrackets > 0
          &&
          (
              $inBrackets > 1
              ||
              $letter !== $openedBracket
          )
      ) {
        $paramsString[$i] = "\x07";
      }

      if (
          $inString
          &&
          (
              $letter !== $inString
              ||
              $escaped
          )
      ) {
        $paramsString[$i] = "\x07";
      }

      $escaped = !$escaped && ($letter === '\\');
      $i++;
    }

    # by now we have an un-nested arguments list, lets make it to an array for processing further
    $arguments = explode(',', preg_replace("[\x07+]", '...', $paramsString));

    # test each argument whether it was passed literary or was it an expression or a variable name
    $parameters = array();
    $blacklist = array('null', 'true', 'false', 'array(...)', 'array()', '"..."', '[...]', 'b"..."',);
    foreach ($arguments as $argument) {
      $argument = trim($argument);

      if (
          is_numeric($argument)
          ||
          in_array(str_replace("'", '"', strtolower($argument)), $blacklist, true)
      ) {
        $parameters[] = null;
      } else {
        $parameters[] = $argument;
      }
    }

    return array($parameters, $modifiers, $callee, $previousCaller, $miniTrace);
  }

  /**
   * @param array $data
   *
   * @return array|false
   */
  private static function _parseTrace(array $data)
  {
    $trace = array();
    $traceFields = array('file', 'line', 'args', 'class');
    $fileFound = false; # file element must exist in one of the steps

    # validate whether a trace was indeed passed
    /** @noinspection PhpAssignmentInConditionInspection */
    while ($step = array_pop($data)) {
      if (!is_array($step) || !isset($step['function'])) {
        return false;
      }
      if (!$fileFound && isset($step['file']) && file_exists($step['file'])) {
        $fileFound = true;
      }

      $valid = false;
      foreach ($traceFields as $element) {
        if (isset($step[$element])) {
          $valid = true;
          break;
        }
      }
      if (!$valid) {
        return false;
      }

      if (self::_stepIsInternal($step)) {
        $step = array(
            'file'     => $step['file'],
            'line'     => $step['line'],
            'function' => '',
        );
        array_unshift($trace, $step);
        break;
      }

      $step['index'] = count($data) - 1;

      if ($step['function'] !== 'spl_autoload_call') { # meaningless
        array_unshift($trace, $step);
      }
    }
    if (!$fileFound) {
      return false;
    }

    $output = array();
    foreach ($trace as $step) {
      if (isset($step['file'])) {
        $file = $step['file'];

        if (isset($step['line'])) {
          $line = $step['line'];
          # include the source of this step
          if (self::enabled() === self::MODE_RICH) {
            $source = self::_showSource($file, $line);
          }
        }
      }

      $function = $step['function'];

      if (in_array($function, array('include', 'include_once', 'require', 'require_once'), true)) {
        if (empty($step['args'])) {
          # no arguments
          $args = array();
        } else {
          # sanitize the included file path
          $args = array('file' => self::shortenPath($step['args'][0]));
        }
      } elseif (isset($step['args'])) {
        if (empty($step['class']) && !function_exists($function)) {
          # introspection on closures or language constructs in a stack trace is impossible before PHP 5.3
          $params = null;
        } else {
          try {
            if (isset($step['class'])) {
              if (method_exists($step['class'], $function)) {
                $reflection = new \ReflectionMethod($step['class'], $function);
              } elseif (isset($step['type']) && $step['type'] === '::') {
                $reflection = new \ReflectionMethod($step['class'], '__callStatic');
              } else {
                $reflection = new \ReflectionMethod($step['class'], '__call');
              }
            } else {
              $reflection = new \ReflectionFunction($function);
            }

            # get the function parameters
            $params = $reflection->getParameters();
          } catch (\Exception $e) { # avoid various PHP version incompatibilities
            $params = null;
          }
        }

        $args = array();
        foreach ($step['args'] as $i => $arg) {
          if (isset($params[$i])) {
            # assign the argument by the parameter name
            $args[$params[$i]->name] = $arg;
          } else {
            # assign the argument by number
            $args['#' . ($i + 1)] = $arg;
          }
        }
      }

      if (isset($step['class'])) {
        # Class->method() or Class::method()
        $function = $step['class'] . $step['type'] . $function;
      }

      // TODO: it's possible to parse the object name out from the source!
      $output[] = array(
          'function' => $function,
          'args'     => isset($args) ? $args : null,
          'file'     => isset($file) ? $file : null,
          'line'     => isset($line) ? $line : null,
          'source'   => isset($source) ? $source : null,
          'object'   => isset($step['object']) ? $step['object'] : null,
          'index'    => isset($step['index']) ? $step['index'] - count($data) : null,
      );

      unset($function, $args, $file, $line, $source);
    }

    return $output;
  }

  /**
   * removes comments and zaps whitespace & <?php tags from php code, makes for easier further parsing
   *
   * @param string $source
   *
   * @return string
   */
  private static function _removeAllButCode($source)
  {
    $commentTokens = array(
        T_COMMENT     => true,
        T_INLINE_HTML => true,
        T_DOC_COMMENT => true,
    );

    $whiteSpaceTokens = array(
        T_WHITESPACE         => true,
        T_CLOSE_TAG          => true,
        T_OPEN_TAG           => true,
        T_OPEN_TAG_WITH_ECHO => true,
    );

    $cleanedSource = '';
    foreach (token_get_all($source) as $token) {
      if (is_array($token)) {
        if (isset($commentTokens[$token[0]])) {
          continue;
        }

        if (isset($whiteSpaceTokens[$token[0]])) {
          $token = "\x07";
        } else {
          $token = $token[1];
        }
      } elseif ($token === ';') {
        $token = "\x07";
      }

      $cleanedSource .= $token;
    }

    return $cleanedSource;
  }

  /**
   * trace helper, shows the place in code inline
   *
   * @param string $file       full path to file
   * @param int    $lineNumber the line to display
   * @param int    $padding    surrounding lines to show besides the main one
   *
   * @return bool|string
   */
  private static function _showSource($file, $lineNumber, $padding = 7)
  {
    if (
        !$file
        ||
        !is_readable($file)
    ) {
      # continuing will cause errors
      return false;
    }

    # open the file and set the line position
    $filePointer = fopen($file, 'r');
    $line = 0;

    # Set the reading range
    $range = array(
        'start' => $lineNumber - $padding,
        'end'   => $lineNumber + $padding,
    );

    # set the zero-padding amount for line numbers
    $format = '% ' . strlen($range['end']) . 'd';

    $source = '';
    while (($row = fgets($filePointer)) !== false) {
      # increment the line number
      if (++$line > $range['end']) {
        break;
      }

      if ($line >= $range['start']) {
        # make the row safe for output
        $row = htmlspecialchars($row, ENT_NOQUOTES, 'UTF-8');

        # trim whitespace and sanitize the row
        $row = '<span>' . sprintf($format, $line) . '</span> ' . $row;

        if ($line === $lineNumber) {
          # apply highlighting to this row
          $row = '<div class="kint-highlight">' . $row . '</div>';
        } else {
          $row = '<div>' . $row . '</div>';
        }

        # add to the captured source
        $source .= $row;
      }
    }

    # close the file-pointer
    fclose($filePointer);

    return $source;
  }

  /**
   * returns whether current trace step belongs to Kint or its wrappers
   *
   * @param $step
   *
   * @return array
   */
  private static function _stepIsInternal($step)
  {
    if (isset($step['class'])) {
      foreach (self::$aliases['methods'] as $alias) {
        if (
            $alias[0] === $step['class']
            &&
            $alias[1] === $step['function']
        ) {
          return true;
        }
      }

      return false;
    } else {
      return in_array($step['function'], self::$aliases['functions'], true);
    }
  }

  /**
   * Dump information about variables, accepts any number of parameters, supports modifiers:
   *
   *  clean up any output before kint and place the dump at the top of page:
   *   - Kint::dump()
   *  *****
   *  expand all nodes on display:
   *   ! Kint::dump()
   *  *****
   *  dump variables disregarding their depth:
   *   + Kint::dump()
   *  *****
   *  return output instead of displaying it:
   *   @ Kint::dump()
   *  *****
   *  force output as plain text
   *   ~ Kint::dump()
   *
   * Modifiers are supported by all dump wrapper functions, including Kint::trace(). Space is optional.
   *
   *
   * You can also use the following shorthand to display debug_backtrace():
   *   Kint::dump( 1 );
   *
   * Passing the result from debug_backtrace() to kint::dump() as a single parameter will display it as trace too:
   *   $trace = debug_backtrace( true );
   *   Kint::dump( $trace );
   *  Or simply:
   *   Kint::dump( debug_backtrace() );
   *
   *
   * @param mixed $data
   *
   * @return void|string
   */
  public static function dump($data = null)
  {
    if (!self::enabled()) {
      return '';
    }

    $stash = self::settings();

    list($names, $modifiers, $callee, $previousCaller, $miniTrace) = self::_getCalleeInfo(
        // DEBUG_BACKTRACE_IGNORE_ARGS was introduced in PHP 5.3.6
        defined('DEBUG_BACKTRACE_IGNORE_ARGS')
            ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            : debug_backtrace()
    );

    # set mode for current run
    $mode = self::enabled();
    if ($mode === true) {
      $mode = (PHP_SAPI === 'cli' && self::$cliDetection === true) ? self::MODE_CLI : self::MODE_RICH;
    }
    self::enabled($mode);

    if (strpos($modifiers, '~') !== false) {
      self::enabled(self::MODE_WHITESPACE);
    }

    switch (self::enabled()) {
      case self::MODE_RICH:
        $decorator = 'kint\decorators\Kint_Decorators_Rich';
        break;
      case self::MODE_JS:
        $decorator = 'kint\decorators\Kint_Decorators_JS';
        break;
      default:
      case self::MODE_PLAIN:
        $decorator = 'kint\decorators\Kint_Decorators_Plain';
        break;
    }

    /* @var Kint_Decorators $decorator */

    $firstRunOldValue = $decorator::$firstRun;

    # process modifiers: @, +, ! and -
    if (strpos($modifiers, '-') !== false) {
      $decorator::$firstRun = true;
      while (ob_get_level()) {
        ob_end_clean();
      }
    }

    if (strpos($modifiers, '!') !== false) {
      self::$expandedByDefault = true;
    }

    if (strpos($modifiers, '+') !== false) {
      self::$maxLevels = false;
    }

    if (strpos($modifiers, '@') !== false) {
      self::$returnOutput = true;
      $decorator::$firstRun = true;
    }

    $output = '';
    if ($decorator::$firstRun) {
      $output .= call_user_func(array($decorator, 'init'));
    }

    $trace = false;
    $tmpFuncNumArgs = func_num_args();
    if (
        $data === 1
        &&
        $tmpFuncNumArgs === 1
        &&
        $names === array(null)
    ) {

      # Kint::dump(1) shorthand
      $trace = debug_backtrace(true);

    } elseif (
        $tmpFuncNumArgs === 1
        &&
        is_array($data)
    ) {

      $trace = $data; # test if the single parameter is result of debug_backtrace()

    }
    $trace and $trace = self::_parseTrace($trace);

    $output .= call_user_func(array($decorator, 'wrapStart'));
    if ($trace) {
      $output .= call_user_func(array($decorator, 'decorateTrace'), $trace);
    } else {
      $data = $tmpFuncNumArgs === 0 ? array('[[no arguments passed]]') : func_get_args();

      foreach ($data as $k => $argument) {
        KintParser::reset();
        # when the dump arguments take long to generate output, user might have changed the file and
        # Kint might not parse the arguments correctly, so check if names are set and while the
        # displayed names might be wrong, at least don't throw an error
        $output .= call_user_func(
            array($decorator, 'decorate'),
            KintParser::factory($argument, isset($names[$k]) ? $names[$k] : '')
        );
      }
    }

    $output .= call_user_func(array($decorator, 'wrapEnd'), $callee, $miniTrace, $previousCaller);

    $decorator::$firstRun = false;

    if (strpos($modifiers, '@') !== false) {
      $decorator::$firstRun = $firstRunOldValue;
    }

    if (self::$returnOutput) {
      self::settings($stash);

      return $output;
    }

    if (self::$delayedMode) {
      self::settings($stash);
      register_shutdown_function('printf', '%s', $output);

      return '';
    }

    self::settings($stash);
    echo $output;

    return '';
  }

  /**
   * Enables or disables Kint, can globally enforce the rendering mode. If called without parameters, returns the
   * current mode.
   *
   * @param mixed $forceMode
   *                     null or void - return current mode
   *                     false        - disable (no output)
   *                     true         - enable and detect cli automatically
   *                     Kint::MODE_* - enable and force selected mode disregarding detection and function
   *                     shorthand (s()/d()), note that you can still override this
   *                     with the "~" modifier
   *
   * @return mixed        previously set value if a new one is passed
   */
  public static function enabled($forceMode = null)
  {
    # act both as a setter...
    if (isset($forceMode)) {
      $before = self::$_enabledMode;
      self::$_enabledMode = $forceMode;

      return $before;
    }

    # ...and a getter
    return self::$_enabledMode;
  }

  /**
   * Stashes or sets all settings at once
   *
   * @param array|null $settings Array of all settings to be set or null to set none
   *
   * @return array Current settings
   */
  public static function settings(array $settings = null)
  {
    static $keys = array(
        'delayedMode',
        '_enabledMode',
        'aliases',
        'appRootDirs',
        'cliColors',
        'displayCalledFrom',
        'expandedByDefault',
        'fileLinkFormat',
        'maxLevels',
        'maxStrLength',
        'returnOutput',
        'theme',
    );

    $out = array();
    foreach ($keys as $key) {
      /** @noinspection PhpVariableVariableInspection */
      $out[$key] = self::$$key;
    }

    if ($settings !== null) {
      $in = array_intersect_key($settings, array_flip($keys));
      foreach ($in as $key => $val) {
        /** @noinspection PhpVariableVariableInspection */
        self::$$key = $val;
      }
    }

    return $out;
  }

  /**
   * @param string $file
   * @param int    $line
   *
   * @return mixed
   */
  public static function getIdeLink($file, $line)
  {
    return str_replace(array('%f', '%l'), array($file, $line), self::$fileLinkFormat);
  }

  /**
   * generic path display callback, can be configured in the settings; purpose is to show relevant path info and hide
   * as much of the path as possible.
   *
   * @param string $file
   *
   * @return string
   */
  public static function shortenPath($file)
  {
    $file = str_replace('\\', '/', $file);
    $shortenedName = $file;
    $replaced = false;
    if (is_array(self::$appRootDirs)) {
      foreach (self::$appRootDirs as $path => $replaceString) {
        if (empty($path)) {
          continue;
        }

        $path = str_replace('\\', '/', $path);

        if (strpos($file, $path) === 0) {
          $shortenedName = $replaceString . substr($file, strlen($path));
          $replaced = true;
          break;
        }
      }
    }

    # fallback to find common path with Kint dir
    if (!$replaced) {
      $pathParts = explode('/', str_replace('\\', '/', KINT_DIR));
      $fileParts = explode('/', $file);
      $i = 0;
      foreach ($fileParts as $i => $filePart) {
        if (!isset($pathParts[$i]) || $pathParts[$i] !== $filePart) {
          break;
        }
      }

      $shortenedName = ($i ? '.../' : '') . implode('/', array_slice($fileParts, $i));
    }

    return $shortenedName;
  }

  /**
   * Prints a debug backtrace, same as Kint::dump(1)
   *
   * @param array $trace [OPTIONAL] you can pass your own trace, otherwise, `debug_backtrace` will be called
   *
   * @return mixed
   */
  public static function trace($trace = null)
  {
    if (!self::enabled()) {
      return '';
    }

    return self::dump(isset($trace) ? $trace : debug_backtrace(true));
  }
}

[![Stories in Ready](https://badge.waffle.io/voku/kint.png?label=ready&title=Ready)](https://waffle.io/voku/kint)
[![Build Status](https://travis-ci.org/voku/kint.svg?branch=master)](https://travis-ci.org/voku/kint)
[![Coverage Status](https://coveralls.io/repos/github/voku/kint/badge.svg?branch=master)](https://coveralls.io/github/voku/kint?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/kint/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/kint/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/d071e6a05db647318dae434ecae22e1b)](https://www.codacy.com/app/voku/kint)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/541e57af-d622-4cb1-8b27-3ac725fd4c99/mini.png)](https://insight.sensiolabs.com/projects/541e57af-d622-4cb1-8b27-3ac725fd4c99)
[![Dependency Status](https://www.versioneye.com/user/projects/571dd3b8fcd19a00454422c0/badge.svg?style=flat)](https://www.versioneye.com/user/projects/571dd3b8fcd19a00454422c0)
[![Latest Stable Version](https://poser.pugx.org/voku/kint/v/stable)](https://packagist.org/packages/voku/kint) 
[![Total Downloads](https://poser.pugx.org/voku/kint/downloads)](https://packagist.org/packages/voku/kint) 
[![Latest Unstable Version](https://poser.pugx.org/voku/kint/v/unstable)](https://packagist.org/packages/voku/kint)
[![PHP 7 ready](http://php7ready.timesplinter.ch/voku/kint/badge.svg)](https://travis-ci.org/voku/kint)
[![License](https://poser.pugx.org/voku/kint/license)](https://packagist.org/packages/voku/kint)

# Kint (Fork) - debugging helper for PHP developers

![Screenshot](http://raveren.github.com/kint/img/preview.png)

## What am I looking at?

At first glance Kint is just a pretty replacement for **[var_dump()](http://php.net/manual/en/function.var-dump.php)**, **[print_r()](http://php.net/manual/en/function.print-r.php)** and **[debug_backtrace()](http://php.net/manual/en/function.debug-backtrace.php)**.

## Installation and Usage

**Composer:**

```json
"require": {
   "voku/kint": "^2.0"
}
```

Or just run `composer require voku/kint`

**That's it, you can now use Kint to debug your code:**

```php
Kint::enabled(true);

########## DUMP VARIABLE ###########################
Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters

// or simply use d() as a shorthand:
d($_SERVER);


########## DEBUG BACKTRACE #########################
Kint::trace();
// or via shorthand:
d(1);


############# BASIC OUTPUT #########################
# this will show a basic javascript-free display
s($GLOBALS);


######### WHITESPACE FORMATTED OUTPUT ##############
# this will be garbled if viewed in browser as it is whitespace-formatted only
~d($GLOBALS); // just prepend with the tilde


########## MISCELLANEOUS ###########################
# this will disable kint completely
Kint::enabled(false);

ddd('Get off my lawn!'); // no effect

Kint::enabled(true);
ddd( 'this line will stop the execution flow because Kint was just re-enabled above!' );


```

Note, that Kint *does* have configuration (like themes and IDE integration!), but it's in need of being rewritten, so I'm not documenting it yet.

## Tips & Tricks

  * Kint is disabled by default, call `Kint::enabled(true);` to turn its funcionality on. The *best practice* is to enable Kint in DEVELOPMENT environment only (or for example `Kint::enabled($_SERVER['REMOTE_ADDR'] === '<your IP>');`) - so even if you accidentally leave a dump in production, no one will know.
  *
  * `kint\sd()` and `kint\ddd()` are shorthands for `kint\s();die;` and `kint\d();die;` respectively.
  * When looking at Kint output, press <kbd>D</kbd> on the keyboard and you will be able to traverse the tree with arrows and tab keys - and expand/collapse nodes with space or enter.
  * Double clicking the `[+]` sign in the output will expand/collapse ALL nodes; triple clicking big blocks of text will select it all.
  * Clicking the tiny arrows on the right of the output open it in a separate window where you can keep it for comparison.
  * To catch output from Kint just assign it to a variable<sup>beta</sup>
```php
$o = Kint::dump($GLOBALS); 
// yes, the assignment is automatically detected, and $o 
// now holds whatever was going to be printed otherwise.

// it also supports modifiers (read on) for the variable:
~$o = Kint::dump($GLOBALS); // this output will be in whitespace
```
  * There are a couple of real-time modifiers you can use:
    * `~d($var)` this call will output in plain text format.
    * `+d($var)` will disregard depth level limits and output everything (careful, this can hang your browser on huge objects)
    * `!d($var)` will show expanded rich output.
    * `-d($var)` will attempt to `ob_clean` the previous output so if you're dumping something inside a HTML page, you will still see Kint output.
  You can combine modifiers too: `~+d($var)`
  * To force a specific dump output type just pass it to the `Kint::enabled()` method. Available options are: `Kint::MODE_RICH` (default), `Kint::MODE_PLAIN`, `Kint::MODE_WHITESPACE` and `Kint::MODE_CLI`:
```php
Kint::enabled(Kint::MODE_WHITESPACE);
$kintOutput = Kint::dump($GLOBALS); 
// now $kintOutput can be written to a text log file and 
// be perfectly readable from there
```
  * To change display theme, use `Kint::$theme = '<theme name>';` where available options are: `'original'` (default), `'solarized'`, `'solarized-dark'` and `'aante-light'`. Here's an (outdated) preview:
  ![Kint themes](http://raveren.github.io/kint/img/theme-preview.png)
  * Kint also includes a naïve profiler you may find handy. It's for determining relatively which code blocks take longer than others:
```php
Kint::dump( microtime() ); // just pass microtime()
sleep( 1 );
Kint::dump( microtime(), 'after sleep(1)' );
sleep( 2 );
ddd( microtime(), 'final call, after sleep(2)' );
```
  ![Kint profiling feature](http://i.imgur.com/tmHUMW4.png)
----

[Visit the project page](http://raveren.github.com/kint/) for documentation, configuration, and more advanced usage examples.

### Author

**Rokas Šleinius** (Raveren)

### License

Licensed under the MIT License

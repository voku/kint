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

# There is a new maintained version of Kint! -> (kint-php)[https://github.com/kint-php/kint] thx@jnvsor

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
use kint\Kint;

Kint::enabled(true);

########## DUMP VARIABLE ###########################
Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters

// or simply use d() as a shorthand:
\kint\d($_SERVER);


########## DEBUG BACKTRACE #########################
Kint::trace();
// or via shorthand:
\kint\d(1);


############# BASIC OUTPUT #########################
# this will show a basic javascript-free display
\kint\s($GLOBALS);


########## MISCELLANEOUS ###########################
# this will disable kint completely
Kint::enabled(false);

\kint\dd('Get off my lawn!'); // no effect

Kint::enabled(true);
\kint\dd( 'this line will stop the execution flow because Kint was just re-enabled above!' );
```

## WARNING / INFO

  * Kint is disabled by default, call `kint\Kint::enabled(true);` to turn its funcionality on. The *best practice* is to enable Kint in DEVELOPMENT environment only (or for example `Kint::enabled($_SERVER['REMOTE_ADDR'] === '<your IP>');`) - so even if you accidentally leave a dump in production, no one will know.

### Author

**Rokas Šleinius** (Raveren)

### License

Licensed under the MIT License

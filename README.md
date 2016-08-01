# JBZoo Profiler  [![Build Status](https://travis-ci.org/JBZoo/Profiler.svg?branch=master)](https://travis-ci.org/JBZoo/Profiler)      [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Profiler/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Profiler?branch=master)

#### Simple Profiler for PHP code and unit tests

[![License](https://poser.pugx.org/JBZoo/Profiler/license)](https://packagist.org/packages/JBZoo/Profiler)   [![Latest Stable Version](https://poser.pugx.org/JBZoo/Profiler/v/stable)](https://packagist.org/packages/JBZoo/Profiler) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JBZoo/Profiler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/JBZoo/Profiler/?branch=master)


## Usage
```php
use JBZoo\Profiler\Benchmark;

// Compare performance of functions
Benchmark::compare([
    'md5'   => function () {
        $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
        return md5($string);
    },
    'sha1'  => function () {
        $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
        return sha1($string);
    },
    'crc32' => function () {
        $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
        return crc32($string);
    },
], array('count' => 500, 'name' => 'Hash functions'));

/* Result:

  ---------- Start benchmark: Hash functions  ----------
  Running tests 500 times
  PHP Overhead: time=58 ms; memory=0 B;

  Testing 1/3 : md5 ... Done!
  Testing 2/3 : sha1 ... Done!
  Testing 3/3 : crc32 ... Done!

  Name of test    Time, ms    Time, %     Memory    Memory, %
  crc32              1 551          ~    1.25 MB            ~
  md5                1 938         25    1.25 MB            ~
  sha1               2 776         79    1.25 MB            ~

  TOTAL TIME: 6 547.37 ms/4.36 ms;   MEMO: 41.05 KB/0.03 KB;   COUNT: 1 500
  ---------- Finish benchmark: Hash functions  ----------
*/

Benchmark::run(function () {
      $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
      return md5($string);
  }, ['count' => 1000]),

```


## Unit tests and check code style
```sh
make
make test-all
```


### License

MIT

<?php
/**
 * JBZoo Profiler
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   Profiler
 * @license   MIT
 * @copyright Copyright (C) JBZoo.com,  All rights reserved.
 * @link      https://github.com/JBZoo/Profiler
 * @author    Denis Smetannikov <denis@jbzoo.com>
 */

namespace JBZoo\PHPUnit;

use JBZoo\Profiler\Benchmark;

/**
 * Class BenchmarkTest
 * @package JBZoo\PHPUnit
 */
class BenchmarkTest extends PHPUnit
{
    public function testOne()
    {
        Benchmark::one(
            function () {
                $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
                return md5($string);
            },
            array(
                'name'  => __FUNCTION__,
                'count' => 100,
            )
        );
    }

    public function testCompare()
    {
        Benchmark::compare(
            array(
                'x1'  => function () {
                    return str_repeat(mt_rand(0, 9), 900000);
                },
                'x2'  => function () {
                    return str_repeat(mt_rand(0, 9), 900000 * 2);
                },
                'x3'  => function () {
                    return str_repeat(mt_rand(0, 9), 900000 * 3);
                },
                'x16' => function () {
                    return str_repeat(mt_rand(0, 9), 900000 * 16);
                },
            ),
            array(
                'name'  => __FUNCTION__,
                'count' => 100,
            )
        );
    }

    public function testHash()
    {
        Benchmark::compare(
            array(
                'md5'   => function () {
                    $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
                    return md5($string);
                },
                'crc32' => function () {
                    $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
                    return crc32($string);
                },
                'sha1'  => function () {
                    $string = str_repeat(mt_rand(0, 9), 1024 * 1024);
                    return sha1($string);
                },
            ),
            array(
                'name'  => __FUNCTION__,
                'count' => 100,
            )
        );
    }
}

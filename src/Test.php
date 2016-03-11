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

namespace JBZoo\Profiler;

use JBZoo\Utils\FS;
use JBZoo\Utils\Timer;

/**
 * Class Test
 * @package JBZoo\Profiler
 */
class Test
{
    /**
     * @var \Closure
     */
    private $_test;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var Profiler
     */
    private $_profiler;

    /**
     * @param string   $name
     * @param \Closure $testFunction
     */
    public function __construct($name, \Closure $testFunction)
    {
        $this->_name     = $name;
        $this->_test     = $testFunction;
        $this->_profiler = new Profiler();
    }

    /**
     * @param int $count
     * @return array
     */
    public function runTest($count = 1)
    {
        gc_collect_cycles(); // Forces collection of any existing garbage cycles

        $this->_profiler->start();

        for ($i = 0; $i < $count; $i++) {
            // Store the result so it appears in memory profiling
            $this->_executeTest();
        }

        $this->_profiler->stop();

        $time    = $this->_profiler->getTime();
        $timeOne = $this->_profiler->getTime() / $count;
        $memory  = $this->_profiler->getMemory();

        return array(
            'time'     => $time,
            'time_one' => $timeOne,
            'memory'   => $memory,
            'count'    => $count,
            'formated' => sprintf(
                "Time: %s/%s; Memory: %s; Count: %s",
                Timer::formatMS($time),
                Timer::formatMS($timeOne),
                FS::format($memory),
                $count
            ),
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    protected function _executeTest()
    {
        return call_user_func($this->_test);
    }
}

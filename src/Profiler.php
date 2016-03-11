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
use JBZoo\Utils\Sys;
use JBZoo\Utils\Timer;

/**
 * Class Profiler
 * @package JBZoo\Profiler
 */
class Profiler
{
    /**
     * @var int
     */
    private $_startMemory = 0;

    /**
     * @var int
     */
    private $_maxMemory = 0;

    /**
     * @var float
     */
    private $_startTime = 0.0;

    /**
     * @var float
     */
    private $_endTime = 0.0;

    /**
     * Start profiler
     * @param bool $registerTick
     */
    public function start($registerTick = true)
    {
        $this->_startTime   = microtime(true);
        $this->_startMemory = memory_get_usage(false);

        if ($registerTick && Sys::isFunc('register_tick_function')) {
            register_tick_function(array($this, 'tick'));
        }
    }

    /**
     * Check one tick
     */
    public function tick()
    {
        $this->_maxMemory = max($this->_maxMemory, memory_get_usage(false));
    }

    /**
     * Stop profiler
     */
    public function stop()
    {
        $this->_endTime = microtime(true);
        $this->tick();

        if (Sys::isFunc('unregister_tick_function')) {
            unregister_tick_function(array($this, 'tick'));
        }
    }

    /**
     * @return float
     */
    public function getMemory()
    {
        return $this->_maxMemory - $this->_startMemory;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->_endTime - $this->_startTime;
    }

    /**
     * @return string
     */
    public function getTotalUsage()
    {
        return sprintf(
            'Time: %s; Memory: %s',
            Timer::format($this->getTime()),
            FS::format($this->getMemory(), 2)
        );
    }

    /**
     * Returns the resources (time, memory) of the request as a string.
     *
     * @param bool $getPeakMemory
     * @param bool $isRealMemory
     * @return string
     */
    public static function resourceUsage($getPeakMemory = true, $isRealMemory = false)
    {
        if ($getPeakMemory) {
            $message = 'Time: %s, Peak memory: %s';
            $memory  = memory_get_peak_usage($isRealMemory);
        } else {
            $message = 'Time: %s, Memory: %s';
            $memory  = memory_get_usage($isRealMemory);
        }

        $memory = FS::format($memory, 2);
        $time   = Timer::format(Timer::timeSinceStart());

        return sprintf($message, $time, $memory);
    }
}

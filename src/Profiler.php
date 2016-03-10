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
     */
    public function start()
    {
        $this->_startMemory = memory_get_usage(false);
        $this->_startTime   = microtime(true);

        register_tick_function(array($this, 'tick'));
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
        $this->tick();
        $this->_endTime = microtime(true);

        unregister_tick_function(array($this, 'tick'));
    }

    /**
     * @return float
     */
    public function getMemoryUsage()
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
}

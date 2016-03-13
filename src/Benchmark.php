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
use JBZoo\Utils\Cli;
use JBZoo\Utils\Timer;
use JBZoo\Utils\Vars;

/**
 * Class Benchmark
 * @package JBZoo\Profiler
 */
class Benchmark
{
    const COL_NAME       = 'Name of test';
    const COL_TIME       = 'Time';
    const COL_TIME_ONE   = 'Time one';
    const COL_TIME_REL   = 'Time, %';
    const COL_MEMORY     = 'Memory leak';
    const COL_MEMORY_REL = 'Memory, %';

    /**
     * @var array [Test]
     */
    private $_tests = array();

    /**
     * @var int
     */
    private $_count = 1;

    /**
     * @var array
     */
    private $_overhead = array();

    /**
     * @param Test $test
     */
    public function addTest(Test $test)
    {
        $this->_tests[$test->getName()] = $test;
    }

    /**
     * Utility method to create tests on the fly. You may chain the test:
     *
     * @param string   $name
     * @param \Closure $closure function to execute
     * @return Test
     */
    public function add($name, \Closure $closure)
    {
        $test = new Test($name, $closure);
        $this->addTest($test);

        return $test;
    }

    /**
     * Runs an empty test to determine the benchmark overhead and run each test once
     */
    private function _warmup()
    {
        $warmup = new Test('warmup', function () {
        });

        $this->_overhead = $warmup->runTest($this->_count);

        // One call each method for init (warmup)

        /** @var Test $test */
        foreach ($this->_tests as $test) {
            $test->runTest(1);
        }

        $this->out(
            'PHP Overhead: ' .
            'time=' . Timer::formatMS($this->_overhead['time']) . '; ' .
            'memory=' . FS::format($this->_overhead['memory'], 2) . ';' .
            PHP_EOL
        );
    }

    /**
     * @param bool $output
     * @param bool $warmup
     * @return array
     */
    public function run($output = true, $warmup = true)
    {
        if ($output) {
            $this->out("Running tests {$this->_count} times");
        }

        if ($warmup) {
            $this->_warmup();
        }

        $results = array(
            'overhead' => $this->_overhead,
            'list'     => array(),
        );

        $testNum = 0;

        /**
         * @var Test $test
         */
        foreach ($this->_tests as $name => $test) {
            if ($output) {
                $this->out('Testing ' . ++$testNum . '/' . count($this->_tests) . ' : ' . $name . ' ... ', false);
            }
            $results['list'][$name] = $test->runTest($this->_count);

            $this->out('Done!');
        }

        $this->out('');

        if ($output) {
            $this->outputTable($this->formatResults($results));
        }

        return $results;
    }

    /**
     * @param $count
     */
    public function setCount($count)
    {
        $this->_count = $count;
    }

    /**
     * Output results in columns, padding right if values are string, left if numeric
     *
     * @param  array   $lines   array(array('Name' => 'Value'));
     * @param  integer $padding space between columns
     */
    public function outputTable(array $lines, $padding = 4)
    {
        $pad = function ($string, $width) use ($padding) {
            if ($width > 0) {
                return str_pad($string, $width, ' ') . str_repeat(' ', $padding);
            } else {
                return str_pad($string, -$width, ' ', STR_PAD_LEFT) . str_repeat(' ', $padding);
            }
        };

        // init width with keys' length
        $cols = array_combine(
            array_keys($lines[0]),
            array_map('strlen', array_keys($lines[0]))
        );

        foreach ($cols as $col => $width) {

            foreach ($lines as $line) {
                $width = max($width, strlen($line[$col]));
            }

            if ($col !== self::COL_NAME) {
                $width = -$width;
            }

            $this->out($pad($col, $width), false);
            $cols[$col] = $width;
        }

        $this->out('');

        foreach ($lines as $line) {
            foreach ($cols as $col => $width) {
                $this->out($pad($line[$col], $width), false);
            }
            $this->out('');
        }
    }

    /**
     * Format the results, rounding numbers, showing difference percentages
     * and removing a flat time based on the benchmark overhead
     *
     * @param  array $results array($name => array('time' => 1.0))
     * @return array array(array('Test' => $name, 'Time' => '1000 ms', 'Perc' => '100 %'))
     */
    public function formatResults(array $results)
    {
        uasort($results['list'], function ($testOne, $testTwo) {
            if ($testOne['time'] === $testTwo['time']) {
                return 0;
            } else {
                return ($testOne['time'] < $testTwo['time']) ? -1 : 1;
            }
        });

        $minTime   = INF;
        $minMemory = INF;

        foreach ($results['list'] as $name => $result) {
            // time
            $time = $result['time'];
            //$time -= $this->_overhead['time']; // Substract base_time
            $results['list'][$name]['time'] = $time;

            $minTime = min($minTime, $time);

            // memory
            $memory = $results['list'][$name]['memory'];
            $memory -= $this->_overhead['memory'];
            $results['list'][$name]['memory'] = $memory;

            $minMemory = min($minMemory, $memory);
        }

        $output = array();

        $isOne = count($results['list']) === 1;
        foreach ($results['list'] as $name => $result) {
            if ($isOne) {
                $output[] = array(
                    self::COL_NAME     => $name,
                    self::COL_TIME     => $this->_timeFormat($result['time'], 0),
                    self::COL_TIME_ONE => $this->_timeFormat($result['time'] / $this->_count),
                    self::COL_MEMORY   => FS::format($result['memory'], 2),
                );
            } else {
                $output[] = array(
                    self::COL_NAME       => $name,
                    self::COL_TIME       => $this->_timeFormat($result['time'], 0),
                    self::COL_TIME_ONE   => $this->_timeFormat($result['time'] / $this->_count),
                    self::COL_TIME_REL   => Vars::relativePercent($minTime, $result['time']),
                    self::COL_MEMORY     => FS::format($result['memory'], 2),
                    self::COL_MEMORY_REL => Vars::relativePercent($minMemory, $result['memory']),
                );
            }
        }

        return $output;
    }

    /**
     * @param float $time
     * @param int   $decimal
     * @return string
     */
    protected function _timeFormat($time, $decimal = 3)
    {
        return number_format($time * 1000, $decimal, '.', ' ') . ' ms';
    }

    /**
     * @param string $message
     * @param bool   $addEol
     */
    public function out($message, $addEol = true)
    {
        if (function_exists('\JBZoo\PHPUnit\cliMessage')) {
            \JBZoo\PHPUnit\cliMessage($message, $addEol);
        } else {
            Cli::out($message, $addEol);
        }
    }

    /**
     * @param [Test] $tests
     * @param array $options
     * @return array
     */
    public static function compare($tests, $options)
    {
        $options = array_merge(array(
            'name'   => 'Compare tests',
            'count'  => 1000,
            'output' => true,
        ), $options);

        // Prepare
        $bench = new Benchmark();
        $bench->setCount($options['count']);
        foreach ($tests as $testName => $function) {
            $bench->add($testName, $function);
        }
        declare(ticks = 1);

        // Run tests
        $wrapProfiler = new Profiler();

        if ($options['output']) {
            $bench->out(PHP_EOL . '<pre>--------------- Start compare: ' . $options['name'] . ' ---------------');
            $wrapProfiler->start(false);
            $result = $bench->run(true);
            $wrapProfiler->stop();
            $bench->out(PHP_EOL . 'TOTAL: ' . $wrapProfiler->getTotalUsage());
            $bench->out('-------------------- Finish compare: ' . $options['name'] . ' ---------</pre>' . PHP_EOL);

        } else {
            $wrapProfiler->start(false);
            $result = $bench->run(false);
            $wrapProfiler->stop();
        }

        $result['total'] = array(
            'time'     => $wrapProfiler->getTime(),
            'memory'   => $wrapProfiler->getMemory(),
            'formated' => $wrapProfiler->getTotalUsage(),
        );

        return $result;
    }

    /**
     * @param callable $test
     * @param array    $options
     * @return array
     */
    public static function one($test, $options)
    {
        $options = array_merge(array(
            'name'   => 'One test',
            'count'  => 1000,
            'output' => true,
        ), $options);

        // Prepare
        $bench = new Benchmark();
        $bench->setCount($options['count']);
        $bench->add('Test', $test);
        declare(ticks = 1);

        // Run tests
        $wrapProfiler = new Profiler();
        $wrapProfiler->start(false);
        if ($options['output']) {
            $bench->out(PHP_EOL . '<pre>--------------- Start one bench: ' . $options['name'] . ' ---------------');
            $result = $bench->run(true);
            $bench->out('-------------------- Finish one bench: ' . $options['name'] . ' ---------</pre>' . PHP_EOL);
        } else {
            $result = $bench->run(false);
        }

        $wrapProfiler->stop();

        $result['total'] = array(
            'time'     => $wrapProfiler->getTime(),
            'time_one' => $wrapProfiler->getTime() / $options['count'],
            'memory'   => $wrapProfiler->getMemory(),
            'formated' => $wrapProfiler->getTotalUsage(),
        );

        return $result;
    }
}

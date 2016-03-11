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

use JBZoo\Profiler\Profiler;

/**
 * Class ProfilerTest
 * @package JBZoo\PHPUnit
 */
class ProfilerTest extends PHPUnit
{
    public function testStartAndStop()
    {
        $profiler = new Profiler();

        $profiler->start();
        sleep(1);
        $profiler->stop();

        $this->assertStringMatchesFormat('Time: %s; Memory: %s', $profiler->getTotalUsage());
    }

    public function testResourceUsage()
    {
        $this->assertStringMatchesFormat('Time: %s, Peak memory: %s', Profiler::resourceUsage());
        $this->assertStringMatchesFormat('Time: %s, Memory: %s', Profiler::resourceUsage(false));
    }
}

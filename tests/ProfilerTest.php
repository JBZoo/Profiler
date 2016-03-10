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
 */

namespace JBZoo\PHPUnit;

use JBZoo\Profiler\Profiler;

/**
 * Class ProfilerTest
 * @package JBZoo\PHPUnit
 */
class ProfilerTest extends PHPUnit
{
    public function testSimple()
    {
        $profiler = new Profiler();

        $profiler->start();
        sleep(1);
        $profiler->tick();
        $data = array(1, 2, 3, 4);
        $profiler->stop();

        isTrue($profiler->getMemoryUsage() > 0);
        isTrue($profiler->getTime() >= 1);
    }
}

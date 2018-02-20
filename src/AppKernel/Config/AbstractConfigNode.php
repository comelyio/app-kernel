<?php
/**
 * This file is part of Comely App Kernel package.
 * https://github.com/comelyio/app-kernel
 *
 * Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\AppKernel\Config;

use Comely\AppKernel\Exception\ConfigException;

/**
 * Class AbstractConfigNode
 * @package Comely\AppKernel\Config
 */
abstract class AbstractConfigNode
{
    /**
     * @param $prop
     * @param $arguments
     * @return mixed
     * @throws ConfigException
     */
    final public function __call($prop, $arguments)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }

        return null;
    }
}
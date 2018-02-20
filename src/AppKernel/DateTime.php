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

namespace Comely\AppKernel;

use Comely\AppKernel\Exception\AppKernelException;


/**
 * Class DateTime
 * @package Comely\AppKernel
 */
class DateTime
{
    /** @var null|string */
    private $timezone;

    /**
     * @param string $tz
     * @return DateTime
     * @throws AppKernelException
     */
    public function setTimezone(string $tz): self
    {
        $zones = \DateTimeZone::listIdentifiers();
        if (!in_array($tz, $zones)) {
            throw new AppKernelException('Invalid timezone');
        }

        $this->timezone = $tz;
        date_default_timezone_set($tz);
        return $this;
    }

    /**
     * @return null|string
     */
    public function currentTimezone(): ?string
    {
        return $this->timezone;
    }
}
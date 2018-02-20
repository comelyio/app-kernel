<?php
/**
 * This file is part of Comely App Kernel package.
 *  https://github.com/comelyio/app-kernel
 *
 *  Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code or visit following link:
 *  https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\AppKernel\Exception;

/**
 * Class ServicesException
 * @package Comely\AppKernel\Exception
 */
class ServicesException extends AppKernelException
{
    /**
     * @param string $service
     * @param string $message
     * @return ServicesException
     */
    public static function ServiceError(string $service, string $message): self
    {
        return new self(sprintf('Service [%s] error: %s', $service, $message));
    }
}
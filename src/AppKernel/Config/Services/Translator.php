<?php
/**
 * This file is part of Comely App Kernel package.
 * https://github.com/comelyio/app-kernel
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\AppKernel\Config\Services;

use Comely\AppKernel\Config\AbstractConfigNode;
use Comely\AppKernel\Exception\ConfigException;

/**
 * Class Translator
 * @package Comely\AppKernel\Config\Services
 * @method string fallBack()
 * @method bool caching()
 */
class Translator extends AbstractConfigNode
{
    /** @var string */
    protected $fallBack;
    /** @var bool */
    protected $caching;

    /**
     * Translator constructor.
     * @param array $opts
     * @throws ConfigException
     */
    public function __construct(array $opts)
    {
        // Default & Fallback language
        $fallBack = $opts["fall_back"] ?? $opts["fallBack"] ?? null;
        if (!is_string($fallBack)) {
            throw ConfigException::PropError(
                'app.services.translator',
                sprintf('Property "fall_back" must be of type string')
            );
        }

        $this->fallBack = $fallBack;

        // Caching
        $caching = $opts["caching"] ?? $opts["cache"] ?? null;
        if (!is_bool($caching)) {
            throw ConfigException::PropError(
                'app.services.translator',
                sprintf('Property "caching" can be either of "on" or "off"')
            );
        }

        $this->caching = $caching;
    }
}
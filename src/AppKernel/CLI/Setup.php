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

namespace Comely\AppKernel\CLI;

/**
 * Class Setup
 * @package Comely\AppKernel\CLI
 */
class Setup
{
    /** @var string */
    public $env;
    /** @var bool */
    public $force;
    /** @var bool */
    public $cachedConfig;
    /** @var bool */
    public $noSleep;

    /**
     * Setup constructor.
     * @param array $flags
     */
    public function __construct(array $flags)
    {
        $this->env = "cli";
        $this->force = false;
        $this->cachedConfig = true;
        $this->noSleep = false;

        // Job execution environment
        $env = $flags["env"] ?? $flags["e"] ?? null;
        if ($env) { // Job environment flag
            $this->env = $env;
        }

        // Force flag
        if (isset($flags["force"]) || isset($flags["f"])) {
            $this->force = true;
        }

        // Cached config?
        if (isset($flags["nocache"])) {
            $this->cachedConfig = false;
        }

        // Quick run (with no sleep/delay in-between lines)
        if (isset($flags["quick"]) || isset($flags["q"])) {
            $this->noSleep = true;
        }
    }
}
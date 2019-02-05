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
 * Class Cache
 * @package Comely\AppKernel\Config\Services
 * @method string engine()
 * @method string host()
 * @method int port()
 * @method int timeOut()
 * @method bool terminate()
 */
class Cache extends AbstractConfigNode
{
    /** @var string */
    protected $engine;
    /** @var string */
    protected $host;
    /** @var int */
    protected $port;
    /** @var bool */
    protected $terminate;
    /** @var int */
    protected $timeOut;

    /**
     * Cache constructor.
     * @param array $options
     * @throws ConfigException
     */
    public function __construct(array $options)
    {
        // Engine
        $engine = $options["engine"] ?? null;
        if (!is_string($engine)) {
            throw ConfigException::PropError('services.cache', 'Property "engine" must be of type string');
        }

        $this->engine = strtolower($engine);

        // Host
        $host = $options["host"] ?? null;
        if (!is_string($host)) {
            throw ConfigException::PropError('services.cache', 'Property "host" must be of type string');
        }

        $this->host = $host;

        // Port
        $port = $options["port"] ?? null;
        if (!is_int($port) || $port <= 0) {
            throw ConfigException::PropError('services.cache', 'Property "port" must be of type integer');
        }

        $this->port = $port;

        // Time out
        $timeOut = $options["time_out"] ?? $options["timeOut"] ?? $options["timeout"] ?? null;
        if (!is_int($timeOut) || $timeOut <= 0) {
            throw ConfigException::PropError('services.cache', 'Property "time_out" must be a positive integer');
        }

        $this->timeOut = $timeOut;

        // Terminate?
        $terminate = $options["terminate"] ?? null;
        if (!is_bool($terminate)) {
            throw ConfigException::PropError('services.cache', 'Property "terminate" must be "true" or "false"');
        }

        $this->terminate = $terminate;
    }
}
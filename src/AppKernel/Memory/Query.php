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

namespace Comely\AppKernel\Memory;

use Comely\AppKernel\Exception\MemoryException;
use Comely\AppKernel\Memory;

/**
 * Class Query
 * @package Comely\AppKernel\Memory
 * @property bool $_cache
 * @property int $_cacheTTL
 * @property string $_key
 * @property string $_instanceOf
 * @property null|\Closure $_callback
 */
class Query
{
    /** @var Memory */
    private $memory;
    /** @var bool */
    private $cache;
    /** @var int */
    private $cacheTTL;
    /** @var null|string */
    private $key;
    /** @var null|string */
    private $instanceOf;
    /** @var null|\Closure */
    private $callback;

    /**
     * Query constructor.
     * @param Memory $memory
     * @param string $key
     * @param string $instanceOf
     */
    public function __construct(Memory $memory, string $key, string $instanceOf)
    {
        $this->memory = $memory;
        $this->cache = false;
        $this->cacheTTL = 0;
        $this->key = $key;
        $this->instanceOf = $instanceOf;
    }

    /**
     * @param $prop
     * @return bool|\Closure|int|null|string
     * @throws MemoryException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_cache":
                return $this->cache;
            case "_cacheTTL":
                return $this->cacheTTL;
            case "_key":
                return $this->key;
            case "_instanceOf":
                return $this->instanceOf;
            case "_callback":
                return $this->callback;
        }

        throw new MemoryException('Cannot read inaccessible property');
    }

    /**
     * @param $prop
     * @param $value
     * @throws MemoryException
     */
    public function __set($prop, $value)
    {
        throw new MemoryException('Cannot override inaccessible property');
    }

    /**
     * @param int $ttl
     * @return Query
     */
    public function cache(int $ttl = 0): self
    {
        $this->cache = true;
        $this->cacheTTL = $ttl;
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return Query
     */
    public function callback(\Closure $callback): self
    {
        if (is_callable($callback)) {
            $this->callback = $callback;
        }

        return $this;
    }

    /**
     * @param callable|null $callback
     * @return null|object
     * @throws MemoryException
     */
    public function fetch(callable $callback = null)
    {
        if ($callback) {
            $this->callback($callback);
        }

        return $this->memory->get($this);
    }
}
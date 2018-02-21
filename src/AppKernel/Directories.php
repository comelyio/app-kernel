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

use Comely\AppKernel;
use Comely\AppKernel\Exception\AppKernelException;
use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;

/**
 * Class Directories
 * @package Comely\AppKernel
 */
class Directories
{
    /** @var AppKernel */
    private $kernel;
    /** @var Directory */
    private $root;
    /** @var null|Directory */
    private $cache;
    /** @var null|Directory */
    private $config;
    /** @var null|Directory */
    private $compiler;
    /** @var null|Directory */
    private $langs;
    /** @var null|Directory */
    private $logs;
    /** @var null|Directory */
    private $sessions;

    /**
     * Directories constructor.
     * @param AppKernel $kernel
     * @param Directory $root
     */
    public function __construct(AppKernel $kernel, Directory $root)
    {
        $this->kernel = $kernel;
        $this->root = $root;
    }

    /**
     * @return Directory
     */
    public function root(): Directory
    {
        return $this->root;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function config(): Directory
    {
        if (!$this->config) {
            $this->config = $this->dir("config");
        }

        return $this->config;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function cache(): Directory
    {
        if (!$this->cache) {
            $this->cache = $this->dir("cache", true);
        }

        return $this->cache;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function compiler(): Directory
    {
        if (!$this->compiler) {
            $this->compiler = $this->dir("compiler", true);
        }

        return $this->compiler;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function langs(): Directory
    {
        if (!$this->langs) {
            $this->langs = $this->dir("langs");
        }

        return $this->langs;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function logs(): Directory
    {
        if (!$this->logs) {
            $this->logs = $this->dir("logs", true);
        }

        return $this->logs;
    }

    /**
     * @return Directory
     * @throws AppKernelException
     */
    public function sessions(): Directory
    {
        if (!$this->sessions) {
            $this->sessions = $this->dir("sessions", true);
        }

        return $this->sessions;
    }

    /**
     * @param string $prop
     * @param bool $writable
     * @return Directory
     * @throws AppKernelException
     */
    private function dir(string $prop, bool $writable = false): Directory
    {
        $prop = strtoupper($prop);
        $directoryPath = $this->kernel->constant("DIR_" . $prop);
        $prop = ucfirst($prop);

        try {
            $directory = $this->root->dir($directoryPath);
            if (!$directory->permissions()->read) {
                throw new AppKernelException(
                    sprintf('%s directory "%s" is not readable', $prop, $directoryPath)
                );
            }

            if ($writable && !$directory->permissions()->write) {
                throw new AppKernelException(
                    sprintf('%s directory "%s" is not writable', $prop, $directoryPath)
                );
            }
        } catch (DiskException $e) {
            throw new AppKernelException(
                sprintf('No such %s directory found "%s" in app root', $prop, $directoryPath)
            );
        }

        return $directory;
    }
}
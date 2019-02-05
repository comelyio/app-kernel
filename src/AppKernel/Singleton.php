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

namespace Comely\AppKernel;

use Comely\AppKernel;
use Comely\AppKernel\Exception\AppKernelException;
use Comely\AppKernel\Exception\BootstrapException;
use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Exception\CacheException;
use Comely\IO\Cipher\Cipher;
use Comely\IO\Cipher\Exception\CipherException;
use Comely\IO\HttpRouter\Exception\HttpRouterException;
use Comely\IO\HttpRouter\Router;
use Comely\IO\Session\ComelySession;
use Comely\IO\Session\Exception\SessionException;
use Comely\IO\Translator\Exception\TranslatorException;
use Comely\IO\Translator\Translator;
use Comely\Knit\Exception\KnitException;
use Comely\Knit\Knit;

/**
 * Class Singleton
 * @package Comely\AppKernel
 */
abstract class Singleton
{
    /** @var null|AppKernel */
    protected static $instance;

    /**
     * @return AppKernel
     * @throws BootstrapException
     */
    final public static function getInstance(): AppKernel
    {
        if (!self::$instance) {
            throw new BootstrapException('AppKernel not bootstrapped');
        }

        return self::$instance;
    }

    /**
     * @return AppKernel
     * @throws BootstrapException
     */
    final public static function getKernel(): AppKernel
    {
        return self::getInstance();
    }

    /**
     * @return Cache
     * @throws AppKernelException
     * @throws BootstrapException
     * @throws CacheException
     */
    final public static function getCache(): Cache
    {
        return self::getInstance()->cache();
    }

    /**
     * @return Cipher
     * @throws AppKernelException
     * @throws BootstrapException
     * @throws CipherException
     */
    final public static function getCipher(): Cipher
    {
        return self::getInstance()->cipher();
    }

    /**
     * @return ComelySession
     * @throws AppKernelException
     * @throws BootstrapException
     * @throws SessionException
     */
    final public static function getSession(): ComelySession
    {
        return self::getInstance()->session();
    }

    /**
     * @return Translator
     * @throws AppKernelException
     * @throws BootstrapException
     * @throws TranslatorException
     */
    public static function getTranslator(): Translator
    {
        return self::getInstance()->translator();
    }

    /**
     * @return Knit
     * @throws AppKernelException
     * @throws BootstrapException
     * @throws KnitException
     */
    public static function getKnit(): Knit
    {
        return self::getInstance()->knit();
    }

    /**
     * @return Router
     * @throws BootstrapException
     * @throws HttpRouterException
     */
    public static function getRouter(): Router
    {
        return self::getInstance()->router();
    }
}
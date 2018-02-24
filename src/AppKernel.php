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

namespace Comely;

use Comely\AppKernel\Config;
use Comely\AppKernel\Databases;
use Comely\AppKernel\DateTime;
use Comely\AppKernel\Directories;
use Comely\AppKernel\ErrorHandler;
use Comely\AppKernel\Exception\AppKernelException;
use Comely\AppKernel\Exception\BootstrapException;
use Comely\AppKernel\Exception\ConfigException;
use Comely\AppKernel\Exception\ServicesException;
use Comely\AppKernel\Http;
use Comely\AppKernel\Memory;
use Comely\AppKernel\Services;
use Comely\AppKernel\Singleton;
use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Exception\CacheException;
use Comely\IO\Cipher\Cipher;
use Comely\IO\Cipher\Exception\CipherException;
use Comely\IO\Database\Database;
use Comely\IO\Database\Exception\DatabaseException;
use Comely\IO\Events\EventsHandler;
use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\IO\HttpRouter\Exception\HttpRouterException;
use Comely\IO\HttpRouter\Router;
use Comely\IO\Session\ComelySession;
use Comely\IO\Session\Exception\SessionException;
use Comely\IO\Translator\Exception\TranslatorException;
use Comely\IO\Translator\Translator;
use Comely\Knit\Exception\KnitException;
use Comely\Knit\Knit;

/**
 * Class AppKernel
 * @package Comely\AppKernel
 */
class AppKernel extends Singleton
{
    /** string App Name */
    public const NAME = 'Comely App Kernel';
    /** string Comely App Kernel Version (Major.Minor.Release-Suffix) */
    public const VERSION = "1.0.0";
    /** int Comely App Kernel Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 10000;

    protected const DIR_CONFIG = "config";
    protected const DIR_CACHE = "cache";
    protected const DIR_COMPILER = "compiler";
    protected const DIR_LANGS = "translator";
    protected const DIR_LOGS = "logs";
    protected const DIR_SESSIONS = "sessions";

    /** @var Config */
    private $config;
    /** @var Databases */
    private $databases;
    /** @var DateTime */
    private $dateTime;
    /** @var bool */
    private $dev;
    /** @var Directories */
    private $directories;
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var null|Memory */
    private $memory;
    /** @var Services */
    private $services;
    /** @var EventsHandler */
    private $events;
    /** @var null */
    private $http;

    /**
     * @param array $options
     * @param string $env
     * @param bool $terminate
     * @return AppKernel
     * @throws AppKernelException
     */
    final public static function Bootstrap(array $options, string $env, $terminate = true): self
    {
        if (static::$instance) {
            throw new BootstrapException('AppKernel instance already bootstrapped');
        }

        try {
            static::$instance = new static($options, $env);
        } catch (AppKernelException $e) {
            if ($terminate) { // Display error screen
                $rootPath = $options["rootPath"] ?? $options["root_path"] ?? $options["root_dir"] ?? "";
                (new ErrorHandler\Screen(true, [], strlen($rootPath), null))
                    ->send($e); // Display screen

                exit(); // Kill execution
            }

            throw $e;
        }

        return static::$instance;
    }

    /**
     * AppKernel constructor.
     * @param array $options
     * @param string $env
     * @throws AppKernelException
     */
    private function __construct(array $options, string $env)
    {
        // Development mode
        $dev = $options["dev"] ?? $dev["development"] ?? null;
        if (!is_bool($dev)) {
            throw new BootstrapException('Option "dev" must be of type boolean');
        }

        $this->dev = $dev;

        // Root directory
        $rootPath = $options["rootPath"] ?? $options["root_path"] ?? $options["root_dir"] ?? null;
        try {
            $rootDirectory = new Directory(strval($rootPath), null, true);
        } catch (DiskException $e) {
            if (!$rootPath) {
                throw new BootstrapException('Invalid or no value for "rootPath" option');
            }
            throw new BootstrapException('App root directory could not be located');
        }

        $this->directories = new Directories($this, $rootDirectory);

        // Configuration
        $loadCachedConfig = $options["loadCachedConfig"] ?? $options["load_cached_config"] ?? null;
        if (!is_bool($loadCachedConfig)) {
            if (!$this->dev) {
                throw new BootstrapException('Invalid value for "loadCachedConfig" option');
            }
        }

        $this->configure($loadCachedConfig, $env); // Bootstrap configuration

        // Error Handler
        $this->errorHandler = new ErrorHandler($this); // Error Handler

        // Components
        $this->dateTime = (new DateTime())
            ->setTimezone($this->config->timeZone());

        $this->databases = new Databases($this); // Databases
        $this->services = new Services($this); // Services
        $this->events = new EventsHandler();
    }

    /**
     * @param bool $loadCached
     * @param string $env
     * @throws AppKernelException
     */
    private function configure(bool $loadCached, string $env): void
    {
        if ($this->dev) { // Dev. mode?
            $loadCached = false; // Always load fresh config
        }

        // Load cached?
        if ($loadCached) {
            try { // Check if cached config exists
                $cachedConfigFile = $this->directories->cache()
                    ->file(sprintf('bootstrap.config.env_%s.php.cache', $env));
            } catch (DiskException $e) {
            }

            if (isset($cachedConfigFile)) {
                try {
                    try {
                        $cachedConfig = $cachedConfigFile->read();
                    } catch (DiskException $e) {
                        throw new ConfigException('Failed to read cached configuration file');
                    }

                    $cachedConfig = unserialize(base64_decode($cachedConfig));
                    if (!$cachedConfig || !$cachedConfig instanceof Config) {
                        throw new ConfigException('Cached configuration file corrupt or incomplete');
                    }

                    // Success
                    $this->config = $cachedConfig;
                } catch (\Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);

                    // Delete cached file...
                    try {
                        $cachedConfigFile->delete();
                    } catch (DiskException $e) {
                        trigger_error('Failed to delete cached configuration file!', E_USER_WARNING);
                    }
                }
            }
        }

        // Compile fresh configuration
        if (!$this->config) {
            $this->config = new Config($this, $env);
        }

        // Save in cache?
        if ($loadCached) {
            try {
                $cacheDirectory = $this->directories->cache();
                $cacheDirectory->write(
                    sprintf('bootstrap.config.env_%s.php.cache', $env),
                    base64_encode(serialize($this->config)),
                    false,
                    true
                );
            } catch (DiskException $e) {
                trigger_error(
                    sprintf('Failed to write bootstrap config file in cache directory. %s', $e->getMessage()),
                    E_USER_WARNING
                );
            }
        }
    }

    /**
     * @return Config
     */
    final public function config(): Config
    {
        return $this->config;
    }

    /**
     * @param string $const
     * @return mixed
     */
    final public function constant(string $const)
    {
        return @constant('static::' . $const);
    }

    /**
     * @return DateTime
     */
    final public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * @return bool
     */
    final  public function dev(): bool
    {
        return $this->dev;
    }

    /**
     * @return Directories
     */
    final public function directories(): Directories
    {
        return $this->directories;
    }

    /**
     * @return ErrorHandler
     */
    final public function errorHandler(): ErrorHandler
    {
        return $this->errorHandler;
    }

    /**
     * @return EventsHandler
     */
    final public function events(): EventsHandler
    {
        return $this->events;
    }

    /**
     * @return Services
     */
    final public function services(): Services
    {
        return $this->services;
    }

    /**
     * @return Cache
     * @throws CacheException
     * @throws ServicesException
     */
    final public function cache(): Cache
    {
        return $this->services->cache();
    }

    /**
     * @return Cipher
     * @throws AppKernelException
     * @throws CipherException
     */
    final public function cipher(): Cipher
    {
        return $this->services->cipher();
    }

    /**
     * @return Translator
     * @throws AppKernelException
     * @throws TranslatorException
     */
    final public function translator(): Translator
    {
        return $this->services->translator();
    }

    /**
     * @return ComelySession
     * @throws AppKernelException
     * @throws SessionException
     */
    final public function session(): ComelySession
    {
        return $this->services->comelySession();
    }

    /**
     * @return Knit
     * @throws AppKernelException
     * @throws KnitException
     */
    final public function knit(): Knit
    {
        return $this->services->knit();
    }

    /**
     * @return Router
     * @throws HttpRouterException
     */
    final public function router(): Router
    {
        return $this->services->router();
    }

    /**
     * @param string $tag
     * @return Database
     * @throws AppKernelException
     * @throws DatabaseException
     */
    final public function db(string $tag = "primary"): Database
    {
        return $this->databases->get($tag);
    }

    /**
     * @return Memory
     */
    final public function memory(): Memory
    {
        if ($this->memory) {
            return $this->memory;
        }

        // new Memory Instance
        $this->memory = new Memory();

        // Caching
        try {
            $cache = $this->cache();
            $this->memory->caching($cache);
        } catch (\Exception $e) {
        }

        return $this->memory;
    }

    /**
     * @return Http
     */
    final public function http(): Http
    {
        if ($this->http) {
            return $this->http;
        }

        $this->http = new Http();
        return $this->http();
    }
}
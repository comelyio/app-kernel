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
use Comely\AppKernel\Exception\ServicesException;
use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Exception\CacheException;
use Comely\IO\Cipher\Cipher;
use Comely\IO\Cipher\Exception\CipherException;
use Comely\IO\Cipher\Keychain\CipherKey;
use Comely\IO\HttpRouter\Exception\HttpRouterException;
use Comely\IO\HttpRouter\Router;
use Comely\IO\Mailer\Agents\SMTP;
use Comely\IO\Mailer\Mailer;
use Comely\IO\Session\ComelySession;
use Comely\IO\Session\Exception\SessionException;
use Comely\IO\Session\Session;
use Comely\IO\Session\Storage\Disk;
use Comely\IO\Translator\Exception\LanguageException;
use Comely\IO\Translator\Exception\TranslatorException;
use Comely\IO\Translator\Translator;
use Comely\Knit\Exception\KnitException;
use Comely\Knit\Knit;

/**
 * Class Services
 * @package Comely\AppKernel
 */
class Services
{
    /** @var AppKernel */
    private $kernel;
    /** @var null|Cache */
    private $cache;
    /** @var null|Cipher */
    private $cipher;
    /** @var null|Session */
    private $session;
    /** @var null|ComelySession */
    private $comelySession;
    /** @var null|Translator */
    private $translator;
    /** @var null|Knit */
    private $knit;
    /** @var null|Router */
    private $router;
    /** @var null|Mailer */
    private $mailer;

    /**
     * Services constructor.
     * @param AppKernel $kernel
     */
    public function __construct(AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return Cache
     * @throws CacheException
     * @throws ServicesException
     * @throws CacheException
     */
    public function cache(): Cache
    {
        if ($this->cache) { // Already registered?
            return $this->cache;
        }

        $cacheConfig = $this->kernel->config()->services()->cache();
        if (!$cacheConfig instanceof AppKernel\Config\Services\Cache) {
            throw ServicesException::ServiceError("cache", 'No configuration found');
        }

        $engine = $cacheConfig->engine();
        switch ($engine) {
            case "redis":
                $engine = Cache::REDIS;
                break;
            case "memcached":
                $engine = Cache::MEMCACHED;
                break;
            default:
                throw ServicesException::ServiceError("cache", 'Bad engine');
        }

        $cache = new Cache();
        $cache->addServer($engine, $cacheConfig->host(), $cacheConfig->port())
            ->setTimeout($cacheConfig->timeOut());

        try {
            $cache->connect();
        } catch (CacheException $e) {
            if ($cacheConfig->terminate() === true) {
                throw $e;
            }

            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        $this->cache = $cache;
        return $this->cache;
    }

    /**
     * @return Translator
     * @throws AppKernelException
     * @throws ServicesException
     * @throws TranslatorException
     */
    public function translator(): Translator
    {
        if ($this->translator) { // Already registered?
            return $this->translator;
        }

        $translatorConfig = $this->kernel->config()->services()->translator();
        if (!$translatorConfig instanceof AppKernel\Config\Services\Translator) {
            throw ServicesException::ServiceError("translator", 'No configuration found');
        }

        $translator = Translator::getInstance();
        $translator->directory($this->kernel->directories()->langs());
        $translator->fallback($translatorConfig->fallBack());

        // Caching?
        $caching = $translatorConfig->caching() ?? false;
        if ($this->kernel->dev()) {
            $caching = false;
        }

        if ($caching) {
            $translator->cacheDirectory($this->kernel->directories()->cache());
        }

        // Current language
        try {
            $currentLanguage = $_COOKIE["COMELYLANG"] ?? $translatorConfig->fallBack() ?? null;
            $translator->language($currentLanguage);
        } catch (LanguageException $e) {
            trigger_error('Failed to set current language', E_USER_WARNING);
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        $this->translator = $translator;
        return $this->translator;
    }

    /**
     * @return Cipher
     * @throws ServicesException
     * @throws CipherException
     */
    public function cipher(): Cipher
    {
        if ($this->cipher) { // Already registered?
            return $this->cipher;
        }

        $cipherConfig = $this->kernel->config()->services()->cipher();
        if (!$cipherConfig instanceof AppKernel\Config\Services\Cipher) {
            throw ServicesException::ServiceError("cipher", 'No configuration found');
        }

        $cipher = new Cipher();
        $count = 0;
        foreach ($cipherConfig->keys() as $tag => $words) {
            $key = new CipherKey(hash("sha256", $words));
            $cipher->keychain()->add($tag, $key); // Append keychain

            if ($count === 0) { // First key?
                $cipher->defaultKey($key); // Set as default
            }

            $count++;
        }

        $this->cipher = $cipher;
        return $this->cipher;
    }

    /**
     * @return Session
     * @throws AppKernelException
     * @throws ServicesException
     * @throws SessionException
     */
    public function sessions(): Session
    {
        if ($this->session) { // Already registered?
            return $this->session;
        }

        $sessionsConfig = $this->kernel->config()->services()->sessions();
        if (!$sessionsConfig instanceof AppKernel\Config\Services\Sessions) {
            throw ServicesException::ServiceError("sessions", 'No configuration found');
        }

        $sessions = new Session(new Disk($this->kernel->directories()->sessions()));
        $cookiePath = $sessionsConfig->cookie()->path() ?? null;
        if (!$cookiePath) {
            $cookiePath = "/";
        }
        $cookieDomain = $sessionsConfig->cookie()->domain() ?? null;
        if (!$cookieDomain) {
            $cookieDomain = sprintf('.%s', $this->kernel->config()->project()->domain());
        }

        $sessions->cookies()->expire($sessionsConfig->cookie()->expire())
            ->path($cookiePath)
            ->domain($cookieDomain)
            ->secure($sessionsConfig->cookie()->secure())
            ->httpOnly($sessionsConfig->cookie()->httpOnly());

        $this->session = $sessions;
        $this->comelySession = $sessions->resume(null, $sessionsConfig->cookie()->name());

        return $this->session;
    }

    /**
     * @return ComelySession
     * @throws AppKernelException
     * @throws ServicesException
     * @throws SessionException
     */
    public function comelySession(): ComelySession
    {
        if ($this->comelySession) { // Already registered?
            return $this->comelySession;
        }

        $this->sessions(); // Load sessions service
        return $this->comelySession();
    }

    /**
     * @return Knit
     * @throws AppKernelException
     * @throws KnitException
     */
    public function knit(): Knit
    {
        if ($this->knit) { // Already registered?
            return $this->knit;
        }

        $knit = new Knit();
        $knit->directories()
            ->compiler($this->kernel->directories()->compiler())
            ->caching($this->kernel->directories()->cache());

        $this->knit = $knit;
        return $this->knit;
    }

    /**
     * @return Router
     * @throws HttpRouterException
     */
    public function router(): Router
    {
        if ($this->router) { // Already registered?
            return $this->router;
        }

        $router = new Router();
        $router->sanitizer()->encoding("utf8");

        $this->router = $router;
        return $this->router;
    }

    /**
     * @return Mailer
     * @throws ServicesException
     * @throws \Comely\IO\Mailer\Exception\MessageException
     */
    public function mailer(): Mailer
    {
        if ($this->mailer) { // Already registered?
            return $this->mailer;
        }

        $mailerConfig = $this->kernel->config()->services()->mailer();
        if (!$mailerConfig instanceof AppKernel\Config\Services\Mailer) {
            throw ServicesException::ServiceError("mailer", 'No configuration found');
        }

        $mailer = new Mailer();
        $mailer->sender()
            ->name($mailerConfig->senderName())
            ->email($mailerConfig->senderEmail());

        // Agent
        if ($mailerConfig->agent() === "smtp") {
            $smtpConfig = $mailerConfig->smtp();
            if (!$smtpConfig instanceof AppKernel\Config\Services\MailerSMTP) {
                throw ServicesException::ServiceError("mailer", 'SMTP agent configuration not found');
            }

            $username = $smtpConfig->username();
            $password = $smtpConfig->password();
            $serverName = $smtpConfig->serverName();

            $smtp = (new SMTP($smtpConfig->host(), $smtpConfig->port(), $smtpConfig->timeOut()))
                ->useTLS($smtpConfig->tls());
            if ($username && $password) { // Authentication credentials
                $smtp->authCredentials($username, $password);
            }

            if ($serverName) { // Server Name
                $smtp->serverName($serverName);
            }

            $mailer->agent($smtp); // Bind agent
        }

        $this->mailer = $mailer;
        return $this->mailer;
    }

    /**
     * @param string $service
     * @return bool
     */
    public function has(string $service): bool
    {
        return property_exists($this, $service) && isset($this->$service) ? true : false;
    }
}
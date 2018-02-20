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

namespace Comely\AppKernel\Config\App\Services;

use Comely\AppKernel\Config\AbstractConfigNode;
use Comely\AppKernel\Exception\ConfigException;
use Comely\Kernel\Toolkit\Time;

/**
 * Class SessionsCookie
 * @package Comely\AppKernel\Config\App\Services
 * @method string name()
 * @method int expire()
 * @method null|string path()
 * @method null|string domain()
 * @method bool secure()
 * @method bool httpOnly()
 */
class SessionsCookie extends AbstractConfigNode
{
    /** @var string */
    private $name;
    /** @var string */
    private $expire;
    /** @var string */
    private $path;
    /** @var string */
    private $domain;
    /** @var bool */
    private $secure;
    /** @var bool */
    private $httpOnly;

    /**
     * SessionsCookie constructor.
     * @param array $cookie
     * @throws ConfigException
     */
    public function __construct(array $cookie)
    {
        // Name
        $name = $cookie["name"] ?? null;
        if (!is_string($name) || !preg_match('/^[a-z0-9\-\_]{3,32}$/i', $name)) {
            throw ConfigException::PropError(
                'services.sessions.cookie',
                'Invalid value for property "name"'
            );
        }

        $this->name = $name;

        // Expiry
        $expire = $cookie["expire"] ?? null;
        if (!is_string($expire) || !preg_match('/^(\s*[1-9]+[0-9]*\s*[d|h|m|s])+$/', $expire)) {
            throw ConfigException::PropError(
                'services.sessions.cookie',
                'Property "expire" value is invalid time in units (i.e. 30d)'
            );
        }

        $expire = Time::unitsToSeconds($expire);
        if (!$expire) {
            throw ConfigException::PropError(
                'services.sessions.cookie.expire',
                'Failed to convert given time in units to int'
            );
        }

        $this->expire = $expire;

        // Path
        $path = $cookie["path"] ?? null;
        if (!is_string($path)) {
            throw ConfigException::PropError('services.sessions.cookie', 'Property "path" must be string or NULL');
        }

        $this->path = $path;

        // Domain
        $domain = $cookie["domain"] ?? null;
        if (!is_string($domain)) {
            throw ConfigException::PropError('services.sessions.cookie', 'Property "domain" must be string or NULL');
        }

        $this->domain = $domain;

        // Secure
        $secure = $cookie["secure"] ?? $cookie["https"] ?? null;
        if (!is_bool($secure)) {
            throw ConfigException::PropError('services.sessions.cookie', 'Property "secure" must be "yes" or "no"');
        }

        $this->secure = $secure;

        // HttpOnly
        $httpOnly = $cookie["http_only"] ?? $cookie["httpOnly"] ?? null;
        if (!is_bool($httpOnly)) {
            throw ConfigException::PropError('services.sessions.cookie', 'Property "http_only" must be "yes" or "no"');
        }

        $this->httpOnly = $httpOnly;
    }
}
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
 * Class MailerSMTP
 * @package Comely\AppKernel\Config\Services
 * @method string host()
 * @method int port()
 * @method int timeOut()
 * @method bool tls()
 * @method null|string auth()
 * @method null|string username()
 * @method null|string password()
 * @method null|string serverName()
 */
class MailerSMTP extends AbstractConfigNode
{
    /** @var string */
    protected $host;
    /** @var int */
    protected $port;
    /** @var int */
    protected $timeOut;
    /** @var bool */
    protected $tls;
    /** @var null|string */
    protected $auth;
    /** @var null|string */
    protected $username;
    /** @var null|string */
    protected $password;
    /** @var null|string */
    protected $serverName;

    /**
     * MailerSMTP constructor.
     * @param array $smtp
     * @throws ConfigException
     */
    public function __construct(array $smtp)
    {
        // Host
        $host = $smtp["host"] ?? null;
        if (!is_string($host)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "host" must be a string');
        }

        $this->host = $host;

        // Port
        $port = $smtp["port"] ?? null;
        if (!is_int($port)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "port" must be an integer');
        }

        $this->port = $port;

        // Timeout
        $timeOut = $smtp["timeout"] ?? $smtp["time_out"] ?? $smtp["timeOut"] ?? null;
        if (!is_int($timeOut)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "timeOut" must be an integer');
        }

        $this->timeOut = $timeOut;

        // TLS
        $useTLS = $smtp["use_tls"] ?? $smtp["tls"] ?? null;
        if (!is_bool($useTLS)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "use_tls" must be "true" or "false"');
        }

        $this->tls = $useTLS;

        // Auth
        $auth = $smtp["auth"] ?? false;
        if (!is_string($auth) && !is_null($auth)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "auth" must be a string or NULL');
        }

        $this->auth = $auth;

        // Username
        $username = $smtp["username"] ?? $smtp["user"] ?? null;
        if (!is_string($username) && !is_null($username)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "username" must be a string or NULL');
        }

        $this->username = $username;

        // Password
        $password = $smtp["password"] ?? $smtp["pass"] ?? null;
        if (!is_string($password) && !is_null($password)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "password" must be a string or NULL');
        }

        $this->password = $password;

        // ServerName
        $serverName = $smtp["server_name"] ?? $smtp["serverName"] ?? null;
        if (!is_string($serverName) && !is_null($serverName)) {
            throw ConfigException::PropError('services.mailer.smtp', 'Property "server_name" must be a string or NULL');
        }

        $this->serverName = $serverName;
    }
}
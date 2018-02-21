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

namespace Comely\AppKernel\Config;

use Comely\AppKernel\Exception\ConfigException;

/**
 * Class Project
 * @package Comely\AppKernel\Config
 * @method string name()
 * @method string domain()
 * @method bool https()
 * @method string url()
 */
class Project extends AbstractConfigNode
{
    /** @var string */
    private $name;
    /** @var string */
    private $domain;
    /** @var bool */
    private $https;
    /** @var string */
    private $url;

    /**
     * Project constructor.
     * @param array $project
     * @throws ConfigException
     */
    public function __construct(array $project)
    {
        // Project Name/title
        $this->name = $project["name"] ?? null;
        if (!$this->name || !is_string($this->name)) {
            throw ConfigException::PropError('project.name', 'Invalid value');
        }

        // Domain name
        $this->domain = $project["domain"] ?? null;
        if (!is_string($this->domain)) {
            throw ConfigException::PropError('project.domain', 'Invalid value');
        }

        if (strtolower(substr($this->domain, 0, 4)) === "www.") {
            $this->domain = substr($this->domain, 4);
        }

        if (!preg_match('/^[a-z0-9\-]+(\.[a-z]{2,8}){1,}$/i', $this->domain)) {
            throw ConfigException::PropError('project.domain', 'Invalid domain name');
        }

        // HTTPS
        $this->https = $project["https"];
        if (!is_bool($this->https)) {
            throw ConfigException::PropError('project.https', 'Invalid value (must be "yes" or "no")');
        }

        // URL
        $this->url = sprintf('%s://%s/', $this->https ? "https" : "http", $this->domain);
    }
}
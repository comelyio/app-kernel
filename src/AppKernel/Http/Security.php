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

namespace Comely\AppKernel\Http;

use Comely\IO\HttpSecurity\CSRF;
use Comely\IO\HttpSecurity\ObfuscatedForms;
use Comely\IO\Session\ComelySession;

/**
 * Class Security
 * @package Comely\AppKernel\Http
 */
class Security
{
    /** @var CSRF */
    private $csrf;
    /** @var ObfuscatedForms */
    private $forms;

    /**
     * Security constructor.
     * @param ComelySession $session
     */
    public function __construct(ComelySession $session)
    {
        $this->csrf = new CSRF($session);
        $this->forms = new ObfuscatedForms($session);
    }

    /**
     * @return CSRF
     */
    public function csrf(): CSRF
    {
        return $this->csrf;
    }

    /**
     * @return CSRF
     */
    public function xsrf(): CSRF
    {
        return $this->csrf;
    }

    /**
     * @return ObfuscatedForms
     */
    public function forms(): ObfuscatedForms
    {
        return $this->forms;
    }
}
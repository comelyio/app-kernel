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

namespace Comely\AppKernel\Http\Response;

use Comely\AppKernel\Http\AppController;

/**
 * Class Messages
 * @package Comely\AppKernel\Http\Response
 */
class Messages
{
    /** @var AppController */
    private $controller;
    /** @var array */
    private $messages;

    /**
     * Messages constructor.
     * @param AppController $controller
     */
    public function __construct(AppController $controller)
    {
        $this->controller = $controller;
        $this->messages = [];
    }

    /**
     * @param string $type
     * @param string $message
     * @param null|string $param
     */
    private function append(string $type, string $message, ?string $param = null): void
    {
        $this->messages[] = [
            "type" => $type,
            "message" => $message,
            "param" => $param
        ];
    }

    /**
     * @param string $message
     * @return Messages
     */
    public function info(string $message): self
    {
        $this->append("info", $message);
        return $this;
    }

    /**
     * @param string $message
     * @return Messages
     */
    public function success(string $message): self
    {
        $this->append("success", $message);
        return $this;
    }

    /**
     * @param string $message
     * @return Messages
     */
    public function warning(string $message): self
    {
        $this->append("warning", $message);
        return $this;
    }

    /**
     * @param string $message
     * @return Messages
     */
    public function danger(string $message): self
    {
        $this->append("danger", $message);
        return $this;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->messages;
    }
}
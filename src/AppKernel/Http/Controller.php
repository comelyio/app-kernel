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

namespace Comely\AppKernel\Http;

use Comely\AppKernel;
use Comely\Kernel\Comely;

/**
 * Class Controller
 * @package Comely\AppKernel\Http
 * @method void start()
 * @method void finish()
 */
abstract class Controller extends \Comely\IO\HttpRouter\Controller
{
    /** @var AppKernel */
    protected $app;

    /**
     * @throws AppKernel\Exception\BootstrapException
     * @throws ControllerException
     */
    public function callback(): void
    {
        // Set AppKernel instance
        $this->app = AppKernel::getInstance();

        // Start controller execution callback
        if (method_exists($this, "start")) {
            $this->start();
        }

        // Controller method
        $controllerMethod = strtolower($this->request()->method());

        // Explicit method name
        $requestedControllerMethod = explode("&", $this->request()->_queryString)[0];
        if (preg_match('/^[a-z0-9\_]+$/i', $requestedControllerMethod)) {
            $controllerMethod .= Comely::PascalCase($requestedControllerMethod);
        }

        if (!method_exists($this, $controllerMethod)) {
            throw new ControllerException(
                sprintf(
                    'Requested method "%s" not found in HTTP controller "%s" class',
                    $controllerMethod,
                    get_called_class()
                )
            );
        }

        // Execute
        try {
            call_user_func([$this, $controllerMethod]);
        } catch (ControllerException $e) {
            $this->error($e->getMessage(), $e->getCode());
        }

        // Finish controller execution callback
        if (method_exists($this, "finish")) {
            $this->finish();
        }
    }

    /**
     * @param string $message
     * @param int|null $code
     */
    abstract public function error(string $message, ?int $code = null): void;
}
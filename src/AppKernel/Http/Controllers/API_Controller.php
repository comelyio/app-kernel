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

namespace Comely\AppKernel\Http\Controllers;

use Comely\AppKernel\Http\AppController;
use Comely\AppKernel\Http\AppControllerException;
use Comely\Kernel\Exception\ComelyException;

/**
 * Class API_Controller
 * @package Comely\AppKernel\Http\Controllers
 */
abstract class API_Controller extends AppController
{
    /**
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\IO\HttpRouter\Exception\ControllerResponseException
     */
    final public function callback(): void
    {
        parent::callback(); // Set AppKernel instance

        // Default response type (despite of ACCEPT header)
        $this->response()->format("application/json");

        // Prepare response
        $this->response()->set("status", false);
        $this->response()->set("message", null);

        // Controller method
        $controllerMethod = strtolower($this->request()->method());

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                throw new AppControllerException(
                    sprintf(
                        'Endpoint "%s" does not support "%s" method',
                        get_called_class(),
                        strtoupper($controllerMethod)
                    )
                );
            }

            $this->onLoad(); // Event callback: onLoad
            call_user_func([$this, $controllerMethod]);
        } catch (ComelyException $e) {
            $this->response()->set("message", $e->getMessage());

            if ($this->app->dev()) {
                $this->response()->set("caught", get_class($e));
                $this->response()->set("file", $e->getFile());
                $this->response()->set("line", $e->getLine());
                $this->response()->set("trace", $this->getExceptionTrace($e));
            }
        }

        if ($this->app->dev()) {
            $this->response()->set("errors", $this->app->errorHandler()->errors()); // Errors
        }

        $this->onFinish(); // Event callback: onFinish
    }

    /**
     * @param \Exception $e
     * @return array
     */
    private function getExceptionTrace(\Exception $e): array
    {
        return array_map(function (array $trace) {
            unset($trace["args"]);
            return $trace;
        }, $e->getTrace());
    }

    /**
     * This method is called right before requested method within controller
     * @return void
     */
    abstract protected function onLoad(): void;

    /**
     * This method is called after executing requested method within controller, both on error or on success
     * @return void
     */
    abstract protected function onFinish(): void;
}
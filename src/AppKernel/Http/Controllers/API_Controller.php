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
        $this->response()->set("success", false);
        $this->response()->set("message", null);

        // Controller method
        $controllerMethod = strtolower($this->request()->method());

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                throw new AppControllerException(
                    sprintf(
                        'Requested method "%s" not found in API controller "%s" class',
                        $controllerMethod,
                        get_called_class()
                    )
                );
            }

            $this->onLoad(); // Event callback: onLoad
            call_user_func([$this, $controllerMethod]);
        } catch (ComelyException $e) {
            $this->response()->set("message", $e->getMessage());

            if ($this->app->dev()) {
                $this->response()->set("trace", $e->getTrace());
            }
        }

        $this->onFinish(); // Event callback: onFinish
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
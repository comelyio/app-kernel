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
use Comely\AppKernel\Http\Security;
use Comely\IO\Session\ComelySession;
use Comely\Kernel\Comely;
use Comely\Kernel\Exception\ComelyException;
use Comely\Knit\Knit;
use Comely\Knit\Template;

/**
 * Class GenericController
 * @package Comely\AppKernel\Http\Controllers
 */
abstract class GenericController extends AppController
{
    /** @var null|Security */
    private $security;

    /**
     * @return ComelySession
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\AppKernel\Exception\ServicesException
     * @throws \Comely\IO\Session\Exception\SessionException
     */
    public function session(): ComelySession
    {
        return $this->app->services()->comelySession();
    }

    /**
     * @return Security
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\AppKernel\Exception\ServicesException
     * @throws \Comely\IO\Session\Exception\SessionException
     */
    public function security(): Security
    {
        if (!$this->security) {
            $this->security = new Security($this->session());
        }

        return $this->security;
    }

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
        $httpRequestMethod = strtolower($this->request()->method());
        $controllerMethod = $httpRequestMethod;

        // Explicit method name
        $queryStringMethod = explode("&", $this->request()->_queryString)[0];
        if (preg_match('/^[a-z0-9\_]+$/i', $queryStringMethod)) {
            $controllerMethod .= Comely::PascalCase($queryStringMethod);

            // If HTTP request method is GET, and assumed controller doesn't exist, default controller is "get()"
            if ($httpRequestMethod === "get" && !method_exists($this, $controllerMethod)) {
                $controllerMethod = "get";
            }
        }

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                throw new AppControllerException(
                    sprintf(
                        'Requested method "%s" not found in HTTP controller "%s" class',
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

    /**
     * Controllers or controllers parents extending this class should use this method to set templates directory
     * and other caching options on Knit instance.
     * @return Knit
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\Knit\Exception\KnitException
     */
    public function knit(): Knit
    {
        return $this->app->knit();
    }

    /**
     * @param string $templateFile
     * @return Template
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\Knit\Exception\KnitException
     */
    public function template(string $templateFile): Template
    {
        return $this->knit()->template($templateFile);
    }

    /**
     * @param Template $template
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\AppKernel\Exception\ServicesException
     * @throws \Comely\IO\HttpRouter\Exception\ControllerResponseException
     * @throws \Comely\IO\Session\Exception\SessionException
     * @throws \Comely\Knit\Exception\CachingException
     * @throws \Comely\Knit\Exception\CompilerException
     * @throws \Comely\Knit\Exception\SandboxException
     * @throws \Comely\Knit\Exception\TemplateException
     */
    public function body(Template $template): void
    {
        $flashMessages = null;
        if ($this->app->services()->has("comelySession")) {
            $flashMessages = $this->session()->flash()->array();
        }

        $template->assign("flashMessages", $flashMessages);
        $template->assign("errors", $this->app->errorHandler()->errors());
        $template->assign("config", $this->app->config()->project()->array());
        $template->assign("client", $this->client);

        // Default response type (despite of ACCEPT header)
        $this->response()->format("text/html");

        // Populate Response "body" param
        $this->response()->body($template->knit());
    }

    /**
     * Alias method
     * @param Template $template
     * @throws \Comely\AppKernel\Exception\AppKernelException
     * @throws \Comely\AppKernel\Exception\ServicesException
     * @throws \Comely\IO\HttpRouter\Exception\ControllerResponseException
     * @throws \Comely\IO\Session\Exception\SessionException
     * @throws \Comely\Knit\Exception\CachingException
     * @throws \Comely\Knit\Exception\CompilerException
     * @throws \Comely\Knit\Exception\SandboxException
     * @throws \Comely\Knit\Exception\TemplateException
     */
    public function display(Template $template): void
    {
        $this->body($template);
    }
}
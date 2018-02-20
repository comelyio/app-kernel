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

/**
 * Class ErrorHandler
 * @package Comely\AppKernel
 */
class ErrorHandler
{
    /** @var AppKernel */
    private $kernel;
    /** @var array */
    private $errors;
    /** @var int */
    private $pathOffset;
    /** @var null|string */
    private $projectName;

    /**
     * ErrorHandler constructor.
     * @param AppKernel $kernel
     */
    public function __construct(AppKernel $kernel)
    {
        $this->kernel = $kernel;
        $this->errors = [];
        $this->pathOffset = strlen($kernel->directories()->root()->path());
        $this->projectName = $kernel->config()->project()->name();

        set_error_handler([$this, "handleError"]);
        set_exception_handler([$this, "handleThrowable"]);
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->errors = [];
    }

    /**
     * @param \Throwable $ex
     */
    public function handleThrowable(\Throwable $ex): void
    {
        $this->screen($ex);
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public function handleError(int $type, string $message, string $file, int $line): bool
    {
        if (error_reporting() === 0) return false;

        // Check if error can be handled
        if (in_array($type, [2, 8, 512, 1024, 2048, 8192, 16384])) {
            $error = [
                "type" => $this->type($type),
                "message" => $message,
                "file" => $this->filePath($file),
                "line" => $line
            ];

            $this->errors[] = $error;
        } else {
            // Proceed to shutdown screen
            try {
                throw new \RuntimeException($message, $type);
            } catch (\RuntimeException $e) {
                $this->screen($e);
            }
        }

        return true;
    }

    /**
     * @param \Throwable $ex
     */
    private function screen(\Throwable $ex): void
    {
        $screen = new AppKernel\ErrorHandler\Screen(
            $this->kernel->dev(),
            $this->errors,
            $this->pathOffset,
            $this->projectName
        );

        $screen->send($ex);
    }

    /**
     * @param string $path
     * @return string
     */
    private function filePath(string $path): string
    {
        return trim(substr($path, $this->pathOffset), DIRECTORY_SEPARATOR);
    }

    /**
     * @param int $type
     * @return string
     */
    private function type(int $type): string
    {
        switch ($type) {
            case 1:
                return "Fatal Error";
            case 2:
                return "Warning";
            case 4:
                return "Parse Error";
            case 8:
                return "Notice";
            case 16:
                return "Core Error";
            case 32:
                return "Core Warning";
            case 64:
                return "Compile Error";
            case 128:
                return "Compile Warning";
            case 256:
                return "Error";
            case 512:
                return "Warning";
            case 1024:
                return "Notice";
            case 2048:
                return "Strict";
            case 4096:
                return "Recoverable";
            case 8192:
                return "Deprecated";
            case 16384:
                return "Deprecated";
            default:
                return "Unknown";
        }
    }
}
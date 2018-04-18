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
use Comely\AppKernel\CLI\Setup;
use Comely\AppKernel\Exception\CLI_Exception;
use Comely\Kernel\Comely;
use Comely\VividShell\ASCII\Banners;
use Comely\VividShell\VividShell;

/**
 * Class CLI
 * @package Comely\AppKernel
 */
class CLI
{
    /** @var AppKernel */
    private $app;
    /** @var null|AppKernel\CLI\AbstractJob */
    private $job;
    /** @var array */
    private $args;
    /** @var array */
    private $flags;
    /** @var mixed */
    private $timeStamp;
    /** @var Setup */
    private $setup;

    /**
     * CLI constructor.
     * @param array $passed
     * @throws CLI_Exception
     */
    public function __construct(array $passed)
    {
        $this->timeStamp = microtime(true);
        $this->args = [];
        $this->flags = [];

        // Process arguments into actual args and flags
        foreach ($passed as $arg) {
            if (!is_string($arg) || !$arg) {
                continue;  // Not a string? (or an empty string) Unlikely, but has to be checked
            }

            // Is an argument?
            if (preg_match('/^[a-z]+([a-z0-9\_]+)?$/i', $arg)) {
                // Arguments are stored in lowercase
                $this->args[] = strtolower($arg);
                continue;
            }

            // Is a flag?
            if (preg_match('/^\-\w+(\=[\w\@\-\.]+)?$/', $arg)) {
                $arg = explode("=", $arg);
                // Flags names are stored lowercase
                // If a flag has value, it is stored as case-sensitive
                $this->flags[substr(strtolower($arg[0]), 1)] = $arg[1] ?? null;
                continue;
            }

            // Bad argument type
            throw new CLI_Exception(
                sprintf('Unacceptable passed argument format near "%s..."', substr($arg, 0, 8))
            );
        }

        // CLI Setup
        $this->setup = new Setup($this->flags);
    }

    /**
     * @return void
     */
    public function bootstrap(): void
    {
        $this->header();

        try {
            // AppKernel Options
            $options = [
                "rootPath" => dirname(__FILE__, 6),
                "dev" => true, // Development mode: FALSE
                "loadCachedConfig" => true, // Cached configuration?
            ];

            if(!class_exists('\App')) {
                throw new CLI_Exception('Class \App not found');
            }

            // Bootstrap AppKernel
            $this->app = call_user_func_array('\App::Bootstrap', [$options, $this->setup->env, true]);

            // Auto-loader for bin/* files
            $rootPath = $this->app->directories()->root()->path();
            spl_autoload_register(function ($class) use ($rootPath) {
                if (preg_match('/^bin\\\\[a-zA-Z0-9\_]+$/', $class)) {
                    $class = explode("\\", $class)[1] ?? null;
                    $path = sprintf('%1$s%3$sbin%3$s%2$s', $rootPath, $class, DIRECTORY_SEPARATOR);
                    if (@is_file($path)) {
                        /** @noinspection PhpIncludeInspection */
                        @include_once($path);
                    }
                }
            });

            $this->introduceApp(); // Introduce App
            $this->run();
        } catch (\Throwable $t) {
            VividShell::Repeat(".", 5, $this->sleep(150));
            VividShell::Print("");
            VividShell::Print("{yellow}Caught:{/} {red}{b}%s{/}", 0, [$t instanceof \Exception ? get_class($t) : "Fatal Error"]);
            VividShell::Print("");
            VividShell::Print($t->getMessage(), 0);
            VividShell::Print("");
            VividShell::Print("{yellow}File:{/} %s", 0, [$t->getFile()]);
            VividShell::Print("{yellow}Line:{/} {cyan}%d", 0, [$t->getLine()]);
            VividShell::Print("");
            VividShell::Print("Debug Backtrace");
            VividShell::Repeat(".", 5, $this->sleep(150));
            VividShell::Print("");

            print $t->getTraceAsString();
            VividShell::Print("");
        }

        $this->footer();
    }

    /**
     * @throws CLI_Exception
     */
    private function run()
    {
        $jobClass = $this->args[0] ?? "console";

        VividShell::Print("Loading Job: ", $this->sleep(0), null, "");
        VividShell::Repeat(".", rand(5, 10), $this->sleep(150), "");
        if (!class_exists($jobClass) || !is_a($jobClass, '\Comely\AppKernel\CLI\AbstractJob', true)) {
            VividShell::Print(" {red}{b}{invert} %s {/}", $this->sleep(0), [$jobClass]);
            VividShell::Print("");

            throw new CLI_Exception(sprintf('Job class "%s" not found', $jobClass));
        }

        VividShell::Print(" {green}{b}{invert} %s {/}", $this->sleep(0), [$jobClass]);
        VividShell::Print("");

        $this->job = new $jobClass($this->app, $this);
        $this->job->run();
    }

    /**
     * @param int $ms
     * @return int
     */
    public function sleep(int $ms = 0): int
    {
        return $this->setup->noSleep ? 0 : $ms;
    }

    /**
     * @return void
     */
    private function introduceApp(): void
    {
        // Loaded components
        // Application Title
        VividShell::Repeat("~", 5, $this->sleep(150));
        $lineNum = 0;
        array_map(function ($line) use (&$lineNum) {
            $lineNum++;
            $eol = $lineNum !== 2 ? PHP_EOL : "";
            VividShell::Print("{magenta}{b}" . $line, $this->sleep(0), null, $eol);
            if ($lineNum === 2) {
                VividShell::Print(' {gray}v%s', $this->sleep(0), [@constant("App::VERSION") ?? "0.0.0"]);
            }
        }, Banners::Digital(@constant("App::NAME") ?? "Untitled App"));

        VividShell::Repeat("~", 5, $this->sleep(150));
        VividShell::Print("");
    }

    /**
     * @return void
     */
    private function header(): void
    {
        VividShell::Print("{invert}{yellow} Comely IO {/} {grey}v%s", $this->sleep(300), [Comely::VERSION]);
        VividShell::Print("{invert}{magenta} Comely App Kernel {/} {gray}v%s", $this->sleep(300), [AppKernel::VERSION]);
        VividShell::Print("");
    }

    /**
     * @return void
     */
    private function footer(): void
    {
        // Completed
        VividShell::Print("");
        VividShell::Repeat("~", 5, $this->sleep(0));

        // Errors
        $errors = $this->app->errorHandler()->errors();
        if (count($errors)) {
            VividShell::Print("{red}{b}%d{/}{red} Errors were triggered", $this->sleep(100), [count($errors)]);
            VividShell::Print("");

            foreach ($errors as $error) {
                VividShell::Print(
                    '[{b}%s{/}] %s in {u}%s{/} on line {b}%d{/}',
                    $this->sleep(100),
                    [
                        strtoupper($error["type"]),
                        $error["message"],
                        $error["file"],
                        $error["line"] ?? -1
                    ]
                );
            }
        } else {
            VividShell::Print("{gray}No errors were triggered", $this->sleep(0));
        }

        // Footprint
        VividShell::Print("");
        VividShell::Print(
            "Execution Time: {gray}%s seconds{/}",
            $this->sleep(0),
            [
                number_format((microtime(true) - $this->timeStamp), 4)
            ]
        );
        VividShell::Print(
            "Memory Usage: {gray}%sMB{/} of {gray}%sMB{/}",
            $this->sleep(0),
            [
                round((memory_get_usage(false) / 1024) / 1024, 2),
                round((memory_get_usage(true) / 1024) / 1024, 2)
            ]
        );
    }
}
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

namespace Comely\AppKernel\ErrorHandler;

use Comely\AppKernel;
use Comely\Kernel\Comely;

/**
 * Class Screen
 * @package Comely\AppKernel\ErrorHandler
 */
class Screen
{
    /** @var string */
    private $version;
    /** @var bool */
    private $dev;
    /** @var array */
    private $errors;
    /** @var int */
    private $pathOffset;
    /** @var null|string */
    private $projectName;

    /**
     * Screen constructor.
     * @param bool $dev
     * @param array $errors
     * @param int $pathOffset
     * @param null|string $projectName
     */
    public function __construct(bool $dev, array $errors, int $pathOffset, ?string $projectName)
    {
        $this->version = AppKernel::VERSION;
        $this->dev = $dev;
        $this->errors = $errors;
        $this->pathOffset = $pathOffset;
        $this->projectName = $projectName;
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
     * @param \Throwable $ex
     */
    public function send(\Throwable $ex)
    {
        // Base Exception name
        $exceptionBaseName = Comely::baseClassName(get_class($ex));

        // Back traces
        $backTraces = [];
        foreach ($ex->getTrace() as $trace) {
            $function = $trace["function"] ?? null;
            $class = $trace["class"] ?? null;
            $type = $trace["type"] ?? null;
            $file = $trace["file"] ?? null;
            $line = $trace["line"] ?? null;

            if ($file && is_string($file) && $line) {
                $file = $this->filePath($file);
                $method = $function;
                if ($class && $type) {
                    $method = $class . $type . $function;
                }

                $traceString = sprintf('“<u>%s</u>” on line # <u>%d</u>', $file, $line);
                if ($method) {
                    $traceString = sprintf('Method <u>%s()</u> in file', $method) . $traceString;
                }

                $backTraces[] = $traceString;
            }
        }
        unset($trace, $traceString, $function, $class, $type, $file, $line);

        ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous">
    <link rel="stylesheet" href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <title><?php if ($this->projectName) printf('%s &mdash; ', $this->projectName); ?>Comely Framework Kernel
        v<?php print $this->version; ?></title>
    <style type="text/css">
        body {
            font-size: 1.2em;
            font-weight: 300;
        }

        a.info {
            color: #fff;
        }

        a.info:hover {
            color: #fffacf;
        }

        div.screen-brand {
            background-color: #1781ad;
            padding: 20px 0;
        }

        div.screen-brand h1, h2, h3, h4, h5 {
            margin: 0;
        }

        div.screen-head {
            background-color: #179ECA;
            color: #fff;
            padding: 40px 0;
            word-wrap: normal;
        }

        div.screen-head .text-normal {
            color: #fff;
        }

        div.screen-head-icon {
            font-size: 200px;
            color: #166f98;
        }

        div.screen-head h2 small {
            font-size: 1.5rem;
        }

        div.screen-body {
            padding: 60px 0;
        }

        .card {
            border-radius: 0;
        }
    </style>
</head>
<body>

<div class="screen-brand">
    <div class="container">
        <div class="row">
            <div class="col text-right">
                <h5 class="font-weight-light">
                    <a href="https://github.com/comelyio/" target="_blank" class="info">Comely App Kernel
                        v<?php print $this->version; ?></a>
                </h5>
            </div>
        </div>
    </div>
</div>
<div class="screen-head">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="font-weight-normal animated tada">
                    Opps! This web app has came to an unexpected halt...
                </h1>
                <h2 class="font-weight-light mt-5 mb-5">
                    <small class="font-weight-light">
                        <i>Caught “<a href="javascript:void(0);"
                                      class="info"><?php print $exceptionBaseName; ?></a>”</i>
                    </small>
                    <br>
                    <?php print $ex->getMessage(); ?>
                </h2>
                <h4 class="font-weight-light text-normal">
                    in file <i class="icon ion-folder"></i> <a href="javascript:void(0);"
                                                               class="info"><?php print $this->filePath($ex->getFile()); ?></a>
                    on line # <a href="javascript:void(0);" class="info"><?php print $ex->getLine(); ?></a>
                </h4>
            </div>
            <div class="col-lg-4 d-none d-lg-block d-xl-block">
                <div class="screen-head-icon text-center animated pulse">
                    <i class="icon ion-social-github"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="screen-body">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="icon ion-magnet"></i>
                        Debug backtrace
                    </div>
                    <?php if (!$this->dev) { ?>
                        <div class="card-body">
                            <p class="card-text">Debug backtrace is not available in PRODUCTION mode</p>
                        </div>
                    <?php } else { ?>
                        <ul class="list-group">
                            <?php foreach ($backTraces as $backTrace) { ?>
                                <li class="list-group-item"><?php print $backTrace; ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="icon ion-bug"></i>
                        Triggered Errors
                    </div>
                    <?php if (count($this->errors)) { ?>
                        <ul class="list-group">
                            <?php foreach ($this->errors as $error) {
                                ?>
                                <li class="list-group-item">
                                    <?php printf(
                                        '[%s] %s in file <u>%s</u> on line # <u>%d</u>',
                                        strtoupper(strval($error["type"] ?? "")),
                                        $error["message"] ?? "",
                                        $error["file"] ?? "",
                                        intval($error["line"] ?? -1)
                                    ); ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    <?php } else { ?>
                        <div class="card-body">
                            <p class="card-text">No error messages were triggered</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="//code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>
        <?php
    }
}
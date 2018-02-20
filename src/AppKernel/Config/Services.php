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

use Comely\AppKernel\Config\App\Services\Cache;
use Comely\AppKernel\Config\App\Services\Cipher;
use Comely\AppKernel\Config\App\Services\Mailer;
use Comely\AppKernel\Config\App\Services\Sessions;
use Comely\AppKernel\Config\App\Services\Translator;

/**
 * Class Services
 * @package Comely\AppKernel\Config
 * @method null|Cache cache()
 * @method null|Cipher cipher()
 * @method null|Mailer mailer()
 * @method null|Sessions sessions()
 * @method null|Translator translator()
 */
class Services extends AbstractConfigNode
{
    /** @var null|Cache */
    private $cache;
    /** @var null|Cipher */
    private $cipher;
    /** @var null|Mailer */
    private $mailer;
    /** @var null|Sessions */
    private $sessions;
    /** @var null|Translator */
    private $translator;

    /**
     * Services constructor.
     * @param array $services
     */
    public function __construct(array $services)
    {
        // Cache
        $cache = $services["cache"] ?? null;
        if (is_array($cache)) {
            $this->cache = new Cache($cache);
        }

        // Cipher
        $cipher = $services["cipher"] ?? null;
        if (is_array($cipher)) {
            $this->cipher = new Cipher($cipher);
        }

        // Mailer
        $mailer = $services["mailer"] ?? null;
        if (is_array($mailer)) {
            $this->mailer = new Mailer($mailer);
        }

        // Sessions
        $sessions = $services["sessions"] ?? null;
        if (is_array($sessions)) {
            $this->sessions = new Sessions($sessions);
        }

        // Translator
        $translator = $services["translator"] ?? null;
        if (is_array($translator)) {
            $this->translator = new Translator($translator);
        }
    }
}
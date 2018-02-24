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

namespace Comely\AppKernel\Http\Security;

use Comely\AppKernel\Http\AppControllerException;
use Comely\IO\Session\ComelySession;

/**
 * Class CSRF
 * @package Comely\AppKernel\Http\Security
 */
class CSRF
{
    /** @var ComelySession */
    private $session;

    /**
     * CSRF constructor.
     * @param ComelySession $session
     */
    public function __construct(ComelySession $session)
    {
        $this->session = $session;
    }

    /**
     * @param int $expire
     * @return string
     * @throws AppControllerException
     * @throws \Comely\IO\Session\Exception\ComelySessionException
     */
    public function get(int $expire = 0): string
    {
        return $this->current() ?? $this->generate($expire);
    }

    /**
     * @return null|string
     */
    public function current(): ?string
    {
        $token = $this->session->meta()->get("csrf_token") ?? null;
        $expire = $this->session->meta()->get("csrf_token_expire") ?? null;

        if (is_int($expire) && $expire > 0) {
            if (time() >= $expire) { // Expired?
                $this->session->meta()->delete("csrf_token")
                    ->delete("csrf_token_expire");

                return null;
            }
        }

        return $token;
    }

    /**
     * @param int $expire
     * @return string
     * @throws AppControllerException
     * @throws \Comely\IO\Session\Exception\ComelySessionException
     */
    public function generate(int $expire = 0): string
    {
        // Expiring token?
        if ($expire > 0) {
            $expire += time();
        }

        // Generate
        try {
            $randomBytes = random_bytes(20); // 20 bytes = 40 hexits
        } catch (\Exception $e) {
            throw new AppControllerException('Failed to generate a security CSRF token');
        }

        // Store
        $token = bin2hex($randomBytes);
        $this->session->meta()->set("csrf_token", $token)
            ->set("csrf_token_expire", ($expire > 0) ? $expire + time() : 0);


        // Return token
        return $token;
    }

    /**
     * @param string $given
     * @return bool
     * @throws AppControllerException
     * @throws \Comely\IO\Session\Exception\ComelySessionException
     */
    public function verify(string $given): bool
    {
        return hash_equals($this->get(), $given);
    }
}
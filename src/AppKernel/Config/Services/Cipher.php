<?php
/**
 * This file is part of Comely App Kernel package.
 * https://github.com/comelyio/app-kernel
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\AppKernel\Config\Services;

use Comely\AppKernel\Config\AbstractConfigNode;
use Comely\AppKernel\Exception\ConfigException;

/**
 * Class Cipher
 * @package Comely\AppKernel\Config\Services
 */
class Cipher extends AbstractConfigNode
{
    /** @var array */
    private $keys;

    /**
     * Cipher constructor.
     * @param array $cipher
     * @throws ConfigException
     */
    public function __construct(array $cipher)
    {
        $this->keys = [];

        // Keys
        $keys = $cipher["keys"] ?? null;
        if (!is_array($keys) && !is_null($keys)) {
            throw ConfigException::PropError('services.cipher', 'Property "keys" must be of an object or NULL');
        }

        foreach ($keys as $tag => $words) {
            if (!is_string($tag) || !is_string($words)) {
                throw ConfigException::PropError('services.cipher', 'Object contains an invalid key name/words');
            }

            $this->keys[$tag] = $words;
        }
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return $this->keys;
    }
}
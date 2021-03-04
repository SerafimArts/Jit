<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit;

use JetBrains\PhpStorm\Immutable;

#[Immutable, \Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class ImportFunction
{
    /**
     * @param string $name
     * @param string $type
     * @param array<string> $args
     */
    public function __construct(
        public string $name,
        public string $type = 'void',
        public array $args = [],
    ) {}
}

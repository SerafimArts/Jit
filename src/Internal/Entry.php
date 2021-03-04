<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Internal;

use FFI\CData;
use Serafim\Jit\ImportFunction;

/**
 * @internal Entry is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\Jit
 */
final class Entry
{
    /**
     * @var string
     */
    private const ERROR_FUNCTION_IMPORT = 'Function "%s" was not imported';

    /**
     * @var array<CData & callable>
     */
    private array $functions = [];

    /**
     * @param Engine $engine
     * @param iterable<ImportFunction> $functions
     */
    public function __construct(Engine $engine, iterable $functions)
    {
        foreach ($functions as $function) {
            $this->functions[$function->name] = $engine->getFunction(
                $function->name,
                $function->type,
                $function->args,
            );
        }
    }

    /**
     * @param string $function
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $function, array $arguments): mixed
    {
        if (! isset($this->functions[$function])) {
            throw new \BadFunctionCallException(\sprintf(self::ERROR_FUNCTION_IMPORT, $function));
        }

        return $this->functions[$function](...$arguments);
    }
}

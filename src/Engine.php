<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit;

use FFI\CData;
use JetBrains\PhpStorm\Pure;

final class Engine
{
    /**
     * @var \FFI
     */
    private \FFI $llvm;

    /**
     * @var CData<"LLVMExecutionEngineRef">
     */
    private CData $engine;

    /**
     * @var CData<"LLVMContextRef">
     */
    private CData $context;

    /**
     * @param \FFI $llvm
     * @param CData<"LLVMExecutionEngineRef"> $engine
     * @param CData<"LLVMContextRef"> $context
     */
    public function __construct(\FFI $llvm, CData $engine, CData $context)
    {
        $this->llvm = $llvm;
        $this->engine = $engine;
        $this->context = $context;
    }

    /**
     * @param string $type
     * @param array<string> $arguments
     * @return string
     */
    #[Pure]
    private function signature(string $type, array $arguments): string
    {
        $argumentsString = $arguments === [] ? 'void' : \implode(',', $arguments);

        return \sprintf('%s (*)(%s)', $type, $argumentsString);
    }

    /**
     * @param string $name
     * @param string $type
     * @param array<string> $arguments
     * @return CData|callable
     */
    public function getFunction(string $name, string $type, array $arguments = []): CData|callable
    {
        return $this->llvm->cast($this->signature($type, $arguments),
            $this->llvm->LLVMGetFunctionAddress($this->engine, $name)
        );
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->llvm->LLVMContextDispose($this->context);
    }
}

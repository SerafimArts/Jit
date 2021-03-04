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
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use Serafim\Jit\Compiler\LLVMCodeGenFileType;
use Serafim\Jit\Exception\JitException;

/**
 * @internal Engine is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\Jit
 */
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
     * @var Error
     */
    private Error $error;

    /**
     * @var CData
     */
    private CData $module;

    /**
     * @param \FFI $llvm
     * @param CData<"LLVMModuleRef"> $module
     * @param CData<"LLVMExecutionEngineRef"> $engine
     * @param CData<"LLVMContextRef"> $context
     */
    public function __construct(\FFI $llvm, CData $module, CData $engine, CData $context)
    {
        $this->llvm = $llvm;
        $this->engine = $engine;
        $this->context = $context;

        $this->error = new Error($llvm);
        $this->module = $module;
    }

    /**
     * @param string $type
     * @param array<string> $arguments
     * @return string
     */
    #[Pure]
    private function signature(
        string $type,
        array $arguments
    ): string {
        $argumentsString = $arguments === [] ? 'void' : \implode(',', $arguments);

        return \sprintf('%s (*)(%s)', $type, $argumentsString);
    }

    /**
     * @param string $file
     * @param int $type
     */
    public function build(
        string $file,
        #[ExpectedValues(valuesFromClass: LLVMCodeGenFileType::class)]
        int $type = LLVMCodeGenFileType::LLVM_ASSEMBLY_FILE,
    ): void {
        $target = $this->llvm->LLVMGetExecutionEngineTargetMachine($this->engine);

        if ($target === null) {
            throw new JitException('Compiler target was not installed');
        }

        $status = $this->llvm->LLVMTargetMachineEmitToFile($target, $this->module, $file, $type, $this->error->message);
        $this->error->assert($status);
    }

    /**
     * @param string $name
     * @param string $type
     * @param array<string> $arguments
     * @return CData|callable
     */
    public function getFunction(string $name, string $type, array $arguments = []): CData|callable
    {
        $addr = $this->llvm->LLVMGetFunctionAddress($this->engine, $name);

        if ($addr === null) {
            throw new \InvalidArgumentException(\sprintf('Function "%s" was not defined', $name));
        }

        return $this->llvm->cast($this->signature($type, $arguments), $addr);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->llvm->LLVMContextDispose($this->context);
    }
}

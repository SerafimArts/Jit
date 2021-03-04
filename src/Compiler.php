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
use Serafim\Jit\Compiler\LLVMVerifierFailureAction;
use Serafim\Jit\Exception\JitException;
use Serafim\Jit\Internal\Engine;
use Serafim\Jit\Internal\Entry;
use Serafim\Jit\Internal\Error;
use Serafim\Jit\Target\TargetInterface;

class Compiler
{
    /**
     * @var object<\FFI>
     */
    private object $llvm;

    /**
     * @var CData<"LLVMModuleRef">
     */
    private CData $module;

    /**
     * @var Error
     */
    private Error $error;

    /**
     * @param \FFI $ffi
     */
    public function __construct(\FFI $ffi)
    {
        $this->llvm = $ffi;
        $this->error = new Error($this);

        $this->module = $this->llvm->new('LLVMModuleRef', false);

        // void LLVMLinkInMCJIT(void);
        $this->llvm->LLVMLinkInMCJIT();
    }

    /**
     * @param string $library
     * @return static
     */
    public static function fromLibrary(string $library): self
    {
        $headers = \file_get_contents(__DIR__ . '/../resources/llvm.h');

        return new static(\FFI::cdef($headers, $library));
    }

    /**
     * @param string $type
     * @return CData
     */
    public function new(string $type): CData
    {
        return $this->llvm->new($type);
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->llvm->$method(...$args);
    }

    /**
     * @param TargetInterface $target
     * @return $this
     */
    public function target(TargetInterface $target): self
    {
        $target->load($this->llvm);

        return $this;
    }

    /**
     * @param string $string
     * @param bool $owned
     * @return CData
     */
    private function string(string $string, bool $owned = true): CData
    {
        $length = \strlen($nullTerminated = $string . "\0");
        $instance = \FFI::new("char[$length]", $owned);

        \FFI::memcpy($instance, $nullTerminated, $length);

        return $instance;
    }

    /**
     * <code>
     *  LLVMMemoryBufferRef LLVMCreateMemoryBufferWithMemoryRangeCopy(
     *      char *InputData,
     *      size_t InputDataLength,
     *      char *BufferName
     *  );
     * </code>
     *
     * @param string $code
     * @return CData<"LLVMMemoryBufferRef">
     */
    private function loadString(string $code): CData
    {
        $unmanaged = $this->string($code, false);

        return $this->llvm->LLVMCreateMemoryBufferWithMemoryRangeCopy($unmanaged, \strlen($code) + 1, 'Source');
    }

    /**
     * <code>
     *  LLVMBool LLVMCreateMemoryBufferWithContentsOfFile(
     *      char *Path,
     *      LLVMMemoryBufferRef *OutMemBuf,
     *      char **OutMessage
     *  );
     * </code>
     *
     * @param string $file
     * @return CData<"LLVMMemoryBufferRef">
     */
    private function loadFile(string $file): CData
    {
        $memory = $this->llvm->new('LLVMMemoryBufferRef');

        $status = $this->llvm->LLVMCreateMemoryBufferWithContentsOfFile($file, \FFI::addr($memory), $this->error->message);
        $this->error->assert($status);

        return $memory;
    }

    /**
     * @param callable $handler
     * @return Entry|object
     * @throws \ReflectionException
     */
    public function entry(callable $handler): Entry
    {
        $engine = $this->compile($handler());

        return new Entry($engine, $this->getImportFunctions($handler));
    }

    /**
     * @param callable|object $handler
     * @return iterable<ImportFunction>
     * @throws \ReflectionException
     */
    private function getImportFunctions(callable $handler): iterable
    {
        $reflection = \is_object($handler) && ! $handler instanceof \Closure
            ? new \ReflectionObject($handler)
            : new \ReflectionFunction($handler)
        ;

        $attributes = $reflection->getAttributes(ImportFunction::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attr) {
            yield $attr->newInstance();
        }
    }

    /**
     * @param string $code
     * @return Engine
     */
    public function compile(string $code): Engine
    {
        return $this->getExecutionEngine($this->loadString($code));
    }

    /**
     * @param string $file
     * @return Engine
     */
    public function file(string $file): Engine
    {
        return $this->getExecutionEngine($this->loadFile($file));
    }

    /**
     * @param CData<"LLVMMemoryBufferRef"> $memory
     * @return CData<"LLVMExecutionEngineRef">
     */
    private function getExecutionEngine(CData $memory): Engine
    {
        // LLVMExecutionEngineRef
        $engine = $this->llvm->new('LLVMExecutionEngineRef');

        // LLVMContextRef LLVMContextCreate(void);
        $context = $this->llvm->LLVMContextCreate();

        // LLVMBool LLVMParseIRInContext(LLVMContextRef ContextRef, LLVMMemoryBufferRef MemBuf, LLVMModuleRef *OutM, char **OutMessage);
        $status = $this->llvm->LLVMParseIRInContext($context, $memory, \FFI::addr($this->module), $this->error->message);
        $this->error->assert($status);

        // LLVMBool LLVMVerifyModule(LLVMModuleRef M, LLVMVerifierFailureAction Action, char **OutMessage);
        $action = LLVMVerifierFailureAction::LLVM_RETURN_STATUS_ACTION;
        $status = $this->llvm->LLVMVerifyModule($this->module, $action, $this->error->message);
        $this->error->assert($status);

        // LLVMBool LLVMCreateExecutionEngineForModule(LLVMExecutionEngineRef *OutEE, LLVMModuleRef M, char **OutError);
        $status = $this->llvm->LLVMCreateExecutionEngineForModule(\FFI::addr($engine), $this->module, $this->error->message);
        $this->error->assert($status);

        return new Engine($this->llvm, $this->module, $engine, $context);
    }
}

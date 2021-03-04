<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Target;

class GenericTarget implements TargetInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param object $llvm
     */
    public function load(object $llvm): void
    {
        $this->call($llvm, 'TargetInfo');
        $this->call($llvm, 'Target');
        $this->call($llvm, 'TargetMC');
        $this->call($llvm, 'AsmParser');
        $this->call($llvm, 'AsmPrinter');
    }

    /**
     * @param object $llvm
     * @param string $suffix
     */
    private function call(object $llvm, string $suffix): void
    {
        $function = "LLVMInitialize$this->name$suffix";

        $llvm->$function();
    }
}

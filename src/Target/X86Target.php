<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Target;

class X86Target implements TargetInterface
{
    /**
     * @param object $llvm
     */
    public function load(object $llvm): void
    {
        $llvm->LLVMInitializeX86TargetInfo();
        $llvm->LLVMInitializeX86Target();
        $llvm->LLVMInitializeX86TargetMC();
        $llvm->LLVMInitializeX86AsmParser();
        $llvm->LLVMInitializeX86AsmPrinter();
    }
}

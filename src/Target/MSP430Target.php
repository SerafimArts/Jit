<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Target;

class MSP430Target implements TargetInterface
{
    /**
     * @param object $llvm
     */
    public function load(object $llvm): void
    {
        $llvm->LLVMInitializeMSP430TargetInfo();
        $llvm->LLVMInitializeMSP430Target();
        $llvm->LLVMInitializeMSP430TargetMC();
        $llvm->LLVMInitializeMSP430AsmParser();
        $llvm->LLVMInitializeMSP430AsmPrinter();
    }
}

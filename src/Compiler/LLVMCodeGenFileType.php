<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Compiler;

final class LLVMCodeGenFileType
{
    public const LLVM_ASSEMBLY_FILE = 0x00;
    public const LLVM_OBJECT_FILE = 0x01;
}

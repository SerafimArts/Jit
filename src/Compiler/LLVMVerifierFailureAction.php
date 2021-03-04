<?php

/**
 * This file is part of Jit package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Jit\Compiler;

final class LLVMVerifierFailureAction
{
    public const LLVM_ABORT_PROCESS_ACTION = 0x00;
    public const LLVM_PRINT_MESSAGE_ACTION = 0x01;
    public const LLVM_RETURN_STATUS_ACTION = 0x02;
}

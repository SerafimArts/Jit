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
use Serafim\Jit\Compiler;
use Serafim\Jit\Exception\JitException;

/**
 * @internal Error is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\Jit
 */
class Error
{
    /**
     * @var CData
     */
    public CData $message;

    /**
     * @var object
     */
    private object $llvm;

    /**
     * @param object $llvm
     */
    public function __construct(object $llvm)
    {
        $this->llvm = $llvm;
        $this->message = \FFI::addr(\FFI::new('char*'));
    }

    /**
     * @param int $status
     */
    public function assert(int $status): void
    {
        if ($status !== 0) {
            $this->llvm->LLVMDisposeMessage($this->message[0]);

            throw new JitException(\FFI::string($this->message[0]));
        }
    }
}

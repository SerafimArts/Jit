<?php

use Serafim\Jit\Compiler;
use Serafim\Jit\ImportFunction;
use Serafim\Jit\Target\X86Target;

require __DIR__ . '/vendor/autoload.php';

$llvm = Compiler::fromLibrary('C:/Development/LLVM/bin/LLVM-C.dll');
$llvm->target(new X86Target());

$entry = $llvm->entry(
    #[ImportFunction(name: 'main', type: 'int')]
    fn() => <<<'asm'
    define i32 @main() {
        ret i32 42
    }
asm
);

echo $entry->main();

<?php

use Serafim\Jit\Compiler;
use Serafim\Jit\ImportFunction;
use Serafim\Jit\Target\X86Target;

require __DIR__ . '/vendor/autoload.php';

$llvm = Compiler::fromLibrary('C:/Development/LLVM/bin/LLVM-C.dll');
$llvm->target(new X86Target());

$entry = $llvm->entry(
    #[ImportFunction(name: 'get_int', type: 'int')]
    #[ImportFunction(name: 'add_five', type: 'int', args: ['int'])]
    fn()=> <<<'asm'
    define i32 @get_int() {
        ret i32 42
    }
    
    define i32 @add_five(i32) #0 {
        %2 = alloca i32, align 4
        store i32 %0, i32* %2, align 4
        %3 = load i32, i32* %2, align 4
        %4 = add nsw i32 %3, 5
        ret i32 %4
    }
    asm
);

echo $entry->get_int() . "\n"; // 42
echo $entry->add_five(5) . "\n"; // 5 + 5

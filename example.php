<?php

use Serafim\Jit\Compiler;
use Serafim\Jit\Target\X86Target;

require __DIR__ . '/vendor/autoload.php';

// TODO (path to llvm lib)
$llvm = Compiler::fromLibrary('C:/Development/LLVM/bin/LLVM-C.dll');
$llvm->target(new X86Target());

// Example 1
$code1 = $llvm->compile('define i32 @main() { ret i32 42 }');
$code2 = $llvm->compile('define i32 @main() { ret i32 23 }');

echo ($code1->getFunction('main', 'int'))() . "\n";
echo ($code2->getFunction('main', 'int'))() . "\n";

// Example 2
$code = $llvm->compile(<<<'asm'
    define i32 @add_five(i32) #0 {
        %2 = alloca i32, align 4
        store i32 %0, i32* %2, align 4
        %3 = load i32, i32* %2, align 4
        %4 = add nsw i32 %3, 5
        ret i32 %4
    }
asm);

$addFive = $code->getFunction('add_five', 'int', ['int']);

echo $addFive(5);

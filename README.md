# PHP Jit Compiler

## Configuration

```php
$llvm = \Serafim\Jit\Compiler::fromLibrary('/path/to/llvm.so');
$llvm->target(new \Serafim\Jit\Target\X86Target());
```

## Example: Return Int

```php
$code = $llvm->compile('define i32 @main() { ret i32 42 }');

echo ($code->getFunction('main', 'int'))() . "\n";
// 42
```

## Example #2: Add Five

```php
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
```

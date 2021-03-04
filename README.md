# PHP Jit Compiler

## Configuration

```php
$llvm = \Serafim\Jit\Compiler::fromLibrary('/path/to/llvm.so');
$llvm->target(new \Serafim\Jit\Target\X86Target());
```

## Example

```php
use Serafim\Jit\Compiler;
use Serafim\Jit\ImportFunction;
use Serafim\Jit\Target\X86Target;

$llvm = Compiler::fromLibrary('/path/to/llvm.so');
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
```

## Example #2: Compile Sources

```php
/** @var \Serafim\Jit\Compiler $llvm */
$entry = $llvm->compile(<<<'asm'
    define i32 @get_int() { ... }
    define i32 @add_five(i32) #0 { ... }
asm);

$get_int = $entry->getFunction('get_int', 'int');
$add_five = $entry->getFunction('add_five', 'int', ['int']);
```

## Example #3: Compile File

```php
/** @var \Serafim\Jit\Compiler $llvm */
$entry = $llvm->file(__DIR__ . '/path/to/file.ll');
```

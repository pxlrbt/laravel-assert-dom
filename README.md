# Laravel Assert DOM

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pxlrbt/laravel-assert-dom.svg?include_prereleases)](https://packagist.org/packages/pxlrbt/laravel-assert-dom)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/pxlrbt/laravel-assert-dom.svg)](https://packagist.org/packages/pxlrbt/laravel-assert-dom)

Fluent DOM assertions with Pest-like syntax for Laravel tests using PHP 8.4's `Dom\HTMLDocument`.

## Requirements

- PHP 8.4+
- Laravel 11+

## Installation

```bash
composer require pxlrbt/laravel-assert-dom --dev
```

Register the mixin in your `tests/Pest.php`:

```php
use Illuminate\Testing\TestResponse;
use pxlrbt\LaravelAssertDom\AssertDomMixin;

protected function setUp(): void
{
    parent::setUp();

    TestResponse::mixin(new AssertDomMixin());
}
```

## Usage

### Basic Assertions

```php
$response->assertDom('.card', fn (DomAssert $el) => $el
    ->text()->toEqual('Hello World')
    ->attribute('id')->toEqual('main-card')
);
```

### Text Content

```php
$response->assertDom('.message', fn (DomAssert $el) => $el
    ->text()->toEqual('Welcome')
    ->text()->toContain('Welc')
    ->text()->toMatch('/Welcome/')
    ->text()->toBeEmpty()
);
```

### Inner HTML

```php
$response->assertDom('.content', fn (DomAssert $el) => $el
    ->html()->toContain('<strong>')
    ->html()->toEqual('<strong>Test</strong>')
);
```

### Attributes

```php
$response->assertDom('button', fn (DomAssert $el) => $el
    ->attribute('type')->toEqual('submit')
    ->attribute('disabled')->toExist()
    ->attribute('aria-hidden')->not->toExist()
);
```

### Convenience Methods

```php
$response->assertDom('.user', fn (DomAssert $el) => $el
    ->class()->toContain('active')
    ->class()->not->toContain('hidden')
    ->data('user-id')->toEqual('123')
    ->value()->toEqual('John Doe')
);
```

### Tag Name

```php
$response->assertDom('.container', fn (DomAssert $el) => $el
    ->toBeTag('div')
    ->not->toBeTag('span')
);
```

### Child Elements

```php
$response->assertDom('.profile', fn (DomAssert $el) => $el
    ->toHave('.avatar')
    ->not->toHave('.error')
);
```

### Count Children

```php
$response->assertDom('.user-list', fn (DomAssert $el) => $el
    ->toHave('li', 3)
    ->toHaveCount(5)
);
```

### Nested Assertions

```php
$response->assertDom('.user-list', fn (DomAssert $el) => $el
    ->toHave('li', fn (DomAssert $item) => $item
        ->class()->toContain('item')
        ->text()->toContain('User')
    )
);
```

### Negation

All string assertions support negation via `->not`:

```php
$response->assertDom('.profile', fn (DomAssert $el) => $el
    ->text()->toContain('John')
    ->text()->not->toContain('Jane')
    ->class()->not->toContain('hidden')
    ->attribute('disabled')->not->toExist()
);
```

### Assert All Matched Elements

```php
$response->assertDomAll('.card', fn (DomAssert $el) => $el
    ->class()->toContain('card-item')
    ->toHave('.title')
);
```

### Fluent Chaining

All assertions return the parent `DomAssert` instance, enabling fluent chains:

```php
$response->assertDom('.card', fn (DomAssert $el) => $el
    ->toBeTag('div')
    ->attribute('id')->toEqual('main')
    ->class()->toContain('active')
    ->class()->not->toContain('hidden')
    ->data('count')->toEqual('5')
    ->toHave('strong')
    ->not->toHave('.error')
    ->text()->toContain('Hello')
);
```

## License

MIT

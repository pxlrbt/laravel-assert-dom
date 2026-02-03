<?php

declare(strict_types=1);

namespace pxlrbt\LaravelAssertDom\Assertions;

use Illuminate\Testing\Assert as PHPUnit;

class StringAssert
{
    public readonly NegatedStringAssert $not;

    public function __construct(
        private ?string $value,
        private DomAssert $parent,
        private string $context,
    ) {
        $this->not = new NegatedStringAssert($this);
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getParent(): DomAssert
    {
        return $this->parent;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function toEqual(string $expected): DomAssert
    {
        PHPUnit::assertSame(
            $expected,
            $this->value,
            "Expected {$this->context} to equal '{$expected}', but got ".($this->value === null ? 'null' : "'{$this->value}'"),
        );

        return $this->parent;
    }

    public function toContain(string $needle): DomAssert
    {
        PHPUnit::assertNotNull(
            $this->value,
            "Expected {$this->context} to contain '{$needle}', but the value is null",
        );

        PHPUnit::assertStringContainsString(
            $needle,
            $this->value,
            "Expected {$this->context} to contain '{$needle}', but got '{$this->value}'",
        );

        return $this->parent;
    }

    public function toMatch(string $pattern): DomAssert
    {
        PHPUnit::assertNotNull(
            $this->value,
            "Expected {$this->context} to match pattern '{$pattern}', but the value is null",
        );

        PHPUnit::assertMatchesRegularExpression(
            $pattern,
            $this->value,
            "Expected {$this->context} to match pattern '{$pattern}', but got '{$this->value}'",
        );

        return $this->parent;
    }

    public function toBeEmpty(): DomAssert
    {
        PHPUnit::assertTrue(
            $this->value === null || $this->value === '',
            "Expected {$this->context} to be empty, but got '{$this->value}'",
        );

        return $this->parent;
    }

    public function toExist(): DomAssert
    {
        PHPUnit::assertNotNull(
            $this->value,
            "Expected {$this->context} to exist, but it does not",
        );

        return $this->parent;
    }
}

<?php

declare(strict_types=1);

namespace pxlrbt\LaravelAssertDom\Assertions;

use Illuminate\Testing\Assert as PHPUnit;

class NegatedStringAssert
{
    public function __construct(
        private StringAssert $assert,
    ) {
    }

    public function toEqual(string $expected): DomAssert
    {
        PHPUnit::assertNotSame(
            $expected,
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to equal '{$expected}', but it does",
        );

        return $this->assert->getParent();
    }

    public function toContain(string $needle): DomAssert
    {
        PHPUnit::assertNotNull(
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to contain '{$needle}', but the value is null",
        );

        PHPUnit::assertStringNotContainsString(
            $needle,
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to contain '{$needle}', but it does",
        );

        return $this->assert->getParent();
    }

    public function toMatch(string $pattern): DomAssert
    {
        PHPUnit::assertNotNull(
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to match pattern '{$pattern}', but the value is null",
        );

        PHPUnit::assertDoesNotMatchRegularExpression(
            $pattern,
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to match pattern '{$pattern}', but it does",
        );

        return $this->assert->getParent();
    }

    public function toBeEmpty(): DomAssert
    {
        PHPUnit::assertFalse(
            $this->assert->getValue() === null || $this->assert->getValue() === '',
            "Expected {$this->assert->getContext()} not to be empty, but it is",
        );

        return $this->assert->getParent();
    }

    public function toExist(): DomAssert
    {
        PHPUnit::assertNull(
            $this->assert->getValue(),
            "Expected {$this->assert->getContext()} not to exist, but it does with value '{$this->assert->getValue()}'",
        );

        return $this->assert->getParent();
    }
}

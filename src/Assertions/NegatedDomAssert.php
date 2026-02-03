<?php

declare(strict_types=1);

namespace pxlrbt\LaravelAssertDom\Assertions;

use Closure;
use Illuminate\Testing\Assert as PHPUnit;
use PHPUnit\Framework\AssertionFailedError;

class NegatedDomAssert
{
    public function __construct(
        private DomAssert $assert,
    ) {}

    public function toHave(string $selector, int|Closure|null $countOrCallback = null): DomAssert
    {
        $elements = $this->assert->getElement()->querySelectorAll($selector);
        $actual = $elements->count();

        if ($countOrCallback === null) {
            PHPUnit::assertSame(
                0,
                $actual,
                "Expected '{$this->assert->getSelector()}' not to have child element(s) matching '{$selector}', but found {$actual}",
            );
        } elseif (is_int($countOrCallback)) {
            PHPUnit::assertNotSame(
                $countOrCallback,
                $actual,
                "Expected '{$this->assert->getSelector()}' not to have {$countOrCallback} child element(s) matching '{$selector}', but it does",
            );
        } else {
            foreach ($elements as $element) {
                $passed = true;

                try {
                    $countOrCallback(new DomAssert($element, "{$this->assert->getSelector()} {$selector}"));
                } catch (AssertionFailedError) {
                    $passed = false;
                }

                if ($passed) {
                    PHPUnit::fail(
                        "Expected '{$this->assert->getSelector()}' not to have child element matching '{$selector}' that satisfies the given assertions",
                    );
                }
            }
        }

        return $this->assert;
    }

    public function toBeTag(string $tagName): DomAssert
    {
        PHPUnit::assertNotSame(
            strtolower($tagName),
            strtolower($this->assert->getElement()->tagName),
            "Expected '{$this->assert->getSelector()}' not to be a <{$tagName}> element, but it is",
        );

        return $this->assert;
    }

    public function toHaveCount(int $expected): DomAssert
    {
        $count = $this->assert->getElement()->childElementCount;

        PHPUnit::assertNotSame(
            $expected,
            $count,
            "Expected '{$this->assert->getSelector()}' not to have {$expected} child element(s), but it does",
        );

        return $this->assert;
    }
}

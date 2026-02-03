<?php

declare(strict_types=1);

namespace pxlrbt\LaravelAssertDom\Assertions;

use Closure;
use Dom\Element;
use Illuminate\Testing\Assert as PHPUnit;

class DomAssert
{
    public readonly NegatedDomAssert $not;

    public function __construct(
        private Element $element,
        private string $selector,
    ) {
        $this->not = new NegatedDomAssert($this);
    }

    public function getElement(): Element
    {
        return $this->element;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function text(): StringAssert
    {
        return new StringAssert(
            $this->element->textContent,
            $this,
            "text content of '{$this->selector}'",
        );
    }

    public function html(): StringAssert
    {
        return new StringAssert(
            $this->element->innerHTML,
            $this,
            "inner HTML of '{$this->selector}'",
        );
    }

    public function attribute(string $name): StringAssert
    {
        $value = $this->element->hasAttribute($name)
            ? $this->element->getAttribute($name)
            : null;

        return new StringAssert(
            $value,
            $this,
            "attribute '{$name}' of '{$this->selector}'",
        );
    }

    public function class(): StringAssert
    {
        return $this->attribute('class');
    }

    public function data(string $key): StringAssert
    {
        return $this->attribute("data-{$key}");
    }

    public function value(): StringAssert
    {
        return $this->attribute('value');
    }

    public function toBeTag(string $tagName): self
    {
        PHPUnit::assertSame(
            strtolower($tagName),
            strtolower($this->element->tagName),
            "Expected '{$this->selector}' to be a <{$tagName}> element, but found <{$this->element->tagName}>",
        );

        return $this;
    }

    public function toHave(string $selector, int|Closure|null $countOrCallback = null): self
    {
        $elements = $this->element->querySelectorAll($selector);
        $count = $elements->count();

        PHPUnit::assertGreaterThan(
            0,
            $count,
            "Expected '{$this->selector}' to have child element(s) matching '{$selector}', but none found",
        );

        if (is_int($countOrCallback)) {
            PHPUnit::assertSame(
                $countOrCallback,
                $count,
                "Expected '{$this->selector}' to have {$countOrCallback} child element(s) matching '{$selector}', but found {$count}",
            );
        }

        if ($countOrCallback instanceof Closure) {
            foreach ($elements as $element) {
                $countOrCallback(new DomAssert($element, "{$this->selector} {$selector}"));
            }
        }

        return $this;
    }

    public function toHaveCount(int $expected): self
    {
        $count = $this->element->childElementCount;

        PHPUnit::assertSame(
            $expected,
            $count,
            "Expected '{$this->selector}' to have {$expected} child element(s), but found {$count}",
        );

        return $this;
    }
}

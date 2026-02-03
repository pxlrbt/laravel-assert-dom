<?php

declare(strict_types=1);

namespace pxlrbt\LaravelAssertDom;

use Closure;
use Dom\HTMLDocument;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use pxlrbt\LaravelAssertDom\Assertions\DomAssert;
use Throwable;

class AssertDomMixin
{
    public function assertDom(): Closure
    {
        return function (string $selector, Closure $callback): TestResponse {
            /** @var TestResponse $this */
            try {
                $content = $this->json('components.0.effects.html');
            } catch (Throwable) {
                $content = $this->getContent();
            }

            $doc = HTMLDocument::createFromString($content, LIBXML_NOERROR);
            $element = $doc->querySelector($selector);

            PHPUnit::assertNotNull($element, "Element '{$selector}' not found");

            $callback(new DomAssert($element, $selector));

            return $this;
        };
    }

    public function assertDomAll(): Closure
    {
        return function (string $selector, Closure $callback): TestResponse {
            /** @var TestResponse $this */
            try {
                $content = $this->json('components.0.effects.html');
            } catch (Throwable) {
                $content = $this->getContent();
            }

            $doc = HTMLDocument::createFromString($content, LIBXML_NOERROR);
            $elements = $doc->querySelectorAll($selector);

            PHPUnit::assertGreaterThan(
                0,
                $elements->count(),
                "No elements matching '{$selector}' found",
            );

            foreach ($elements as $element) {
                $callback(new DomAssert($element, $selector));
            }

            return $this;
        };
    }
}

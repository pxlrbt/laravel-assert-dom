<?php

declare(strict_types=1);

use Dom\HTMLDocument;
use pxlrbt\LaravelAssertDom\Assertions\DomAssert;

function domAssert(string $html, string $selector): DomAssert
{
    $doc = HTMLDocument::createFromString($html, LIBXML_NOERROR);
    $element = $doc->querySelector($selector);

    if ($element === null) {
        throw new RuntimeException("Element '{$selector}' not found");
    }

    return new DomAssert($element, $selector);
}

describe('text()', function () {
    it('asserts text equals', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->toEqual('Hello World');
    });

    it('asserts text contains', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->toContain('World');
    });

    it('asserts text matches pattern', function () {
        $assert = domAssert('<div class="card">Hello World 123</div>', '.card');

        $assert->text()->toMatch('/World \d+/');
    });

    it('asserts text is empty', function () {
        $assert = domAssert('<div class="card"></div>', '.card');

        $assert->text()->toBeEmpty();
    });

    it('asserts text not equals', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->not->toEqual('Goodbye');
    });

    it('asserts text not contains', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->not->toContain('Goodbye');
    });
});

describe('html()', function () {
    it('asserts inner HTML contains', function () {
        $assert = domAssert('<div class="card"><strong>Bold</strong></div>', '.card');

        $assert->html()->toContain('<strong>');
    });

    it('asserts inner HTML equals', function () {
        $assert = domAssert('<div class="card"><span>Test</span></div>', '.card');

        $assert->html()->toEqual('<span>Test</span>');
    });
});

describe('attribute()', function () {
    it('asserts attribute equals', function () {
        $assert = domAssert('<div id="main-card" class="card">Content</div>', '.card');

        $assert->attribute('id')->toEqual('main-card');
    });

    it('asserts attribute exists', function () {
        $assert = domAssert('<button disabled class="btn">Submit</button>', '.btn');

        $assert->attribute('disabled')->toExist();
    });

    it('asserts attribute not exists', function () {
        $assert = domAssert('<button class="btn">Submit</button>', '.btn');

        $assert->attribute('disabled')->not->toExist();
    });

    it('asserts boolean attribute', function () {
        $assert = domAssert('<input type="checkbox" checked class="checkbox" />', '.checkbox');

        $assert->attribute('checked')->toExist();
        $assert->attribute('readonly')->not->toExist();
    });
});

describe('class()', function () {
    it('asserts class contains', function () {
        $assert = domAssert('<div class="card active highlighted">Content</div>', '.card');

        $assert->class()->toContain('active');
    });

    it('asserts class not contains', function () {
        $assert = domAssert('<div class="card active">Content</div>', '.card');

        $assert->class()->not->toContain('hidden');
    });
});

describe('data()', function () {
    it('asserts data attribute equals', function () {
        $assert = domAssert('<div class="user" data-user-id="123">John</div>', '.user');

        $assert->data('user-id')->toEqual('123');
    });

    it('asserts data attribute contains', function () {
        $assert = domAssert('<div class="item" data-tags="php,laravel,pest">Item</div>', '.item');

        $assert->data('tags')->toContain('laravel');
    });
});

describe('value()', function () {
    it('asserts input value', function () {
        $assert = domAssert('<input class="name" value="John Doe" />', '.name');

        $assert->value()->toEqual('John Doe');
    });
});

describe('toBeTag()', function () {
    it('asserts element tag name', function () {
        $assert = domAssert('<div class="container">Content</div>', '.container');

        $assert->toBeTag('div');
    });

    it('asserts element tag name case insensitive', function () {
        $assert = domAssert('<DIV class="container">Content</DIV>', '.container');

        $assert->toBeTag('div');
    });

    it('asserts element is not a tag', function () {
        $assert = domAssert('<div class="container">Content</div>', '.container');

        $assert->not->toBeTag('span');
    });
});

describe('toHave()', function () {
    it('asserts element has child matching selector', function () {
        $assert = domAssert('<div class="profile"><img class="avatar" /></div>', '.profile');

        $assert->toHave('.avatar');
    });

    it('asserts element has specific count of children', function () {
        $assert = domAssert('<ul class="user-list"><li>A</li><li>B</li><li>C</li></ul>', '.user-list');

        $assert->toHave('li', 3);
    });

    it('asserts element does not have child', function () {
        $assert = domAssert('<div class="profile"><img class="avatar" /></div>', '.profile');

        $assert->not->toHave('.error');
    });

    it('supports nested assertions with callback', function () {
        $html = '<ul class="user-list"><li class="item">User 1</li><li class="item">User 2</li></ul>';
        $assert = domAssert($html, '.user-list');

        $assert->toHave(
            'li',
            fn (DomAssert $item) => $item
                ->class()->toContain('item')
                ->text()->toContain('User')
        );
    });
});

describe('chaining', function () {
    it('supports fluent chaining of assertions', function () {
        $html = '<div class="card active" id="main" data-count="5"><strong>Title</strong></div>';
        $assert = domAssert($html, '.card');

        $assert
            ->toBeTag('div')
            ->attribute('id')->toEqual('main')
            ->class()->toContain('active')
            ->class()->not->toContain('hidden')
            ->data('count')->toEqual('5')
            ->toHave('strong')
            ->not->toHave('.error');
    });
});

describe('error handling', function () {
    it('throws when element not found', function () {
        domAssert('<div class="card">Content</div>', '.nonexistent');
    })->throws(RuntimeException::class, "Element '.nonexistent' not found");

    it('handles malformed HTML gracefully', function () {
        // Arrange - unclosed tags and missing attributes
        $html = '<div class="card"><p>Unclosed paragraph<span>Nested</div>';

        // Act & Assert - should parse without throwing
        $assert = domAssert($html, '.card');
        $assert->toBeTag('div');
    });

    it('provides clear error message with selector', function () {
        // Arrange
        $assert = domAssert('<div class="card">Hello</div>', '.card');

        // Act & Assert
        try {
            $assert->text()->toEqual('Goodbye');
        } catch (PHPUnit\Framework\ExpectationFailedException $e) {
            expect($e->getMessage())->toContain('.card');
            expect($e->getMessage())->toContain('text content');

            return;
        }

        throw new RuntimeException('Expected exception not thrown');
    });

    it('provides clear error message for missing attribute', function () {
        // Arrange
        $assert = domAssert('<div class="card">Hello</div>', '.card');

        // Act & Assert
        try {
            $assert->attribute('data-id')->toEqual('123');
        } catch (PHPUnit\Framework\ExpectationFailedException $e) {
            expect($e->getMessage())->toContain('data-id');
            expect($e->getMessage())->toContain('.card');

            return;
        }

        throw new RuntimeException('Expected exception not thrown');
    });
});

describe('toHaveCount()', function () {
    it('asserts exact count of direct children', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li><li>C</li></ul>', '.list');

        $assert->toHaveCount(3);
    });

    it('fails with wrong count', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li></ul>', '.list');

        $assert->toHaveCount(5);
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, 'to have 5 child element(s), but found 2');

    it('counts only direct children', function () {
        // Arrange - nested structure
        $html = '<div class="parent"><span>A</span><span><b>Nested</b></span></div>';
        $assert = domAssert($html, '.parent');

        // Act & Assert - should only count the 2 direct span children
        $assert->toHaveCount(2);
    });

    it('returns DomAssert for chaining', function () {
        $assert = domAssert('<ul class="list"><li>A</li></ul>', '.list');

        $result = $assert->toHaveCount(1);

        expect($result)->toBeInstanceOf(DomAssert::class);
    });
});

describe('negated assertions', function () {
    it('asserts text not matches pattern', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->not->toMatch('/\d+/');
    });

    it('asserts text not empty', function () {
        $assert = domAssert('<div class="card">Content</div>', '.card');

        $assert->text()->not->toBeEmpty();
    });

    it('asserts html not equals', function () {
        $assert = domAssert('<div class="card"><span>Test</span></div>', '.card');

        $assert->html()->not->toEqual('<p>Different</p>');
    });

    it('asserts html not contains', function () {
        $assert = domAssert('<div class="card"><span>Test</span></div>', '.card');

        $assert->html()->not->toContain('<p>');
    });

    it('asserts attribute not equals', function () {
        $assert = domAssert('<div class="card" id="main">Content</div>', '.card');

        $assert->attribute('id')->not->toEqual('sidebar');
    });

    it('asserts attribute not contains', function () {
        $assert = domAssert('<div class="card primary" id="main">Content</div>', '.card');

        $assert->class()->not->toContain('secondary');
    });

    it('asserts attribute not matches', function () {
        $assert = domAssert('<div class="card" data-id="abc123">Content</div>', '.card');

        $assert->data('id')->not->toMatch('/^[0-9]+$/');
    });

    it('fails negated text equals when text matches', function () {
        $assert = domAssert('<div class="card">Hello</div>', '.card');

        $assert->text()->not->toEqual('Hello');
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "not to equal 'Hello'");

    it('fails negated contains when text contains needle', function () {
        $assert = domAssert('<div class="card">Hello World</div>', '.card');

        $assert->text()->not->toContain('World');
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "not to contain 'World'");

    it('fails negated match when pattern matches', function () {
        $assert = domAssert('<div class="card">Test 123</div>', '.card');

        $assert->text()->not->toMatch('/\d+/');
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, 'not to match pattern');

    it('fails negated empty when value is empty', function () {
        $assert = domAssert('<div class="card"></div>', '.card');

        $assert->text()->not->toBeEmpty();
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, 'not to be empty');
});

describe('not->toHave()', function () {
    it('asserts element does not have matching child', function () {
        $assert = domAssert('<div class="card"><span>Content</span></div>', '.card');

        $assert->not->toHave('.error');
    });

    it('fails when element has matching child', function () {
        $assert = domAssert('<div class="card"><span class="error">Error</span></div>', '.card');

        $assert->not->toHave('.error');
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "not to have child element(s) matching '.error'");

    it('asserts element does not have specific count of children', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li></ul>', '.list');

        $assert->not->toHave('li', 5);
    });

    it('fails when element has exact count of children', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li><li>C</li></ul>', '.list');

        $assert->not->toHave('li', 3);
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "not to have 3 child element(s) matching 'li'");

    it('asserts no element satisfies callback assertions', function () {
        $html = '<ul class="nav"><li class="item">Home</li><li class="item">About</li></ul>';
        $assert = domAssert($html, '.nav');

        $assert->not->toHave(
            'li',
            fn (DomAssert $li) => $li
                ->class()->toContain('active')
                ->text()->toEqual('Contact')
        );
    });

    it('fails when any element satisfies all callback assertions', function () {
        $html = '<ul class="nav"><li class="item active">Home</li><li class="item">About</li></ul>';
        $assert = domAssert($html, '.nav');

        $assert->not->toHave(
            'li',
            fn (DomAssert $li) => $li
                ->class()->toContain('active')
                ->text()->toEqual('Home')
        );
    })->throws(PHPUnit\Framework\AssertionFailedError::class, "not to have child element matching 'li' that satisfies the given assertions");

    it('passes callback when no elements match selector', function () {
        $assert = domAssert('<div class="empty"></div>', '.empty');

        $result = $assert->not->toHave(
            '.item',
            fn (DomAssert $item) => $item->text()->toEqual('Test')
        );

        expect($result)->toBeInstanceOf(DomAssert::class);
    });

    it('passes callback when all elements fail at least one assertion', function () {
        $html = '<ul class="list"><li class="a">One</li><li class="b">Two</li></ul>';
        $assert = domAssert($html, '.list');

        $assert->not->toHave(
            'li',
            fn (DomAssert $li) => $li
                ->class()->toContain('active')
        );
    });
});

describe('not->toBeTag()', function () {
    it('asserts element is not a specific tag', function () {
        $assert = domAssert('<div class="container">Content</div>', '.container');

        $assert->not->toBeTag('span');
    });

    it('fails when element is the specified tag', function () {
        $assert = domAssert('<div class="container">Content</div>', '.container');

        $assert->not->toBeTag('div');
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, 'not to be a <div> element');

    it('is case insensitive', function () {
        $assert = domAssert('<DIV class="container">Content</DIV>', '.container');

        $assert->not->toBeTag('span');
        $assert->not->toBeTag('SPAN');
    });
});

describe('not->toHaveCount()', function () {
    it('asserts element does not have specific child count', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li></ul>', '.list');

        $assert->not->toHaveCount(5);
    });

    it('fails when element has exact child count', function () {
        $assert = domAssert('<ul class="list"><li>A</li><li>B</li></ul>', '.list');

        $assert->not->toHaveCount(2);
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, 'not to have 2 child element(s)');

    it('counts only direct children', function () {
        $html = '<div class="parent"><span><b>Nested</b></span><span>Direct</span></div>';
        $assert = domAssert($html, '.parent');

        $assert->not->toHaveCount(3);
        $assert->not->toHaveCount(1);
    });

    it('returns DomAssert for chaining', function () {
        $assert = domAssert('<ul class="list"><li>A</li></ul>', '.list');

        $result = $assert->not->toHaveCount(5);

        expect($result)->toBeInstanceOf(DomAssert::class);
    });
});

describe('edge cases', function () {
    it('handles empty attribute values', function () {
        $assert = domAssert('<input class="field" value="" disabled />', '.field');

        $assert->value()->toBeEmpty();
        $assert->attribute('disabled')->toExist();
        $assert->attribute('disabled')->toEqual('');
    });

    it('handles whitespace in text content', function () {
        $assert = domAssert('<div class="card">  Hello   World  </div>', '.card');

        $assert->text()->toContain('Hello');
        $assert->text()->toContain('World');
    });

    it('handles nested elements with same class', function () {
        $html = '<div class="item"><div class="item"><span class="item">Nested</span></div></div>';
        $assert = domAssert($html, '.item');

        // Should match the outermost element
        $assert->toBeTag('div');
        $assert->toHave('.item', 2); // 2 nested .item elements
    });

    it('handles special characters in selectors', function () {
        $assert = domAssert('<div id="my-id" class="my-class">Content</div>', '#my-id');

        $assert->toBeTag('div');
        $assert->class()->toContain('my-class');
    });

    it('handles data attributes with dashes', function () {
        $assert = domAssert('<div class="card" data-user-id="123" data-is-active="true">Content</div>', '.card');

        $assert->data('user-id')->toEqual('123');
        $assert->data('is-active')->toEqual('true');
    });

    it('handles elements with multiple classes', function () {
        $assert = domAssert('<div class="card primary active highlighted">Content</div>', '.card');

        $assert->class()->toContain('primary');
        $assert->class()->toContain('active');
        $assert->class()->toContain('highlighted');
        $assert->class()->not->toContain('secondary');
    });

    it('handles unicode content', function () {
        $assert = domAssert('<div class="card">Hello 世界 🌍</div>', '.card');

        $assert->text()->toContain('世界');
        $assert->text()->toContain('🌍');
    });

    it('handles self-closing tags', function () {
        $assert = domAssert('<div class="form"><input class="input" type="text" /><br /><hr /></div>', '.form');

        $assert->toHave('input');
        $assert->toHave('br');
        $assert->toHave('hr');
    });

    it('handles attribute selectors', function () {
        $html = '<input type="text" name="email" /><input type="password" name="pass" />';
        $assert = domAssert($html, 'input[type="password"]');

        $assert->attribute('name')->toEqual('pass');
    });
});

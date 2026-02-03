<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;
use pxlrbt\LaravelAssertDom\AssertDomMixin;
use pxlrbt\LaravelAssertDom\Assertions\DomAssert;

function createTestResponse(string $content): TestResponse
{
    $response = new class($content)
    {
        public ?Throwable $exception = null;

        public function __construct(private string $content) {}

        public function getContent(): string
        {
            return $this->content;
        }
    };

    return new TestResponse($response);
}

function createLivewireTestResponse(string $html): TestResponse
{
    $livewirePayload = [
        'components' => [
            [
                'effects' => [
                    'html' => $html,
                ],
            ],
        ],
    ];

    $response = new class($livewirePayload)
    {
        public ?Throwable $exception = null;

        public function __construct(private array $payload) {}

        public function getContent(): string
        {
            return json_encode($this->payload);
        }
    };

    return new TestResponse($response);
}

beforeAll(function () {
    TestResponse::mixin(new AssertDomMixin);
});

describe('assertDom()', function () {
    it('asserts DOM element in HTML response', function () {
        // Arrange
        $html = '<div class="card"><h1>Title</h1></div>';
        $response = createTestResponse($html);

        // Act & Assert
        $response->assertDom(
            '.card',
            fn (DomAssert $dom) => $dom
                ->toBeTag('div')
                ->toHave('h1')
        );
    });

    it('asserts DOM element in Livewire JSON response', function () {
        // Arrange
        $html = '<div class="card"><h1>Title</h1></div>';
        $response = createLivewireTestResponse($html);

        // Act & Assert
        $response->assertDom(
            '.card',
            fn (DomAssert $dom) => $dom
                ->toBeTag('div')
                ->toHave('h1')
        );
    });

    it('fails when selector not found', function () {
        // Arrange
        $html = '<div class="card">Content</div>';
        $response = createTestResponse($html);

        // Act & Assert
        $response->assertDom('.nonexistent', fn (DomAssert $dom) => $dom->toBeTag('div'));
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "Element '.nonexistent' not found");

    it('returns TestResponse for chaining', function () {
        // Arrange
        $html = '<div class="card">Content</div>';
        $response = createTestResponse($html);

        // Act
        $result = $response->assertDom('.card', fn (DomAssert $dom) => $dom->toBeTag('div'));

        // Assert
        expect($result)->toBeInstanceOf(TestResponse::class);
        expect($result)->toBe($response);
    });

    it('allows nested assertions within callback', function () {
        // Arrange
        $html = '<div class="profile"><span class="name">John Doe</span><span class="email">john@example.com</span></div>';
        $response = createTestResponse($html);

        // Act & Assert
        $response->assertDom(
            '.profile',
            fn (DomAssert $dom) => $dom
                ->toHave('.name', fn (DomAssert $name) => $name->text()->toEqual('John Doe'))
                ->toHave('.email', fn (DomAssert $email) => $email->text()->toContain('@'))
        );
    });
});

describe('assertDomAll()', function () {
    it('asserts all matching elements', function () {
        // Arrange
        $html = '<ul><li class="item">A</li><li class="item">B</li><li class="item">C</li></ul>';
        $response = createTestResponse($html);

        // Act & Assert
        $count = 0;
        $response->assertDomAll('.item', function (DomAssert $dom) use (&$count) {
            $dom->toBeTag('li');
            $count++;
        });

        expect($count)->toBe(3);
    });

    it('calls callback for each element', function () {
        // Arrange
        $html = '<div><span class="tag">PHP</span><span class="tag">Laravel</span></div>';
        $response = createTestResponse($html);

        // Act
        $texts = [];
        $response->assertDomAll('.tag', function (DomAssert $dom) use (&$texts) {
            $texts[] = $dom->getElement()->textContent;
        });

        // Assert
        expect($texts)->toBe(['PHP', 'Laravel']);
    });

    it('fails when no elements found', function () {
        // Arrange
        $html = '<div class="card">Content</div>';
        $response = createTestResponse($html);

        // Act & Assert
        $response->assertDomAll('.nonexistent', fn (DomAssert $dom) => $dom->toBeTag('div'));
    })->throws(PHPUnit\Framework\ExpectationFailedException::class, "No elements matching '.nonexistent' found");

    it('returns TestResponse for chaining', function () {
        // Arrange
        $html = '<div><span class="item">A</span><span class="item">B</span></div>';
        $response = createTestResponse($html);

        // Act
        $result = $response->assertDomAll('.item', fn (DomAssert $dom) => $dom->toBeTag('span'));

        // Assert
        expect($result)->toBeInstanceOf(TestResponse::class);
        expect($result)->toBe($response);
    });

    it('works with Livewire JSON responses', function () {
        // Arrange
        $html = '<ul><li class="item">First</li><li class="item">Second</li></ul>';
        $response = createLivewireTestResponse($html);

        // Act
        $count = 0;
        $response->assertDomAll('.item', function (DomAssert $dom) use (&$count) {
            $dom->toBeTag('li');
            $count++;
        });

        // Assert
        expect($count)->toBe(2);
    });
});

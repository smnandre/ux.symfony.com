<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Browser;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use Playwright\Locator\LocatorInterface;
use Playwright\Network\RequestInterface;
use Playwright\Testing\PlaywrightTestCase;

#[Group('e2e')]
#[CoversNothing]
final class DocSearchTest extends PlaywrightTestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        $baseUrl = $_SERVER['PLAYWRIGHT_BASE_URL'] ?? getenv('PLAYWRIGHT_BASE_URL');
        if (!\is_string($baseUrl) || '' === $baseUrl) {
            self::markTestSkipped('Set PLAYWRIGHT_BASE_URL to run browser tests.');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (isset($this->context)) {
            parent::tearDown();
        }
    }

    public function testNothingFromDocSearchLoadsWithThePage(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $this->expect($this->button())->toBeVisible();

        self::assertSame([], $this->requestedUrls('@docsearch'));
        $this->expect($this->page->locator('.DocSearch-Container'))->toHaveCount(0);
    }

    public function testHoveringTheButtonPrefetchesWithoutQueryingAlgolia(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $this->button()->hover();

        $this->expect($this->page->locator('[data-docsearch-target="mount"] .DocSearch-Button'))->toBeAttached();
        self::assertNotSame([], $this->requestedUrls('@docsearch/css'));
        $this->expect($this->modal())->toHaveCount(0);
        self::assertSame([], $this->requestedUrls('algolia.net'));
    }

    public function testPressingTheButtonDoesNotMoveTheHeader(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $previousLink = $this->page->locator('header')->getByRole('link', ['name' => 'Demos', 'exact' => true]);
        $this->button()->hover();
        $before = [$this->button()->boundingBox(), $previousLink->boundingBox()];

        $this->page->mouse()->down();
        $pressed = [$this->button()->boundingBox(), $previousLink->boundingBox()];
        $this->page->mouse()->up();

        self::assertSame($before, $pressed);
    }

    public function testOpeningKeepsTheScrollbarRoom(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $html = $this->page->locator('html');

        $this->button()->click();
        $this->expect($this->modal())->toBeVisible();
        $this->expect($html)->toHaveCSS('scrollbar-gutter', 'stable');

        $this->page->keyboard()->press('Escape');
        $this->expect($this->modal())->toHaveCount(0);
        $this->expect($html)->toHaveCSS('scrollbar-gutter', 'auto');
    }

    public function testButtonOpensAnAccessibleDialogThatEscapeCloses(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $this->button()->click();

        $dialog = $this->page->getByRole('dialog', ['name' => 'Search']);
        $this->expect($dialog)->toBeVisible();
        $this->expect($dialog)->toHaveAttribute('aria-modal', 'true');
        $this->expect($this->page->locator('.DocSearch-Container'))->not()->toHaveAttribute('role', 'button');
        $this->expect($this->page->locator('.DocSearch-Input'))->toBeFocused();

        $this->page->keyboard()->press('Escape');

        $this->expect($this->modal())->toHaveCount(0);
        $this->expect($this->button())->toBeFocused();
    }

    public function testOnlyCmdOrCtrlKOpensTheDialog(): void
    {
        $this->page->goto($this->baseUrl.'/');
        $this->expect($this->button())->toBeVisible();

        foreach (['/', 'Shift', 'Shift', 'k', 'Alt+Meta+K', 'Meta+J'] as $key) {
            $this->page->keyboard()->press($key);
        }
        self::assertStringNotContainsString('@import url(', $this->page->content());
        $this->expect($this->modal())->toHaveCount(0);

        foreach (['Meta+K', 'Control+K'] as $shortcut) {
            $this->page->keyboard()->press($shortcut);
            $this->expect($this->modal())->toBeVisible();

            $this->page->keyboard()->press($shortcut);
            $this->expect($this->modal())->toHaveCount(0);
        }
    }

    private function button(): LocatorInterface
    {
        return $this->page->getByRole('button', ['name' => 'Search', 'exact' => true]);
    }

    private function modal(): LocatorInterface
    {
        return $this->page->locator('.DocSearch-Modal');
    }

    /**
     * @return list<string>
     */
    private function requestedUrls(string $needle): array
    {
        $urls = array_map(static fn (RequestInterface $request): string => $request->url(), $this->page->requests());

        return array_values(array_filter($urls, static fn (string $url): bool => str_contains($url, $needle)));
    }
}

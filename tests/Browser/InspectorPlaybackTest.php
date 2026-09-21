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
use Playwright\Testing\PlaywrightTestCase;

#[Group('e2e')]
#[CoversNothing]
final class InspectorPlaybackTest extends PlaywrightTestCase
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

    public function testGuidedDemoUsesInteractiveFrames(): void
    {
        $this->page->goto($this->baseUrl.'/inspector');

        $frame = $this->page->locator('[data-inspector-mockup-target="frame"]');
        $this->expect($frame)->withTimeout(15000)->toBeVisible();
        $this->expect($this->page->locator('#inspector-stage'))->withTimeout(15000)->toHaveAttribute('aria-busy', 'false');
        $this->expect($this->page->locator('.uxc-interact'))->toHaveCount(0);
        self::assertNull($frame->getAttribute('inert'));

        $inspect = $this->page->locator('[role="tab"][data-mode="inspect"]');
        $inspect->click();
        $this->expect($inspect)->toHaveAttribute('aria-selected', 'true');
        $this->expect($this->page->locator('[data-controller~="inspector-mockup"]'))
            ->toHaveAttribute('data-inspector-mockup-autoplay-value', 'false');
        $this->page->waitForFunction(
            '() => Number.parseFloat(getComputedStyle(document.querySelector("[data-controller~=inspector-mockup]")).getPropertyValue("--progress")) > 0',
            null,
            ['timeout' => 1000],
        );
        $inspectFrame = $this->page->locator('iframe[src*="/demos/inspector/inspect"]');
        $this->expect($inspectFrame)
            ->withTimeout(15000)
            ->toBeVisible();
        $firstInspectRun = $inspectFrame->getAttribute('data-run');

        $trace = $this->page->locator('[role="tab"][data-mode="trace"]');
        $trace->click();
        $this->expect($trace)->toHaveAttribute('aria-selected', 'true');
        $this->expect($this->page->locator('iframe[src*="/demos/inspector/trace"]'))
            ->withTimeout(15000)
            ->toBeVisible();

        $inspect->click();
        $this->expect($inspect)->toHaveAttribute('aria-selected', 'true');
        $restartedInspectFrame = $this->page->locator('iframe[src*="/demos/inspector/inspect"]');
        $this->expect($restartedInspectFrame)
            ->withTimeout(15000)
            ->toBeVisible();
        self::assertNotSame($firstInspectRun, $restartedInspectFrame->getAttribute('data-run'));
    }

    public function testUserInputStopsPlayback(): void
    {
        $this->page->addInitScript(<<<'JS'
            window.__inspectorDemoMessages = [];
            window.addEventListener('message', (event) => {
                if (event.data?.inspectorDemo) window.__inspectorDemoMessages.push(event.data);
            });
            JS);
        $this->page->goto($this->baseUrl.'/demos/inspector/find');

        $this->expect($this->page->locator('ux-inspector[ready]'))->withTimeout(15000)->toHaveCount(1);
        $this->page->waitForFunction(
            '() => window.__inspectorDemoMessages.some((message) => typeof message.progress === "number")',
            null,
            ['timeout' => 15000],
        );

        $favorite = $this->page->locator('[aria-label="Favorite product 1"]');
        $favorite->click();
        $this->expect($favorite)->toHaveAttribute('aria-pressed', 'true');

        usleep(200000);
        $messageCount = $this->page->evaluate('window.__inspectorDemoMessages.length');
        usleep(1200000);
        self::assertSame($messageCount, $this->page->evaluate('window.__inspectorDemoMessages.length'));
    }
}

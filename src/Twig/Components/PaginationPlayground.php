<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\Pagination\LiveComponent\ComponentWithPaginationTrait;
use Symfony\UX\Pagination\PaginationBuilder;
use Symfony\UX\Pagination\PaginatorInterface;
use Twig\Environment;

#[AsLiveComponent('PaginationPlayground')]
final class PaginationPlayground
{
    use ComponentWithPaginationTrait;
    use DefaultActionTrait;

    private const THEMES = [
        'default' => '@UXPagination/theme/default.html.twig',
        'tailwind' => '@UXPagination/theme/tailwind.html.twig',
        'dots' => 'demos/live_component/pagination_playground/_dots.html.twig',
    ];

    #[LiveProp(writable: true)]
    public string $theme = 'default';

    #[LiveProp(writable: true)]
    public string $accentColor = '#6D28D9';

    #[LiveProp(writable: true)]
    public string $mode = 'sliding';

    #[LiveProp(writable: true)]
    public int $size = 5;

    #[LiveProp(writable: true)]
    public bool $showInfo = true;

    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly Environment $twig,
    ) {
    }

    public function getThemeTemplate(): string
    {
        return self::THEMES[$this->theme] ?? self::THEMES['default'];
    }

    public function getThemeSource(): string
    {
        return $this->twig->getLoader()->getSourceContext($this->getThemeTemplate())->getCode();
    }

    public function getAccentStyle(): string
    {
        $color = preg_match('/^#[0-9a-fA-F]{3,8}$/', $this->accentColor) ? $this->accentColor : '#6D28D9';

        return \sprintf('--ux-pagination-active-background: %1$s; --ux-pagination-focus-color: %1$s;', $color);
    }

    protected function createPagination(): PaginationBuilder
    {
        $builder = $this->paginator
            ->query(range(1, 240))
            ->route('app_demo_live_component_pagination_playground')
            ->perPage(10);

        $size = max(3, min(9, $this->size));

        return match ($this->mode) {
            'fixed' => $builder->fixed($size),
            'full' => $builder->full(24),
            default => $builder->sliding($size),
        };
    }
}

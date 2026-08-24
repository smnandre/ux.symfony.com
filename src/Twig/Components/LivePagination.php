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

use App\Repository\JerseyRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\Pagination\LiveComponent\ComponentWithPaginationTrait;
use Symfony\UX\Pagination\PaginationBuilder;
use Symfony\UX\Pagination\PaginatorInterface;

#[AsLiveComponent('LivePagination')]
final class LivePagination
{
    use ComponentWithPaginationTrait;
    use DefaultActionTrait;

    #[LiveProp(
        writable: true,
        url: new UrlMapping(mapPath: true),
        modifier: 'modifyPageProp',
    )]
    public int $page = 1;

    #[LiveProp(url: true)]
    public string $color = '';

    #[LiveProp(url: true)]
    public string $pattern = '';

    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly JerseyRepository $jerseys,
    ) {
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    protected function createPagination(): PaginationBuilder
    {
        return $this->paginator
            ->query($this->jerseys->find($this->color, $this->pattern))
            ->queryParameters(array_filter([
                'color' => $this->color,
                'pattern' => $this->pattern,
            ]))
            ->perPage(10)
            ->sliding(5);
    }

    /**
     * @return array{
     *     color: array<string, string>,
     *     pattern: array<string, string>,
     * }
     */
    public function getFilters(): array
    {
        return [
            'color' => $this->jerseys->getColors(),
            'pattern' => $this->jerseys->getPatterns(),
        ];
    }

    #[LiveAction]
    public function toggleFilter(#[LiveArg] string $name, #[LiveArg] string $value): void
    {
        $choices = $this->getFilters()[$name] ?? null;
        if (null === $choices || !\array_key_exists($value, $choices)) {
            return;
        }

        $selection = $value === match ($name) {
            'color' => $this->color,
            'pattern' => $this->pattern,
            default => null,
        } ? '' : $value;

        match ($name) {
            'color' => $this->color = $selection,
            'pattern' => $this->pattern = $selection,
            default => null,
        };

        $this->resetPage();
    }
}

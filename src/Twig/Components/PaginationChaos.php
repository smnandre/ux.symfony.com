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
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\Pagination\PaginatorInterface;
use Symfony\UX\TwigComponent\Attribute\PostMount;

/**
 * Offset vs cursor pagination under data mutations.
 *
 * The reader stands on "page 2" of both paginations while rows are inserted
 * or deleted at the top of the dataset: offset drifts, the cursor does not.
 */
#[AsLiveComponent('PaginationChaos')]
final class PaginationChaos
{
    use DefaultActionTrait;

    private const PER_PAGE = 5;

    /**
     * @var list<int>
     */
    #[LiveProp]
    public array $items = [];

    /**
     * @var list<int>
     */
    #[LiveProp]
    public array $originalPageOne = [];

    /**
     * @var list<int>
     */
    #[LiveProp]
    public array $originalPageTwo = [];

    #[LiveProp]
    public ?string $cursor = null;

    public function __construct(
        private readonly PaginatorInterface $paginator,
    ) {
    }

    /**
     * @internal
     */
    #[PostMount]
    public function seed(): void
    {
        if ([] === $this->items) {
            $this->reset();
        }
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->items = range(12, 1);

        $firstPage = $this->paginator
            ->cursor($this->items)
            ->orderBy('id', 'DESC')
            ->perPage(self::PER_PAGE)
            ->context('pagination-chaos')
            ->cursor(null)
            ->paginate();

        $this->cursor = $firstPage->getNextCursor();
        $this->originalPageOne = $firstPage->getItems();
        $this->originalPageTwo = $this->getCursorPage();
    }

    #[LiveAction]
    public function insertRow(): void
    {
        if (\count($this->items) >= 24) {
            return;
        }

        array_unshift($this->items, max($this->items) + 1);
    }

    #[LiveAction]
    public function deleteRow(): void
    {
        if (\count($this->items) <= 8) {
            return;
        }

        array_shift($this->items);
    }

    /**
     * @return list<int>
     */
    public function getOffsetPage(): array
    {
        return $this->paginator
            ->query($this->items)
            ->perPage(self::PER_PAGE)
            ->paginate(page: 2)
            ->getItems();
    }

    /**
     * @return list<int>
     */
    public function getCursorPage(): array
    {
        return $this->paginator
            ->cursor($this->items)
            ->orderBy('id', 'DESC')
            ->perPage(self::PER_PAGE)
            ->context('pagination-chaos')
            ->cursor($this->cursor)
            ->paginate()
            ->getItems();
    }

    /**
     * Rows shown again although they were already read on page 1.
     *
     * @return list<int>
     */
    public function getDuplicates(): array
    {
        return array_values(array_intersect($this->getOffsetPage(), $this->originalPageOne));
    }

    /**
     * Rows that slipped back into the already-read first page: the reader
     * walking forward will never see them.
     *
     * @return list<int>
     */
    public function getSkipped(): array
    {
        $firstPageNow = \array_slice($this->items, 0, self::PER_PAGE);

        return array_values(array_intersect($this->originalPageTwo, $firstPageNow));
    }
}

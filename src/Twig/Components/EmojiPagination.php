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

use App\Service\EmojiCollection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\Pagination\LiveComponent\ComponentWithPaginationTrait;
use Symfony\UX\Pagination\PaginationBuilder;
use Symfony\UX\Pagination\PaginatorInterface;

#[AsLiveComponent('EmojiPagination')]
final class EmojiPagination
{
    use ComponentWithPaginationTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: new UrlMapping(mapPath: true), modifier: 'modifyPageProp')]
    public int $page = 1;

    #[LiveProp(writable: true, onUpdated: 'onPerPageUpdated')]
    public int $perPage = 24;

    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly EmojiCollection $emojis,
    ) {
    }

    public function onPerPageUpdated(): void
    {
        $this->resetPage();
    }

    protected function createPagination(): PaginationBuilder
    {
        return $this->paginator
            ->query(iterator_to_array($this->emojis))
            ->perPage($this->perPage)
            ->sliding(5);
    }
}

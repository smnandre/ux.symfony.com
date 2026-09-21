<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Demo;

use App\Model\LiveDemo;
use App\Service\CursorPayloadPreview;
use App\Service\UxReleaseFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Pagination\PaginatorInterface;

final class CursorPaginationDemoController extends AbstractController
{
    #[Route('/demos/pagination/cursor-pagination', name: 'app_demo_pagination_cursor')]
    public function __invoke(
        Request $request,
        CursorPayloadPreview $cursorPayloadPreview,
        PaginatorInterface $paginator,
        UxReleaseFeed $releases,
    ): Response {
        $withDeletedItem = $request->query->getBoolean('deleted');
        $pagination = $paginator
            ->cursor($releases->all($withDeletedItem))
            ->orderBy('id', 'DESC')
            ->perPage(5)
            ->context('demo-cursor')
            ->queryParameters(array_filter(['deleted' => $withDeletedItem ? 1 : null]))
            ->paginate();

        $demo = new LiveDemo(
            'cursor-pagination',
            name: 'Cursor Pagination',
            description: 'Move through ordered values without letting earlier changes shift the current slice.',
            author: 'smnandre',
            publishedAt: '2026-09-21',
            tags: ['pagination', 'cursor', 'no-js'],
            longDescription: 'Move through an ordered feed without a count query. The signed cursor records
                a stable boundary, so an earlier deletion does not shift the current slice.',
            route: 'app_demo_pagination_cursor',
            template: 'demos/pagination/cursor.html.twig',
        );

        return $this->render($demo->getTemplate(), [
            'demo' => $demo,
            'pagination' => $pagination,
            'cursorPayload' => $cursorPayloadPreview->decode($pagination->getCursor()),
            'nextCursorPayload' => $cursorPayloadPreview->decode($pagination->getNextCursor()),
            'withDeletedItem' => $withDeletedItem,
        ]);
    }
}

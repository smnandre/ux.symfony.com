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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LivePaginationDemoController extends AbstractController
{
    #[Route('/demos/pagination/live-pagination', name: 'app_demo_pagination_live')]
    public function __invoke(): Response
    {
        $demo = new LiveDemo(
            'pagination',
            name: 'Live Pagination',
            description: 'Add reactive filters and page changes to ordinary pagination links.',
            author: 'smnandre',
            publishedAt: '2026-08-04',
            tags: ['pagination', 'LiveComponent', 'filters', 'trait'],
            longDescription: 'Combine [`Pagination`](/pagination) with [`LiveComponent`](/live-component)
                for reactive filters and page changes. Each control remains an ordinary link,
                and changing a filter returns you to the first page.',
            route: 'app_demo_pagination_live',
            template: 'demos/pagination/live.html.twig',
        );

        return $this->render($demo->getTemplate(), ['demo' => $demo]);
    }
}

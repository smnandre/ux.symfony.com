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
use Symfony\UX\Pagination\PaginatorInterface;

final class PaginationThemesDemoController extends AbstractController
{
    #[Route('/demos/pagination/themes', name: 'app_demo_pagination_themes')]
    public function __invoke(PaginatorInterface $paginator): Response
    {
        $pagination = $paginator
            ->query(range(1, 48))
            ->perPage(6)
            ->sliding(3)
            ->paginate()
        ;

        $demo = new LiveDemo(
            'pagination-themes',
            name: 'Pagination Themes',
            description: 'Render the same pages with a built-in theme or a focused Twig block override.',
            author: 'smnandre',
            publishedAt: '2026-09-21',
            tags: ['pagination', 'Twig', 'themes', 'blocks'],
            longDescription: 'Render the same pagination result with the default bundle CSS,
                Tailwind utility classes, or a focused application theme override.',
            route: 'app_demo_pagination_themes',
            template: 'demos/pagination/themes.html.twig',
        );

        return $this->render($demo->getTemplate(), [
            'demo' => $demo,
            'pagination' => $pagination,
        ]);
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\UxPackage;

use App\Service\LiveDemoRepository;
use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Pagination\PaginatorInterface;

class PaginationController extends AbstractController
{
    #[Route('/pagination', name: 'app_pagination')]
    public function __invoke(
        UxPackageRepository $packageRepository,
        LiveDemoRepository $liveDemoRepository,
        PaginatorInterface $paginator,
    ): Response {
        $themes = $paginator
            ->query(range(1, 120))
            ->pageParameter('t')
            ->perPage(10)
            ->paginate();

        return $this->render('ux_packages/pagination.html.twig', [
            'package' => $packageRepository->find('pagination'),
            'themes' => $themes,
            'demos' => [
                $liveDemoRepository->find('pagination'),
                $liveDemoRepository->find('cursor-pagination'),
                $liveDemoRepository->find('pagination-studio'),
            ],
        ]);
    }
}

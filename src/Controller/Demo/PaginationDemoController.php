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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demos/pagination')]
final class PaginationDemoController extends AbstractController
{
    #[Route('', name: 'app_demo_pagination')]
    public function index(): Response
    {
        return $this->render('demos/pagination/index.html.twig');
    }
}

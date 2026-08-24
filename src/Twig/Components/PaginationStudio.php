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
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\Pagination\LiveComponent\ComponentWithPaginationTrait;
use Symfony\UX\Pagination\PaginationBuilder;
use Symfony\UX\Pagination\PaginationInterface;
use Symfony\UX\Pagination\PaginatorInterface;

#[AsLiveComponent('PaginationStudio')]
final class PaginationStudio
{
    use ComponentWithPaginationTrait;
    use DefaultActionTrait;

    private const THEMES = [
        'default' => '@UXPagination/theme/default.html.twig',
        'bootstrap' => '@UXPagination/theme/bootstrap.html.twig',
        'tailwind' => '@UXPagination/theme/tailwind.html.twig',
        'application' => 'demos/live_component/pagination_studio/_application.html.twig',
    ];

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: new UrlMapping(as: 'type'))]
    public string $strategy = 'numbered';

    #[LiveProp(writable: true, onUpdated: 'resetOutput', url: true)]
    public string $theme = 'default';

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: new UrlMapping(as: 'per_page'))]
    public int $itemsPerPage = 10;

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: new UrlMapping(as: 'items'))]
    public int $totalItems = 240;

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: true)]
    public int $maxOffset = 100000;

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: true)]
    public string $mode = 'sliding';

    #[LiveProp(writable: true, onUpdated: 'resetPreview', url: true)]
    public int $size = 5;

    #[LiveProp(writable: true, url: true)]
    public bool $showInfo = true;

    #[LiveProp(writable: true, url: true)]
    public string $pipeline = 'asset-mapper';

    #[LiveProp(writable: true, onUpdated: 'resetOutput', url: true)]
    public bool $includeCss = true;

    #[LiveProp(writable: true)]
    public string $output = 'config';

    #[LiveProp(writable: true)]
    public string $accentColor = '#78d64b';

    #[LiveProp(url: true)]
    public ?string $cursor = null;

    public function __construct(
        private readonly PaginatorInterface $paginator,
    ) {
    }

    /**
     * @return PaginationInterface<int|array{id: int}>
     */
    public function getPreview(): PaginationInterface
    {
        if ('cursor' !== $this->getStrategy()) {
            return $this->getPagination();
        }

        $items = array_map(
            static fn (int $id): array => ['id' => $id],
            range(1, $this->getTotalItems()),
        );

        return $this->paginator
            ->cursor($items)
            ->orderBy('id', 'ASC')
            ->perPage($this->getItemsPerPage())
            ->cursor($this->cursor)
            ->context('pagination-studio')
            ->route('app_demo_live_component_pagination_studio')
            ->queryParameters($this->getStateQueryParameters())
            ->paginate();
    }

    public function resetPreview(mixed $previousValue): void
    {
        $this->resetPage();
        $this->cursor = null;
    }

    public function resetOutput(mixed $previousValue): void
    {
        if (!\array_key_exists($this->output, $this->getOutputs())) {
            $this->output = 'config';
        }
    }

    public function getStrategy(): string
    {
        return \in_array($this->strategy, ['numbered', 'lookahead', 'cursor'], true)
            ? $this->strategy
            : 'numbered';
    }

    public function getTheme(): string
    {
        return isset(self::THEMES[$this->theme]) ? $this->theme : 'default';
    }

    public function getThemeTemplate(): string
    {
        return self::THEMES[$this->getTheme()];
    }

    public function getItemsPerPage(): int
    {
        return max(4, min(40, $this->itemsPerPage));
    }

    public function getTotalItems(): int
    {
        return max(24, min(480, $this->totalItems));
    }

    public function getMaxOffset(): int
    {
        return max(0, min(1000000, $this->maxOffset));
    }

    public function getNavigationSize(): int
    {
        if ('full' === $this->getNavigationMode()) {
            return (int) ceil($this->getTotalItems() / $this->getItemsPerPage());
        }

        return max(3, min(9, $this->size));
    }

    public function getNavigationMode(): string
    {
        return \in_array($this->mode, ['sliding', 'fixed', 'full'], true)
            ? $this->mode
            : 'sliding';
    }

    public function getPipeline(): string
    {
        return 'encore' === $this->pipeline ? 'encore' : 'asset-mapper';
    }

    public function getAccentStyle(): string
    {
        $color = $this->getAccentColor();

        return \sprintf(
            '--ux-pagination-active-background: %1$s; --ux-pagination-focus-color: %1$s;',
            $color,
        );
    }

    /**
     * @return array<string, array{label: string, filename: string, language: string}>
     */
    public function getOutputs(): array
    {
        $outputs = [
            'install' => [
                'label' => 'Install',
                'filename' => 'terminal',
                'language' => 'shell',
            ],
            'config' => [
                'label' => 'Config',
                'filename' => 'config/packages/ux_pagination.yaml',
                'language' => 'yaml',
            ],
            'php' => [
                'label' => 'PHP',
                'filename' => 'src/Controller/ProductController.php',
                'language' => 'php',
            ],
            'twig' => [
                'label' => 'Twig',
                'filename' => 'templates/product/index.html.twig',
                'language' => 'twig',
            ],
        ];

        if ($this->hasAssetOutput()) {
            $outputs['assets'] = [
                'label' => 'Assets',
                'filename' => $this->getAssetFilename(),
                'language' => $this->getAssetLanguage(),
            ];
        }

        if ('application' === $this->getTheme()) {
            if ($this->includeCss) {
                $outputs['css'] = [
                    'label' => 'CSS',
                    'filename' => 'assets/styles/pagination.css',
                    'language' => 'css',
                ];
            }

            $outputs['theme'] = [
                'label' => 'Theme',
                'filename' => 'templates/pagination/application.html.twig',
                'language' => 'twig',
            ];
        }

        return $outputs;
    }

    /**
     * @return array{label: string, filename: string, language: string}
     */
    public function getSelectedOutput(): array
    {
        $outputs = $this->getOutputs();

        return $outputs[$this->output] ?? $outputs['config'];
    }

    public function getOutputSource(): string
    {
        $output = \array_key_exists($this->output, $this->getOutputs())
            ? $this->output
            : 'config';

        return match ($output) {
            'install' => $this->getInstallationSource(),
            'php' => $this->getPhpSource(),
            'twig' => $this->getTwigSource(),
            'assets' => $this->getAssetSource(),
            'css' => $this->getCustomCssSource(),
            'theme' => $this->getApplicationThemeSource(),
            default => $this->getConfigurationSource(),
        };
    }

    public function getConfigurationSource(): string
    {
        $lines = [
            'ux_pagination:',
            \sprintf('    items_per_page: %d', $this->getItemsPerPage()),
        ];

        if ('cursor' !== $this->getStrategy()) {
            $lines[] = \sprintf('    max_offset: %d', $this->getMaxOffset());
        }

        if ('numbered' === $this->getStrategy()) {
            $lines[] = '    navigation:';
            $lines[] = \sprintf('        mode: %s', $this->getNavigationMode());
            $lines[] = \sprintf('        size: %d', $this->getNavigationSize());
        }

        $lines[] = \sprintf("    theme: '%s'", $this->getConfigurationTheme());

        return implode("\n", $lines);
    }

    public function getInstallationSource(): string
    {
        $lines = ['composer require symfony/ux-pagination'];

        if (\in_array($this->getTheme(), ['default', 'application'], true)
            && $this->includeCss
            && 'encore' === $this->getPipeline()
        ) {
            $lines[] = 'npm install @symfony/ux-pagination';
        }

        return implode("\n", $lines);
    }

    public function getPhpSource(): string
    {
        return match ($this->getStrategy()) {
            'lookahead' => <<<'PHP'
                $pagination = $paginator
                    ->query($products->createListQuery())
                    ->lookahead()
                    ->paginate();
                PHP,
            'cursor' => <<<'PHP'
                $pagination = $paginator
                    ->cursor($products->createListQuery())
                    ->orderBy(['createdAt', 'id'], 'DESC')
                    ->context('products')
                    ->paginate();
                PHP,
            default => <<<'PHP'
                $pagination = $paginator
                    ->query($products->createListQuery())
                    ->paginate();
                PHP,
        };
    }

    public function getTwigSource(): string
    {
        return <<<'TWIG'
            {% for product in pagination %}
                <article>{{ product.name }}</article>
            {% endfor %}

            {{ ux_pagination(pagination) }}
            TWIG;
    }

    public function hasAssetOutput(): bool
    {
        return match ($this->getTheme()) {
            'default', 'application' => $this->includeCss,
            'tailwind' => true,
            default => false,
        };
    }

    public function getAssetFilename(): string
    {
        if ('tailwind' === $this->getTheme()) {
            return 'assets/styles/app.css';
        }

        return 'encore' === $this->getPipeline()
            ? 'assets/app.js'
            : 'templates/base.html.twig';
    }

    public function getAssetLanguage(): string
    {
        if (\in_array($this->getTheme(), ['default', 'application'], true)) {
            return 'encore' === $this->getPipeline() ? 'javascript' : 'twig';
        }

        return 'css';
    }

    public function getAssetSource(): string
    {
        if ('tailwind' === $this->getTheme()) {
            return <<<'CSS'
                @import "tailwindcss";
                @source "../../vendor/symfony/ux-pagination/templates";
                CSS;
        }

        if ('encore' === $this->getPipeline()) {
            $lines = ["import '@symfony/ux-pagination/style.min.css';"];
            if ('application' === $this->getTheme()) {
                $lines[] = "import './styles/pagination.css';";
            }

            return implode("\n", $lines);
        }

        $source = <<<'TWIG'
            <link
                rel="stylesheet"
                href="{{ asset('@symfony/ux-pagination/style.min.css') }}"
            >
            TWIG;

        if ('application' === $this->getTheme()) {
            $source .= <<<'TWIG'

                <link rel="stylesheet" href="{{ asset('styles/pagination.css') }}">
                TWIG;
        }

        return $source;
    }

    public function getCustomCssSource(): string
    {
        return \sprintf(<<<'CSS'
            .AppPagination {
                --ux-pagination-active-background: %s;
                --ux-pagination-focus-color: %s;
            }

            .AppPagination .ux-pagination__link {
                border-radius: 999px;
            }
            CSS, $this->getAccentColor(), $this->getAccentColor());
    }

    public function getApplicationThemeSource(): string
    {
        return <<<'TWIG'
            {% extends '@UXPagination/theme/default.html.twig' %}

            {% block pagination %}
                {% set attributes = attributes|merge({
                    class: (attributes.class|default('') ~ ' AppPagination')|trim,
                }) %}
                {{ parent() }}
            {% endblock %}

            {% block previous_label %}&larr; Newer{% endblock %}
            {% block next_label %}Older &rarr;{% endblock %}
            TWIG;
    }

    protected function createPagination(): PaginationBuilder
    {
        $builder = $this->paginator
            ->query(range(1, $this->getTotalItems()))
            ->route('app_demo_live_component_pagination_studio')
            ->queryParameters($this->getStateQueryParameters())
            ->perPage($this->getItemsPerPage())
            ->maxOffset($this->getMaxOffset());

        if ('lookahead' === $this->getStrategy()) {
            return $builder->lookahead();
        }

        return match ($this->getNavigationMode()) {
            'fixed' => $builder->fixed($this->getNavigationSize()),
            'full' => $builder->full($this->getNavigationSize()),
            default => $builder->sliding($this->getNavigationSize()),
        };
    }

    /**
     * @return array<string, bool|int|string>
     */
    private function getStateQueryParameters(): array
    {
        return [
            'type' => $this->getStrategy(),
            'theme' => $this->getTheme(),
            'per_page' => $this->getItemsPerPage(),
            'items' => $this->getTotalItems(),
            'maxOffset' => $this->getMaxOffset(),
            'mode' => $this->getNavigationMode(),
            'size' => $this->getNavigationSize(),
            'showInfo' => $this->showInfo,
            'pipeline' => $this->getPipeline(),
            'includeCss' => $this->includeCss,
        ];
    }

    private function getConfigurationTheme(): string
    {
        return 'application' === $this->getTheme()
            ? 'pagination/application.html.twig'
            : $this->getThemeTemplate();
    }

    private function getAccentColor(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/D', $this->accentColor)
            ? $this->accentColor
            : '#78d64b';
    }
}

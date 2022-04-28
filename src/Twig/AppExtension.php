<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('link', [$this, 'formatLink']),
        ];
    }

    public function formatLink(string $link): string
    {
        return "<a href='https://id.eaufrance.fr/par/$link'>$link</a>";
    }

}
<?php

namespace Kanboard\Twig;
//use Twig\Extension\AbstractExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationFilter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('t', [$this, 'translate']),
        ];
    }

    public function translate($text) {
        return t($text);
    }
}

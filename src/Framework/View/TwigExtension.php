<?php

namespace Echo\Framework\View;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction("csrf", [$this, "csrf"]),
            new TwigFunction("uri", [$this, "uri"]),
            new TwigFunction("old", [$this, "old"])
        ];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function csrf(): string
    {
        $token = session()->get("csrf_token");
        return twig()->render("components/csrf.html.twig", ["token" => $token]);
    }

    public function uri(string $name)
    {
        return uri($name);
    }

    public function old(string $name, mixed $default = null)
    {
        return request()->request->get($name) ?? $default;
    }
}

<?php

namespace Echo\Framework\Http;

use Echo\Framework\Http\Request\{Get, Post, Files, Cookie, Headers};
use Echo\Framework\Http\Request\Request as Req;
use Echo\Interface\Http\Request as HttpRequest;

class Request implements HttpRequest
{
    public Get $get;
    public Post $post;
    public Files $files;
    public Req $request;
    public Cookie $cookie;
    public Headers $headers;

    private array $attributes = [];

    public function __construct(
        array $get = [],
        array $post = [],
        array $request = [],
        array $files = [],
        array $cookie = [],
        array $headers = [],
    ) {
        $this->get = new Get($get);
        $this->post = new Post($post);
        $this->request = new Req($request);
        $this->files = new Files($files);
        $this->cookie = new Cookie($cookie);
        $this->headers = new Headers($headers);
    }

    public function isHTMX(): bool
    {
        return $this->headers->has('HX-Request');
    }

    public function getUri(): string
    {
        return strtok($_SERVER["REQUEST_URI"], '?');
    }

    public function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name];
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getClientIp(): ?string
    {
        if (php_sapi_name() == "cli") return null;
        return  isset($_SERVER['HTTP_CLIENT_IP'])
            ? $_SERVER['HTTP_CLIENT_IP']
            : (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                ? $_SERVER['HTTP_X_FORWARDED_FOR']
                : $_SERVER['REMOTE_ADDR']);
    }
}

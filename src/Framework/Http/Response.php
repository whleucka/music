<?php

namespace Echo\Framework\Http;

use Echo\Interface\Http\Response as HttpResponse;

class Response implements HttpResponse
{
    private int $code;

    public function __construct(private ?string $content, ?int $code = null)
    {
        if (is_null($code)) {
            $this->code = http_response_code();
        } else {
            $this->code = $code;
        }
    }

    public function send(): void
    {
        ob_start();
        ob_clean();
        http_response_code($this->code);
        echo $this->content;
    }

    public function setHeader(string $name, string $value): void
    {
        header("$name: $value");
    }
}

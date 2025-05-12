<?php

namespace Echo\Framework\Http;

use Echo\Interface\Http\Response as HttpResponse;

class Response implements HttpResponse
{
    private int $code;
    private array $headers = [];

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
        $this->sendHeaders();
        http_response_code($this->code);
        echo $this->content;
    }

    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    private function sendHeaders(): void
    {
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }
}

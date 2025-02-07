<?php

namespace Echo\Framework\Http;

use Echo\Interface\Http\Controller as HttpController;
use Echo\Interface\Http\Request;

class Controller implements HttpController
{
    protected Request $request;
    private array $validation_errors = [];
    private array $validation_messages = [
        "required" => "Required field",
        "string" => "Must be a string",
        "array" => "Must be an array",
        "date" => "Invalid date format",
        "numeric" => "Must be a numeric value",
        "email" => "Invalid email address",
        "integer" => "Must be an integer",
        "float" => "Must be a floating-point number",
        "boolean" => "Must be a boolean value",
        "url" => "Invalid URL format",
        "ip" => "Invalid IP address",
        "ipv4" => "Must be a valid IPv4 address",
        "ipv6" => "Must be a valid IPv6 address",
        "mac" => "Invalid MAC address",
        "domain" => "Invalid domain name",
        "uuid" => "Invalid UUID format",
    ];

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getValiationErrors()
    {
        return $this->validation_errors;
    }

    public function validate(array $ruleset): ?object
    {
        $valid = true;
        $request = $this->request->request->data();
        $data = [];

        foreach ($ruleset as $field => $set) {
            foreach ($set as $rule) {
                $request_value = $request[$field] ?? null;
                $result = match($rule) {
                    'required' => !is_null($request_value) && $request_value !== '',
                    'string' => is_string($request_value),
                    'array' => is_array($request_value),
                    'date' => strtotime($request_value) !== false,
                    'numeric' => is_numeric($request_value),
                    'email' => filter_var($request_value, FILTER_VALIDATE_EMAIL) !== false,
                    'integer' => filter_var($request_value, FILTER_VALIDATE_INT) !== false,
                    'float' => filter_var($request_value, FILTER_VALIDATE_FLOAT) !== false,
                    'boolean' => filter_var($request_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
                    'url' => filter_var($request_value, FILTER_VALIDATE_URL) !== false,
                    'ip' => filter_var($request_value, FILTER_VALIDATE_IP) !== false,
                    'ipv4' => filter_var($request_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false,
                    'ipv6' => filter_var($request_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false,
                    'mac' => filter_var($request_value, FILTER_VALIDATE_MAC) !== false,
                    'domain' => filter_var($request_value, FILTER_VALIDATE_DOMAIN) !== false,
                    'uuid' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $request_value),
                    default => throw new \Error("undefined validation rule: $rule")
                };
                if ($result) {
                    $data[$field] = $request[$field];
                } else {
                    $this->addValidationError($field, $this->validation_messages[$rule] ?? "Invalid");
                }
                $valid &= $result;
            }
        }
        return $valid ? (object)$data : null;
    }

    protected function setValidationMessage(string $rule, string $message)
    {
        $this->validation_messages[$rule] = $message;
    }

    protected function addValidationError(string $field, string $message)
    {
        $this->validation_errors[$field][] = $message;
    }

    protected function getDefaultTemplateData(): array
    {
        return [
            "app" => config("app"),
            "validation_errors" => $this->validation_errors,
        ];
    }

    protected function render(string $template, array $data = []): string
    {
        $twig = container()->get(\Twig\Environment::class);
        $data = array_merge($data, $this->getDefaultTemplateData());
        return $twig->render($template, $data);
    }
}

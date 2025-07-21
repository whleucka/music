<?php

namespace Echo\Framework\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Group
{
    public function __construct(
        public string $prefix = '',
        public array $middleware = []
    ) {}
}

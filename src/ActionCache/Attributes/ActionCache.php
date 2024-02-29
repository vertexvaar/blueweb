<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\ActionCache\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ActionCache
{
    public function __construct(
        public int $ttl = 60 * 60 * 24,
        public array $matches = [],
        public array $params = [],
        public bool $interchangeableParams = true,
    ) {
    }
}

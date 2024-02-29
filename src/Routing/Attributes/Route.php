<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Route
{
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const CONNECT = 'CONNECT';
    public const OPTIONS = 'OPTIONS';
    public const TRACE = 'TRACE';

    public function __construct(
        public string $path,
        public string $method = 'GET',
        public int $priority = 100,
    ) {
    }
}

<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\ApiTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class ApiTagParser implements TagParserInterface
{
    public function parse(string $source, ?string $specialization, string $type) : ?TagInterface
    {
        return new ApiTag();
    }
}

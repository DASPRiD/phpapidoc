<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\InternalTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class InternalTagParser implements TagParserInterface
{
    public function parse(string $source, ?string $specialization, string $type) : ?TagInterface
    {
        return new InternalTag();
    }
}

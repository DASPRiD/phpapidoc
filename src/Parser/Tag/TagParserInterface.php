<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag;

use PhpApiDoc\Structure\Tag\TagInterface;

interface TagParserInterface
{
    public const NONE = 'none';
    public const DESCRIPTION = 'description';
    public const SIGNATURE = 'signature';
    public const INLINE_PHPDOC = 'inline-phpdoc';

    public const SEMANTIC_VERSION_REGEX = '
        [1-9]\d*\\.[1-9]\d*\\.[1-9]\d*
        (?:-[0-9A-Za-z\\-]+
        (?:\\.[0-9A-Za-z\\-]+)*)?
    ';

    public function parse(string $source, ?string $specialization, string $type) : ?TagInterface;
}

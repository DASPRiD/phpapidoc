<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\DescriptionParser;
use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\TodoTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class TodoTagParser implements TagParserInterface
{
    /**
     * @var DescriptionParser
     */
    private $descriptionParser;

    public function __construct(DescriptionParser $descriptionParser)
    {
        $this->descriptionParser = $descriptionParser;
    }

    public function parse(string $source, ?string $specialization, string $type) : ?TagInterface
    {
        if (self::NONE === $type) {
            return TodoTag::empty();
        }

        if (self::DESCRIPTION !== $type) {
            return null;
        }

        return new TodoTag($this->descriptionParser->parse($source));
    }
}

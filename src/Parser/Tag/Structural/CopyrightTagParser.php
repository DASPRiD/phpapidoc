<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\DescriptionParser;
use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\CopyrightTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class CopyrightTagParser implements TagParserInterface
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
        if (self::DESCRIPTION !== $type) {
            return null;
        }

        return new CopyrightTag($this->descriptionParser->parse($source));
    }
}

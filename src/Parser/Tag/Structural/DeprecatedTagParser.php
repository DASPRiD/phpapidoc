<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\DescriptionParser;
use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\DeprecatedTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class DeprecatedTagParser implements TagParserInterface
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
            return DeprecatedTag::empty();
        }

        if (self::DESCRIPTION !== $type) {
            return null;
        }

        $result = preg_match('(^
            (?<startingVersion>' . self::SEMANTIC_VERSION_REGEX . ')?
            (?::(?<endingVersion>' . self::SEMANTIC_VERSION_REGEX . '))?
            [ \t]*
            (?<description>.+)?
        $)xS', $source, $matches);

        if (0 === $result) {
            return DeprecatedTag::empty();
        }

        $startingVersion = null;
        $endingVersion = null;
        $description = null;

        if (isset($matches['startingVersion']) && '' !== $matches['startingVersion']) {
            $startingVersion = $matches['startingVersion'];
        }

        if (isset($matches['endingVersion']) && '' !== $matches['endingVersion']) {
            $endingVersion = $matches['endingVersion'];
        }

        if (isset($matches['description']) && '' !== $matches['description']) {
            $description = $matches['description'];
        }

        return new DeprecatedTag($startingVersion, $endingVersion, $description);
    }
}

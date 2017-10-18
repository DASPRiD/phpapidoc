<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser\Tag\Structural;

use PhpApiDoc\Parser\DescriptionParser;
use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\Tag\Structural\VersionTag;
use PhpApiDoc\Structure\Tag\TagInterface;

final class VersionTagParser implements TagParserInterface
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
            return VersionTag::empty();
        }

        if (self::DESCRIPTION !== $type) {
            return null;
        }

        $result = preg_match('(^
            (?<version>' . self::SEMANTIC_VERSION_REGEX . ')?
            [ \t]*
            (?<description>.+)?
        $)xS', $source, $matches);

        if (0 === $result) {
            return VersionTag::empty();
        }

        $version = null;
        $description = null;

        if (isset($matches['version']) && '' !== $matches['version']) {
            $version = $matches['startingVersion'];
        }

        if (isset($matches['description']) && '' !== $matches['description']) {
            $description = $matches['description'];
        }

        return new VersionTag($version, $description);
    }
}

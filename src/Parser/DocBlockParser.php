<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser;

use PhpApiDoc\Parser\Tag\TagParserInterface;
use PhpApiDoc\Structure\DocBlock;
use PhpApiDoc\Structure\Tag\TagInterface;
use PhpApiDoc\Structure\Tag\UnknownDescriptionTag;
use PhpApiDoc\Structure\Tag\UnknownEmptyTag;
use PhpApiDoc\Structure\Tag\UnknownInlinePhpdocTag;

/**
 * Doc block parser implementation of PSR-5.
 *
 * This parser implements the PSR-5 standard for doc blocks, following the specification as much as possible.
 */
final class DocBlockParser
{
    /**
     * @var DescriptionParser
     */
    private $descriptionParser;

    /**
     * @var TagParserInterface[]
     */
    private $tagParsers = [];

    public function __construct(DescriptionParser $descriptionParser)
    {
        $this->descriptionParser = $descriptionParser;
    }

    /**
     * Registers a tag parser and associates it with a tag.
     *
     * This method is meant for registering structural element tag parsers. Inline tag parsers are not hanlded by this
     * class.
     */
    public function registerTagParser(string $name, TagParserInterface $tagParser) : void
    {
        $this->tagParsers[$name] = $tagParser;
    }

    /**
     * Parses a doc block text into a structured object.
     *
     * Even if a doc block is empty or does not follow the PSR-5 specification a DocBlock object will be returned. This
     * ensures a consistent behavior across the entire generation process.
     */
    public function parse(string $docBlock) : DocBlock
    {
        $parts = $this->splitParts($docBlock);

        if (null === $parts) {
            return DocBlock::empty();
        }

        $tags = $this->parseTags($parts['tagsSource']);

        return new DocBlock(
            $parts['summary'],
            $this->descriptionParser->parse($parts['description']),
            ...$tags
        );
    }

    /**
     * Parses the tags source and returns an array of tags.
     *
     * @return TagInterface[]
     */
    private function parseTags(string $tagsSource) : array
    {
        preg_match_all('(
            (?:^|(?<=\n))
            (?<tag>
                @
                (?<name>[A-Za-z\\\\][A-Za-z0-9\\\\_\\-]*)
                (?::(?<specialization>)[A-Za-z0-9\-]+)?
    
                (?<details>
                    [ \t]*
                    (?:
                        (?:
                            [ \t](?![ \t\\(\\{])
                            (?<description>
                                [^\n]+
                                
                                (?<descriptionEnd>
                                    \n+(?=@) # Only empty lines until an at-character remain
                                    | $ # Doc block end
                                )?
                                
                                (?(descriptionEnd)
                                    # Exit if descriptionEnd matched
                                    |
                                    \n(?&description) # Eat the new-line and repeat the description routine 
                                )
                            )
                        )
                        |
                        (?<signature>
                            \\(
                            [ \t]* [^,\\)]+ ,? [ \t]*
                            \\)
                        )
                        |
                        (?<inlinePhpdoc>
                            \\{
                            (?&tag)
                            \\}
                        )
                    )
                )?
            )
        )xS', $tagsSource, $matches, PREG_SET_ORDER);

        return $this->processTagsMatches($matches);
    }

    /**
     * Processes the matches for all tags.
     *
     * @return TagInterface[]
     */
    private function processTagsMatches(array $matches) : array
    {
        $tags = [];

        foreach ($matches as $match) {
            $name = $match['name'];
            $specialization = null;

            if (isset($match['specialization']) && '' !== $match['specialization']) {
                $specialization = $match['specialization'];
            }

            $type = TagParserInterface::NONE;
            $source = '';

            if (isset($match['description']) && '' !== $match['description']) {
                $type = TagParserInterface::DESCRIPTION;
                $source = trim($match['description']);
            } elseif (isset($match['signature']) && '' !== $match['signature']) {
                $type = TagParserInterface::SIGNATURE;
                $source = $match['signature'];
            } elseif (isset($match['inlinePhpdoc']) && '' !== $match['inlinePhpdoc']) {
                $type = TagParserInterface::INLINE_PHPDOC;
                $source = $match['inlinePhpdoc'];
            }

            if (array_key_exists($name, $this->tagParsers)) {
                $tag = $this->tagParsers[$name]->parse($source, $specialization, $type);

                if (null !== $tag) {
                    $tags[] = $tag;
                }

                continue;
            }

            switch ($type) {
                case TagParserInterface::NONE:
                    $tags[] = new UnknownEmptyTag($name, $specialization);
                    break;

                case TagParserInterface::DESCRIPTION:
                    $tags[] = new UnknownDescriptionTag($name, $specialization, $source);
                    break;

                case TagParserInterface::SIGNATURE:
                    $tags[] = new UnknownDescriptionTag($name, $specialization, $source);
                    break;

                case TagParserInterface::INLINE_PHPDOC:
                    $tags[] = new UnknownInlinePhpdocTag($name, $specialization, $source);
                    break;
            }
        }

        return $tags;
    }

    /**
     * Splits a doc block into multiple parts.
     *
     * This method will parse a doc block and return an array containing three parts:
     *
     * - the summary `summary`
     * - the description `description`
     * - the source of all tags `tagsSource`
     *
     * If the doc block does not follow the PSR-5 specification and thus doesn't match, null is returned instead.
     */
    private function splitParts(string $docBlock) : ?array
    {
        $result = preg_match('(^
            # Summary
            (?<summary>
                (?!@) # Summaries do not start with an at-character
                [^\n]*

                (?<summaryEnd>
                    \n+(?=@) # Only empty lines until an at-character remain
                    | (?<=\.)\n+ # A dot followed by one or more new-lines
                    | \n{2,} # Two ore more new-lines
                    | $ # Doc block end
                )?

                (?(summaryEnd)
                    # Exit if summaryEnd matched
                    |
                    \n(?&summary) # Eat the new-line and repeat the summary routine 
                )
            )?

            # Description
            (?<description>
                (?!@) # Descriptions do not start with an at-character
                [^\n]*
                
                (?<descriptionEnd>
                    \n+(?=@) # Only empty lines until an at-character remain
                    | $ # Doc block end
                )?
                
                (?(descriptionEnd)
                    # Exit if descriptionEnd matched
                    |
                    \n(?&description) # Eat the new-line and repeat the description routine 
                )
            )?

            # Anything remaining are tags
            (?<tagsSource>.*)
        )xSs', $this->normalize($docBlock), $matches);

        if (0 === $result) {
            return null;
        }

        return [
            'summary' => $matches['summary'],
            'description' => $matches['description'],
            'tagsSource' => $matches['tagsSource'],
        ];
    }

    /**
     * Normalized the doc block text.
     *
     * In this method, some normalization is performed to assist future parsing. The following steps are executed:
     *
     * - all non-Unix new-lines are replaced Unix ones.
     * - all occurrences of comment marker are removed
     * - leading and trailing whitespace is removed from each line
     */
    private function normalize(string $docBlock) : string
    {
        $docBlock = str_replace(["\r\n", "\r"], "\n", $docBlock);
        $docBlock = preg_replace('((?:
            ^[ \t]*/\*\*(?:[ \t]*\n)?
            |
            (?<=\n)[ \t]*\*(?:/[ \t]*$)?
        ))xS', '', $docBlock);
        $docBlock = preg_replace('((?:
            (?:^|(?<=\n))[ \t]+
            |
            [ \t]+(?:$|(?=\n))
        ))xS', '', $docBlock);
        return $docBlock;
    }
}

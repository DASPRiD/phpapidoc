<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser;

use ParsedownExtra;

final class DescriptionParser
{
    /**
     * @var ParsedownExtra
     */
    private $markdownParser;

    public function __construct(ParsedownExtra $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function parse(string $description)
    {
        // @todo parse inline tags

        return $this->markdownParser->parse($description);
    }
}

<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure;

use PhpApiDoc\Structure\Tag\TagInterface;

/**
 * Doc block definition holding the summary, description and all defined tags.
 */
final class DocBlock
{
    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $description;

    /**
     * @var TagInterface[]
     */
    private $tags;

    /**
     * @var self|null
     */
    private static $empty;

    public function __construct($summary, $description, TagInterface ...$tags)
    {
        $this->summary = $summary;
        $this->description = $description;
        $this->tags = $tags;
    }

    public static function empty() : self
    {
        return self::$empty ?: self::$empty = new self('', '');
    }
}

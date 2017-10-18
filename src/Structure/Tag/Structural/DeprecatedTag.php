<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag\Structural;

use PhpApiDoc\Structure\Tag\TagInterface;

final class DeprecatedTag implements TagInterface
{
    /**
     * @var string|null
     */
    private $startingVersion;

    /**
     * @var string|null
     */
    private $endingVersion;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var self|null
     */
    private static $empty;

    public function __construct(?string $startingVersion, ?string $endingVersion, ?string $description)
    {
        $this->startingVersion = $startingVersion;
        $this->endingVersion = $endingVersion;
        $this->description = $description;
    }

    public static function empty() : self
    {
        return self::$empty ?: self::$empty = new self(null, null, null);
    }
}

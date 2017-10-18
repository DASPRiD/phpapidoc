<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag\Structural;

use PhpApiDoc\Structure\Tag\TagInterface;

final class VersionTag implements TagInterface
{
    /**
     * @var string|null
     */
    private $version;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var self|null
     */
    private static $empty;

    public function __construct(?string $version, ?string $description)
    {
        $this->version = $version;
        $this->description = $description;
    }

    public static function empty() : self
    {
        return self::$empty ?: self::$empty = new self(null, null);
    }
}

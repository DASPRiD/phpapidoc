<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag\Structural;

use PhpApiDoc\Structure\Tag\TagInterface;

final class TodoTag implements TagInterface
{
    /**
     * @var string|null
     */
    private $description;

    /**
     * @var self|null
     */
    private static $empty;

    public function __construct(?string $description)
    {
        $this->description = $description;
    }

    public static function empty() : self
    {
        return self::$empty ?: self::$empty = new self(null);
    }
}

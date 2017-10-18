<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag\Structural;

use PhpApiDoc\Structure\Tag\TagInterface;

final class CopyrightTag implements TagInterface
{
    /**
     * @var string
     */
    private $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }
}

<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag\Structural;

use PhpApiDoc\Structure\Tag\TagInterface;

final class AuthorTag implements TagInterface
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $emailAddress;

    public function __construct(?string $name, ?string $emailAddress)
    {
        $this->name = $name;
        $this->emailAddress = $emailAddress;
    }
}

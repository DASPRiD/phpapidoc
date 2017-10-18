<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag;

final class UnknownEmptyTag implements TagInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $specialization;

    public function __construct(string $name, ?string $specialization)
    {
        $this->name = $name;
        $this->specialization = $specialization;
    }
}

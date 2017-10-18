<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag;

final class UnknownDescriptionTag implements TagInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $specialization;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name, ?string $specialization, string $description)
    {
        $this->name = $name;
        $this->specialization = $specialization;
        $this->description = $description;
    }
}

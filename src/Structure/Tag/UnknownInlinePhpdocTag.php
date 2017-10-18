<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag;

final class UnknownInlinePhpdocTag implements TagInterface
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
    private $inlinePhpdoc;

    public function __construct(string $name, ?string $specialization, string $inlinePhpdoc)
    {
        $this->name = $name;
        $this->specialization = $specialization;
        $this->inlinePhpdoc = $inlinePhpdoc;
    }
}

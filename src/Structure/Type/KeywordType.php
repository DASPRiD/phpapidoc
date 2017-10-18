<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Type;

final class KeywordType implements TypeInterface
{
    /**
     * @var string
     */
    private $keyword;

    public function __construct(string $keyword)
    {
        $this->keyword = $keyword;
    }

    public function getKeyword() : string
    {
        return $this->keyword;
    }
}

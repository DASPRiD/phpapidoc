<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Type;

final class ArrayType implements TypeInterface
{
    /**
     * @var TypeInterface
     */
    private $baseType;

    /**
     * @var int
     */
    private $levels;

    public function __construct(TypeInterface $baseType, int $levels)
    {
        $this->baseType = $baseType;
        $this->levels = $levels;
    }

    public function getBaseType() : TypeInterface
    {
        return $this->baseType;
    }

    public function getLevels() : int
    {
        return $this->levels;
    }
}

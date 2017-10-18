<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Type;

final class UnionType implements TypeInterface
{
    /**
     * @var TypeInterface[]
     */
    private $types;

    public function __construct(TypeInterface ...$types)
    {
        $this->types = $types;
    }
}

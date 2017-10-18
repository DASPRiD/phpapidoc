<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Type;

final class ClassNameType implements TypeInterface
{
    /**
     * @var string
     */
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName() : string
    {
        return $this->className;
    }
}

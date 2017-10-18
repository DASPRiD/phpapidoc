<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Type;

/**
 * Type representing generics.
 */
final class GenericType implements TypeInterface
{
    /**
     * @var ClassNameType|null
     */
    private $collectionType;

    /**
     * @var TypeInterface|null
     */
    private $keyType;

    /**
     * @var TypeInterface
     */
    private $valueType;

    /**
     * Creates a new generic type.
     *
     * A collection type can either be a `ClassNameType` or null, in which case the type is considered to be `array`.
     */
    public function __construct(?ClassNameType $collectionType, ?TypeInterface $keyType, TypeInterface $valueType)
    {
        $this->collectionType = $collectionType;
        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    public function getCollectionType() : ?ClassNameType
    {
        return $this->collectionType;
    }

    public function getKeyType() : ?TypeInterface
    {
        return $this->keyType;
    }

    public function getValueType() : TypeInterface
    {
        return $this->valueType;
    }
}

<?php
declare(strict_types = 1);

namespace PhpApiDocTest\Parser;

use PhpApiDoc\Parser\TypeExpressionParser;
use PhpApiDoc\Structure\Type\ArrayType;
use PhpApiDoc\Structure\Type\ClassNameType;
use PhpApiDoc\Structure\Type\GenericType;
use PhpApiDoc\Structure\Type\KeywordType;
use PhpApiDoc\Structure\Type\UnionType;
use PHPUnit\Framework\TestCase;

final class TypeExpressionParserTest extends TestCase
{
    public function keywords() : array
    {
        return [
            ['array'],
            ['bool'],
            ['callable'],
            ['false'],
            ['float'],
            ['int'],
            ['mixed'],
            ['null'],
            ['object'],
            ['resource'],
            ['self'],
            ['static'],
            ['string'],
            ['true'],
            ['void'],
            ['$this'],
        ];
    }

    /**
     * @dataProvider keywords
     */
    public function testKeywordMatching(string $keyword) : void
    {
        $parser = new TypeExpressionParser();
        $type = $parser->parse($keyword);

        $this->assertInstanceOf(KeywordType::class, $type);
        $this->assertSame($keyword, $type->getKeyword());
    }

    public function classNames() : array
    {
        return [
            ['Foo'],
            ['Foo_Bar123_baz'],
            ['Foo\\Bar123\\baz'],
            ['\\Foo\\Bar123\\baz'],
            ['Foo\\π\\baz'],
        ];
    }

    /**
     * @dataProvider classNames
     */
    public function testClassNameMatching(string $className) : void
    {
        $parser = new TypeExpressionParser();
        $type = $parser->parse($className);

        $this->assertInstanceOf(ClassNameType::class, $type);
        $this->assertSame($className, $type->getClassName());
    }

    public function arrays() : array
    {
        return [
            ['int[]', KeywordType::class, 1],
            ['int[][][]', KeywordType::class, 3],
            ['\\Foo\\Bar[][]', ClassNameType::class, 2],
            ['(array)[]', KeywordType::class, 1],
            ['(int|\\Foo\\Bar)[][]', UnionType::class, 2],
            ['ArrayObject<int, string>[]', GenericType::class, 1],
            ['(ArrayObject<int, string>|float)[]', UnionType::class, 1],
        ];
    }

    /**
     * @dataProvider arrays
     */
    public function testArray(string $typeExpression, string $expectedBaseType, int $expectedLevels) : void
    {
        $parser = new TypeExpressionParser();
        $type = $parser->parse($typeExpression);

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertInstanceOf($expectedBaseType, $type->getBaseType());
        $this->assertSame($expectedLevels, $type->getLevels());
    }

    public function generics() : array
    {
        return [
            ['array<int>', null, null, KeywordType::class],
            ['ArrayObject<int, string>', ClassNameType::class, KeywordType::class, KeywordType::class],
            ['ArrayObject<SplObjectStorage, int[]>', ClassNameType::class, ClassNameType::class, ArrayType::class],
            ['ArrayObject<string, (int|string)[]>', ClassNameType::class, KeywordType::class, ArrayType::class],
            ['ArrayObject<string, int|string>', ClassNameType::class, KeywordType::class, UnionType::class],
            ['ArrayObject<int|float, int|string>', ClassNameType::class, UnionType::class, UnionType::class],
        ];
    }

    /**
     * @dataProvider generics
     */
    public function testGenerics(
        string $typeExpression,
        ?string $expectedCollectionTypeClassName,
        ?string $expectedKeyTypeClassName,
        string $expectedValueTypeClassName
    ) : void {
        $parser = new TypeExpressionParser();
        $type = $parser->parse($typeExpression);

        $this->assertInstanceOf(GenericType::class, $type);

        if (null === $expectedCollectionTypeClassName) {
            $this->assertNull($type->getCollectionType());
        } else {
            $this->assertInstanceOf($expectedCollectionTypeClassName, $type->getCollectionType());
        }

        if (null === $expectedKeyTypeClassName) {
            $this->assertNull($type->getKeyType());
        } else {
            $this->assertInstanceOf($expectedKeyTypeClassName, $type->getKeyType());
        }

        $this->assertInstanceOf($expectedValueTypeClassName, $type->getValueType());
    }

    public function invalidExpressions() : array
    {
        return [
            ['foo['],
            ['foo]'],
            ['1'],
            ['string<string>'],
            ['array<(string)>'],
            ['array<string'],
            ['arraystring>'],
            ['array()'],
            ['$that'],
        ];
    }

    /**
     * @dataProvider invalidExpressions
     */
    public function testInvalidExpressions(string $typeExpression) : void
    {
        $parser = new TypeExpressionParser();
        $this->assertNull($parser->parse($typeExpression));
    }
}

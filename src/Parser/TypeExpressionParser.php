<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser;

use PhpApiDoc\Structure\Type\ArrayType;
use PhpApiDoc\Structure\Type\ClassNameType;
use PhpApiDoc\Structure\Type\GenericType;
use PhpApiDoc\Structure\Type\KeywordType;
use PhpApiDoc\Structure\Type\TypeInterface;
use PhpApiDoc\Structure\Type\UnionType;

/**
 * Parser implementation conforming to PSR-5 defined type expressions.
 *
 * This implementation is following the PSR-5 type expressions as close a possible. For instance, the original ABNF does
 * not allow for underlines in class names, which is probably an oversight, since it is still in proposed state. Also,
 * since the original ABNF allowed for an infinite recursion, the logic around this has been modified to exclude that
 * case.
 *
 * Instead of parsing the entire expression with a single regular expression, it is parsed piece by piece, because it
 * extracts information through recursion, which is not possible through a regular expression (it can only match
 * recursion, but not extract it).
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md#appendix-a-types
 */
final class TypeExpressionParser
{
    /**
     * @var int|null
     */
    private $position;

    /**
     * @var int|null
     */
    private $length;

    /**
     * Parses the given type expression according to PSR-5.
     */
    public function parse(string $typeExpression) : ?TypeInterface
    {
        $this->position = 0;
        $this->length = strlen($typeExpression);
        return $this->matchTypeExpression($typeExpression);
    }

    /**
     * Matches a type expression.
     *
     * Given a specific type expression, this will match that expression either till the end of the string or when after
     * a successful type match one of the defined stop characters is matched.
     *
     * A type expression consists of one or more types separated by a pipe (`|`) character.
     */
    private function matchTypeExpression(string $typeExpression, ?string $stopCharacters = null) : ?TypeInterface
    {
        $startPosition = $this->position;
        $types = [];

        do {
            if ($this->position > $startPosition) {
                if (0 === preg_match('(\G\\|)xS', $typeExpression, $matches, 0, $this->position)) {
                    return null;
                }

                ++$this->position;
            }

            $type = $this->matchType($typeExpression);

            if (null === $type) {
                return null;
            }

            $types[] = $type;
        } while (
            $this->position < $this->length && (
                null === $stopCharacters
                || false === strpos($stopCharacters, $typeExpression[$this->position])
            )
        );

        if (count($types) > 1) {
            return new UnionType(...$types);
        }

        return $types[0];
    }

    /**
     * Matches a single type.
     *
     * A single type can either be an array, a keyword or a class name. This implementation tests arrays first, as they
     * may begin with a keyword or a class name, and thus halt the type parsing too early. Keywords are also tested
     * before class names, the pattern for class names can match keywords as well.
     */
    private function matchType(string $typeExpression) : ?TypeInterface
    {
        return $this->matchArray($typeExpression)
            ?? $this->matchKeyword($typeExpression)
            ?? $this->matchClassName($typeExpression);
    }

    /**
     * Matches a single keyword.
     *
     * This will match either one of the reserved PHP keywords for types or one of the keywords defined by PSR-5. Those
     * extra keywords are:
     *
     * - `false`
     * - `mixed`
     * - `static`
     * - `true`
     * - `$this`
     */
    private function matchKeyword(string $typeExpression) : ?KeywordType
    {
        $result = preg_match('(\G
            (?:
                array | bool | callable | false | float | int | mixed | null | object | resource | self
                | static | string | true | void | \\$this
            )
        )xS', $typeExpression, $matches, 0, $this->position);

        if (0 === $result) {
            return null;
        }

        $this->position += strlen($matches[0]);
        return new KeywordType($matches[0]);
    }

    /**
     * Matches a single (fully qualified) class name.
     *
     * This will match any class name, whether it is fully qualified or not. In contrast to PSR-5, this also allows
     * underline characters (`_`) to be part of a class name.
     */
    private function matchClassName(string $typeExpression) : ?ClassNameType
    {
        $result = preg_match('(\G
            (?:
                \\\\?
                (?<label>
                    [A-Za-z_\x7f-\xff]
                    (?:[A-Za-z0-9_\x7f-\xff])*
                )
                (?:\\\\(?&label))*
            )
        )xS', $typeExpression, $matches, 0, $this->position);

        if (0 === $result) {
            return null;
        }

        $this->position += strlen($matches[0]);
        return new ClassNameType($matches[0]);
    }

    /**
     * Matches any type of array.
     *
     * The possible array types are:
     *
     * - simple array types (`int[]`)
     * - union array types (`(int|string)[]´)
     * - generics (`array<string, int>`)
     *
     * It is important to test for those types in a specific order, as one of the matches could interfere with the
     * others otherwise.
     */
    private function matchArray(string $typeExpression) : ?TypeInterface
    {
        return $this->matchArrayType($typeExpression)
            ?? $this->matchUnionArrayType($typeExpression)
            ?? $this->matchGeneric($typeExpression);
    }

    /**
     * Matches a simple array.
     *
     * Simple array types consist of a base type, which can be a keyword, generic, class name or an union array, and
     * a number of levels for that array. The levels are defined by the number of square brackets followed by the base
     * type. For instance, `[][]` would mean two levels.
     *
     * It is important to test for the base type in a specific order, as one of the matches could interfere with the
     * others otherwise.
     */
    private function matchArrayType(string $typeExpression) : ?ArrayType
    {
        $startPosition = $this->position;
        $baseType = $this->matchKeyword($typeExpression)
            ?? $this->matchGeneric($typeExpression)
            ?? $this->matchClassName($typeExpression)
            ?? $this->matchUnionArrayType($typeExpression);

        if (null === $baseType) {
            return null;
        }

        if (0 === preg_match('(\G(?:\\[\\])+)', $typeExpression, $matches, 0, $this->position)) {
            // Not an array type, reset the position to try to parse that type normally.
            $this->position = $startPosition;
            return null;
        }

        $this->position += strlen($matches[0]);
        return new ArrayType($baseType, intdiv(strlen($matches[0]), 2));
    }

    /**
     * Matches an union array.
     *
     * Union arrays are very similar to simple arrays, except that their base type can be any kind of expression,
     * enclosed in parentheses (`()`).
     */
    private function matchUnionArrayType(string $typeExpression) : ?ArrayType
    {
        if (0 === preg_match('(\G\\()', $typeExpression, $matches, 0, $this->position)) {
            return null;
        }

        ++$this->position;
        $baseType = $this->matchTypeExpression($typeExpression, ')');

        if (null === $baseType) {
            return null;
        }

        if (0 === preg_match('(\G\\))', $typeExpression, $matches, 0, $this->position)) {
            return null;
        }

        ++$this->position;

        if (0 === preg_match('(\G(?:\\[\\])+)', $typeExpression, $matches, 0, $this->position)) {
            return null;
        }

        $this->position += strlen($matches[0]);
        return new ArrayType($baseType, intdiv(strlen($matches[0]), 2));
    }

    /**
     * Matches a generic definition.
     *
     * A generic definition consists of a base type, which can be either the keyword `array` or any class name, and
     * a key and a value type. The key and value types are enclosed in chevrons (`<>`) and the key type is optional. If
     * present, the key type must occur first and be followed by a comma (`,`) and one or more spaces or tabs.
     */
    private function matchGeneric(string $typeExpression) : ?GenericType
    {
        $startPosition = $this->position;
        $collectionType = $this->matchKeyword($typeExpression)
            ?? $this->matchClassName($typeExpression);

        if (null === $collectionType) {
            return null;
        }

        if ($collectionType instanceof KeywordType) {
            if ('array' !== $collectionType->getKeyword()) {
                // Unsupported keyword for generics, reset the position to try to parse that keyword normally.
                $this->position = $startPosition;
                return null;
            }

            $collectionType = null;
        }

        if (0 === preg_match('(\G<)', $typeExpression, $matches, 0, $this->position)) {
            // Not a generic, reset the position to try to parse that type normally.
            $this->position = $startPosition;
            return null;
        }

        ++$this->position;
        $valueType = $this->matchTypeExpression($typeExpression, ',>');
        $keyType = null;

        if (null === $valueType) {
            return null;
        }

        if (0 !== preg_match('(\G,[ \t]*)', $typeExpression, $matches, 0, $this->position)) {
            $this->position += strlen($matches[0]);
            $keyType = $valueType;
            $valueType = $this->matchTypeExpression($typeExpression, '>');

            if (null === $valueType) {
                return null;
            }
        }

        if (0 === preg_match('(\G>)', $typeExpression, $matches, 0, $this->position)) {
            return null;
        }

        ++$this->position;
        return new GenericType($collectionType, $keyType, $valueType);
    }
}

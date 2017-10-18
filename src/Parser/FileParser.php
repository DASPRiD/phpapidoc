<?php
declare(strict_types = 1);

namespace PhpApiDoc\Parser;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

final class FileParser
{
    /**
     * @var DocBlockParser
     */
    private $docBlockParser;

    public function __construct(DocBlockParser $docBlockParser)
    {
        $this->docBlockParser = $docBlockParser;
    }

    public function parse(string $pathname)
    {
        $astLocator = (new BetterReflection())->astLocator();
        $directorySourceLocator = new SingleFileSourceLocator($pathname, $astLocator);
        $reflector = new ClassReflector($directorySourceLocator);
        $classes = $reflector->getAllClasses();

        foreach ($classes as $class) {
            var_dump($this->docBlockParser->parse($class->getDocComment()));
        }
    }
}

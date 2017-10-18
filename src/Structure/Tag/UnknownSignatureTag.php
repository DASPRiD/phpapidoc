<?php
declare(strict_types = 1);

namespace PhpApiDoc\Structure\Tag;

final class UnknownSignatureTag implements TagInterface
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
    private $signature;

    public function __construct(string $name, ?string $specialization, string $signature)
    {
        $this->name = $name;
        $this->specialization = $specialization;
        $this->signature = $signature;
    }
}

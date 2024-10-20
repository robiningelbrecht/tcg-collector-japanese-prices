<?php

declare(strict_types=1);

namespace App\Infrastructure\Xml;

final readonly class Xpath
{
    private \DOMXPath $xpath;

    public function __construct(
        string $dom,
    ) {
        // Suppress any faulty HTML errors.
        libxml_use_internal_errors(true);
        $domDoc = new \DOMDocument();
        $domDoc->loadHTML($dom);

        $this->xpath = new \DOMXPath($domDoc);
    }

    public function query(string $expression): \DOMNodeList|false
    {
        return $this->xpath->query($expression);
    }
}

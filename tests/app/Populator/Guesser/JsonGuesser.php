<?php

declare(strict_types=1);

namespace ApiExtension\App\Populator\Guesser;

use Doctrine\DBAL\Types\Type;
use ApiExtension\Populator\Guesser\AbstractGuesser;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;

/**
 * @author Mathieu Dewet <mathieu.dewet@gmail.com>
 */
class JsonGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return \in_array($mapping['type'], [JsonDocumentType::class, Type::JSON, Type::JSON_ARRAY, Type::OBJECT], true);
    }
    public function getValue(array $mapping): string
    {
        return \json_encode(array_fill(2, mt_rand(3, 10), $this->faker->word));
    }
}
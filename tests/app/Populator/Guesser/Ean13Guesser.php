<?php

declare(strict_types=1);

namespace ApiExtension\App\Populator\Guesser;

use Doctrine\DBAL\Types\Type;
use ApiExtension\App\Type\EanType;
use ApiExtension\Populator\Guesser\AbstractGuesser;

/**
 * @author Mathieu Dewet <mathieu.dewet@gmail.com>
 */
class Ean13Guesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return \in_array($mapping['type'], [EanType::EAN], true);
    }
    public function getValue(array $mapping): string
    {
        return $this->faker->ean13;
    }
}

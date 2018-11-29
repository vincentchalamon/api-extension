<?php

declare(strict_types=1);

namespace ApiExtension\App\Populator\Guesser;

use Doctrine\DBAL\Types\Type;
use ApiExtension\Populator\Guesser\AbstractGuesser;

/**
 * @author Mathieu Dewet <mathieu.dewet@gmail.com>
 */
class GuidGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return \in_array($mapping['type'], [Type::GUID], true);
    }
    public function getValue(array $mapping): string
    {
        return $this->faker->uuid;
    }
}

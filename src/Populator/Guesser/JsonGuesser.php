<?php

/*
 * This file is part of the API Extension project.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator\Guesser;

use Doctrine\DBAL\Types\Type;

/**
 * @author Mathieu Dewet <mathieu.dewet@gmail.com>
 */
class JsonGuesser extends AbstractGuesser implements GuesserAwareInterface
{
    use GuesserAwareTrait;

    public function supports(array $mapping): bool
    {
        $class_name = Type::class;
        $constant = "$class_name::JSON";
        $constant_value = \defined($constant) ? $constant : null;

        return \in_array($mapping['type'], (null !== $constant_value) ? [Type::JSON, Type::JSON_ARRAY] : [Type::JSON_ARRAY], true);
    }

    public function getValue(array $mapping): array
    {
        return $this->guesser->getValue(['type' => Type::TARRAY]);
    }
}

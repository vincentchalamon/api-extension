<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\SchemaGenerator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
trait SchemaGeneratorAwareTrait
{
    /**
     * @var SchemaGeneratorInterface
     */
    private $schemaGenerator;

    public function setSchemaGenerator(SchemaGeneratorInterface $schemaGenerator): void
    {
        $this->schemaGenerator = $schemaGenerator;
    }
}

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

namespace ApiExtension\Context;

use ApiExtension\ClassRepository\ClassRepositoryInterface;
use ApiExtension\ObjectManager\ObjectManagerInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class FixturesContext implements Context
{
    private $classRepository;
    private $objectManager;

    public function __construct(ClassRepositoryInterface $classRepository, ObjectManagerInterface $objectManager)
    {
        $this->classRepository = $classRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @Given /^the following (?P<name>[\w\-]+):$/
     */
    public function theFollowing(string $name, TableNode $table)
    {
        $rows = $table->getRows();
        $headers = \array_shift($rows);
        $reflectionClass = $this->classRepository->getReflectionClass($name);
        foreach ($rows as $row) {
            $this->objectManager->fake($reflectionClass, \array_combine($headers, $row));
        }
    }

    /**
     * @Given /^there are (?P<number>\d+) (?P<name>[\w\-]+) with:$/
     */
    public function thereAreWith(int $number, string $name, TableNode $table)
    {
        $rows = $table->getRows();
        for ($i = 0; $i < $number-1; ++$i) {
            $rows[] = $rows[\count($rows)-1];
        }
        $this->theFollowing($name, new TableNode($rows));
    }

    /**
     * @Given /^there (?:is|are) (?P<number>\d+) (?P<name>[\w\-]+)$/
     */
    public function thereIs(string $name, int $number)
    {
        $reflectionClass = $this->classRepository->getReflectionClass($name);
        for ($i = 0; $i < $number; ++$i) {
            $this->objectManager->fake($reflectionClass);
        }
    }

    /**
     * @Given /^there (?:is|are) (?:a|an) (?P<name>[\w\-]+)$/
     */
    public function thereIsA(string $name)
    {
        $this->thereIs($name, 1);
    }

    /**
     * @Given /^there are (?P<name>[\w\-]+)$/
     */
    public function thereAre(string $name)
    {
        $this->thereIs($name, mt_rand(3, 10));
    }
}

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

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Populator;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class FixturesContext implements Context
{
    private $populator;
    private $registry;
    private $helper;

    public function __construct(Populator $populator, ManagerRegistry $registry, ApiHelper $helper)
    {
        $this->populator = $populator;
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * @Given /^the following (?P<name>[\w\-]+):$/
     */
    public function theFollowing($name, TableNode $table)
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->name);
        $rows = $table->getRows();
        $headers = array_shift($rows);
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($reflectionClass->name)->getClassMetadata($reflectionClass->name);
        if (array_intersect($headers, $classMetadata->getIdentifierFieldNames())) {
            $idGenerator = $classMetadata->idGenerator;
            $classMetadata->setIdGenerator(new AssignedGenerator());
            $generatorType = $classMetadata->generatorType;
            $classMetadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        }
        foreach ($rows as $row) {
            $em->persist($this->populator->getObject($reflectionClass, array_combine($headers, $row)));
        }
        $em->flush();
        $em->clear();
        if (isset($idGenerator) && isset($generatorType)) {
            $classMetadata->setIdGenerator($idGenerator);
            $classMetadata->setIdGeneratorType($generatorType);
        }
    }

    /**
     * @Given /^there (?:is|are) (?P<number>\d+) (?P<name>[\w\-]+)$/
     */
    public function thereIs(string $name, int $number)
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->name);
        for ($i = 0; $i < $number; ++$i) {
            $em->persist($this->populator->getObject($reflectionClass));
        }
        $em->flush();
        $em->clear();
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

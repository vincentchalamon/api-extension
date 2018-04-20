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
    public function theFollowing($name, TableNode $table): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        $rows = $table->getRows();
        $headers = array_shift($rows);
        foreach ($rows as $row) {
            $em->persist($this->populator->getObject($reflectionClass, array_combine($headers, $row)));
        }
        $em->flush();
        $em->clear();
    }

    /**
     * @Given /^there (?:is|are) (?P<number>\d+) (?P<name>[\w\-]+)$/
     */
    public function thereIs(string $name, int $number): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        for ($i = 0; $i < $number; ++$i) {
            $em->persist($this->populator->getObject($reflectionClass));
        }
        $em->flush();
        $em->clear();
    }

    /**
     * @Given /^there (?:is|are) (?:a|an) (?P<name>[\w\-]+)$/
     */
    public function thereIsA(string $name): void
    {
        $this->thereIs($name, 1);
    }

    /**
     * @Given /^there are (?P<name>[\w\-]+)$/
     */
    public function thereAre(string $name): void
    {
        $this->thereIs($name, mt_rand(3, 10));
    }
}

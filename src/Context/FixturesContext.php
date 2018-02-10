<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Context;

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiExtension\Transformer\TransformerInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class FixturesContext implements Context
{
    private $helper;
    private $transformer;
    private $guesser;
    private $registry;

    public function __construct(ApiHelper $helper, TransformerInterface $transformer, GuesserInterface $guesser, ManagerRegistry $registry)
    {
        $this->helper = $helper;
        $this->transformer = $transformer;
        $this->guesser = $guesser;
        $this->registry = $registry;
    }

    /**
     * @Given /^the following (?P<name>[A-z ]+):$/
     */
    public function theFollowing($name, TableNode $table): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        $rows = $table->getRows();
        $headers = array_shift($rows);
        foreach ($rows as $row) {
            $em->persist($this->helper->createObject($reflectionClass, array_combine($headers, $row)));
        }
        $em->flush();
        $em->clear();
    }

    /**
     * @Given /^there (?:is|are) (?P<number>\d+) (?P<name>[A-z]+)$/
     */
    public function thereIs(string $name, int $number): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        for ($i = 0; $i < $number; ++$i) {
            $em->persist($this->helper->createObject($reflectionClass));
        }
        $em->flush();
        $em->clear();
    }

    /**
     * @Given /^there (?:is|are) (?:a|an) (?P<name>[A-z]+)$/
     */
    public function thereIsA(string $name): void
    {
        $this->thereIs($name, 1);
    }
}

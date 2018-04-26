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

use Behat\Behat\Context\Context as ContextInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FeatureContext implements ContextInterface
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @BeforeSuite
     */
    public static function setUpSuite()
    {
        require_once __DIR__.'/../../../../vendor/autoload.php';
    }

    /**
     * @BeforeScenario
     */
    public function initDatabase()
    {
        $manager = $this->doctrine->getManager();

        if (!is_file(__DIR__.'/../../cache/test/db.sqlite')) {
            $classes = $manager->getMetadataFactory()->getAllMetadata();
            $schema = new SchemaTool($manager);
            $schema->createSchema($classes);
        } else {
            $purger = new ORMPurger($manager);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
            $purger->purge();
        }
    }
}

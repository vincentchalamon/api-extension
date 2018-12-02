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
use Doctrine\DBAL\Types\Type;
use ApiExtension\App\Type\EanType;
/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FeatureContext implements ContextInterface
{
    private $doctrine;
    private $cacheDir;

    public function __construct(ManagerRegistry $doctrine, string $cacheDir)
    {
        $this->doctrine = $doctrine;
        $this->cacheDir = $cacheDir;
        if (!Type::hasType(EanType::EAN)) {
            Type::addType('ean', EanType::class);
        }
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

        if (!is_file(sprintf('%s/db.sqlite', $this->cacheDir))) {
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

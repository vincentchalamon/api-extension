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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FeatureContext implements ContextInterface
{
    /**
     * @BeforeSuite
     */
    public static function setUpSuite()
    {
        require_once __DIR__.'/../../autoload.php';
    }

    public function IDoSomething()
    {
        throw new \Behat\Behat\Tester\Exception\PendingException();
    }
}

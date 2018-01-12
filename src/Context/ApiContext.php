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

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext;
use ApiExtension\Exception\InvalidStatusCodeException;
use ApiExtension\Helper\UriHelper;
use ApiExtension\Populator\Populator;
use ApiExtension\SchemaGenerator\SchemaGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ApiContext implements Context
{
    public const FORMAT = 'application/ld+json';

    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @var MinkContext
     */
    private $minkContext;

    /**
     * @var JsonContext
     */
    private $jsonContext;
    private $schemaGenerator;
    private $helper;
    private $populator;

    public function __construct(SchemaGeneratorInterface $schemaGenerator, UriHelper $helper, Populator $populator)
    {
        $this->schemaGenerator = $schemaGenerator;
        $this->helper = $helper;
        $this->populator = $populator;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext(RestContext::class);
        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->jsonContext = $environment->getContext(JsonContext::class);
    }

    /**
     * @When /^I get a list of (?P<name>[A-z]+)$/
     */
    public function sendGetRequestToCollection(string $name): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->helper->getUri($this->helper->getReflectionClass($name)));
    }

    /**
     * @When /^I create (?:a|an) (?P<name>[A-z]+)(?: with:)?$/
     */
    public function sendPostRequestToCollection(string $name, TableNode $table = null): void
    {
        $values = [];
        if (null !== $table) {
            $rows = $table->getRows();
            $values = array_combine(array_shift($rows), $rows[0]);
        }
        $reflectionClass = $this->helper->getReflectionClass($name);
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestToWithBody(Request::METHOD_POST, $this->helper->getUri($reflectionClass), new PyStringNode([json_encode($values + $this->populator->getData($reflectionClass))], 0));
    }

    /**
     * @When /^I get (?:a|an|the same) (?P<name>[A-z]+)$/
     */
    public function sendGetRequestToItem(string $name): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->helper->getItemUri($this->helper->getReflectionClass($name)));
    }

    /**
     * @When /^I delete (?:a|an) (?P<name>[A-z]+)$/
     */
    public function sendDeleteRequestToItem(string $name): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_DELETE, $this->helper->getItemUri($this->helper->getReflectionClass($name)));
    }

    /**
     * @When /^I update (?:a|an) (?P<name>[A-z]+)(?: with:)?$/
     */
    public function sendPutRequestToItem(string $name, TableNode $table = null): void
    {
        $values = [];
        if (null !== $table) {
            $rows = $table->getRows();
            $values = array_combine(array_shift($rows), $rows[0]);
        }
        $reflectionClass = $this->helper->getReflectionClass($name);
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestToWithBody(Request::METHOD_PUT, $this->helper->getItemUri($this->helper->getReflectionClass($name)), new PyStringNode([json_encode($values + $this->populator->getData($reflectionClass, 'put'))], 0));
    }

    /**
     * @Then the request is invalid
     */
    public function responseStatusCodeShouldBe400(): void
    {
        $this->minkContext->assertResponseStatus(400);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([json_encode($this->schemaGenerator->generate(new \ReflectionClass(ConstraintViolationListInterface::class)))], 0));
    }

    /**
     * @Then /^the (?:.*) is not found$/
     */
    public function responseStatusCodeShouldBe404(): void
    {
        $this->minkContext->assertResponseStatus(404);
    }

    /**
     * @Then the method is not allowed
     */
    public function responseStatusCodeShouldBe405(): void
    {
        $this->minkContext->assertResponseStatus(405);
    }

    /**
     * @Then /^the (?P<name>[A-z]+) has been successfully deleted$/
     */
    public function itemShouldHaveBeSuccessfullyDeleted(string $name): void
    {
        $this->minkContext->assertResponseStatus(204);
        // todo Ensure object has been deleted from database
    }

    /**
     * @Then /^I see (?:a|an) (?P<name>[A-z]+)$/
     */
    public function validateItemJsonSchema(string $name): void
    {
        $statusCode = $this->minkContext->getSession()->getStatusCode();
        if (200 > $statusCode || 300 <= $statusCode) {
            throw new InvalidStatusCodeException('Invalid status code: expecting between 200 and 300, got '.$statusCode);
        }
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([json_encode($this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => false, 'root' => true]))], 0));
    }

    /**
     * @Then /^I see a list of (?P<name>[A-z]+)$/
     */
    public function validateCollectionJsonSchema(string $name): void
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([json_encode($this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => true, 'root' => true]))], 0));
    }
}

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

use ApiExtension\Exception\InvalidStatusCodeException;
use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Populator;
use ApiExtension\SchemaGenerator\SchemaGeneratorInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext;
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
    private $lastRequestJson;

    public function __construct(SchemaGeneratorInterface $schemaGenerator, ApiHelper $helper, Populator $populator)
    {
        $this->schemaGenerator = $schemaGenerator;
        $this->helper = $helper;
        $this->populator = $populator;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContextsAndEmptyLastRequest(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext(RestContext::class);
        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->jsonContext = $environment->getContext(JsonContext::class);
        $this->lastRequestJson = null;
    }

    /**
     * @When /^I get a list of (?P<name>[A-z\-\_]+)$/
     */
    public function sendGetRequestToCollection(string $name): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->helper->getUri($this->helper->getReflectionClass($name)));
    }

    /**
     * @When /^I create (?:a|an) (?P<name>[A-z\-\_]+)(?: with:)?$/
     */
    public function sendPostRequestToCollection(string $name, $data = null, bool $completeRequired = true): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $values = [];
        if (null !== $data) {
            $values = $data;
            if ($data instanceof TableNode) {
                $rows = $data->getRows();
                $values = array_combine(array_shift($rows), $rows[0]);
                foreach ($values as $property => $value) {
                    if ('boolean' === ($this->helper->getMapping($reflectionClass->getName(), $property)['type'] ?? null)) {
                        $values[$property] = 'true' === $value;
                    }
                }
            }
        }
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iAddHeaderEqualTo('Content-Type', self::FORMAT);
        $this->lastRequestJson = $values + ($completeRequired ? $this->populator->getData($reflectionClass) : []);
        $this->restContext->iSendARequestToWithBody(Request::METHOD_POST, $this->helper->getUri($reflectionClass), new PyStringNode([json_encode($this->lastRequestJson)], 0));
    }

    /**
     * @When /^I get (?:a|an) (?P<name>[A-z\-\_]+)$/
     */
    public function sendGetRequestToItem(string $name, ?array $ids = null): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->helper->getItemUri($this->helper->getReflectionClass($name), $ids));
    }

    /**
     * @When /^I delete (?:a|an) (?P<name>[A-z\-\_]+)$/
     */
    public function sendDeleteRequestToItem(string $name, ?array $ids = null): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo(Request::METHOD_DELETE, $this->helper->getItemUri($this->helper->getReflectionClass($name), $ids));
    }

    /**
     * @When /^I update (?:a|an) (?P<name>[A-z\-\_]+)$/
     */
    public function sendPutRequestToItem(string $name, $data = null, bool $completeRequired = true, ?array $ids = null): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $values = [];
        if (null !== $data) {
            $values = $data;
            if ($data instanceof TableNode) {
                $rows = $data->getRows();
                $values = array_combine(array_shift($rows), $rows[0]);
                foreach ($values as $property => $value) {
                    if ('boolean' === ($this->helper->getMapping($reflectionClass->getName(), $property)['type'] ?? null)) {
                        $values[$property] = 'true' === $value;
                    }
                }
            }
        }
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iAddHeaderEqualTo('Content-Type', self::FORMAT);
        $this->lastRequestJson = $values + ($completeRequired ? $this->populator->getData($reflectionClass) : []);
        $this->restContext->iSendARequestToWithBody(Request::METHOD_PUT, $this->helper->getItemUri($this->helper->getReflectionClass($name), $ids), new PyStringNode([json_encode($this->lastRequestJson)], 0));
    }

    /**
     * @When /^I update (?:a|an) (?P<name>[A-z\-\_]+) with:$/
     */
    public function sendPutRequestToItemWithData(string $name, $data = null, ?array $ids = null): void
    {
        $this->sendPutRequestToItem($name, $data, false, $ids);
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
     * @Then /^the (?P<name>[A-z\-\_]+) has been successfully deleted$/
     */
    public function itemShouldHaveBeSuccessfullyDeleted(string $name): void
    {
        $this->minkContext->assertResponseStatus(204);
        // todo Ensure object has been deleted from database
    }

    /**
     * @Then /^I see (?:a|an) (?P<name>[A-z\-\_]+)$/
     */
    public function validateItemJsonSchema(string $name, array $schema = null): void
    {
        $statusCode = $this->minkContext->getSession()->getStatusCode();
        if (200 > $statusCode || 300 <= $statusCode) {
            throw new InvalidStatusCodeException('Invalid status code: expecting between 200 and 300, got '.$statusCode);
        }
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([json_encode($schema ?: $this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => false, 'root' => true]))], 0));
    }

    /**
     * @Then /^I see a list of (?P<name>[A-z\-\_]+)$/
     */
    public function validateCollectionJsonSchema(string $name, array $schema = null): void
    {
        $statusCode = $this->minkContext->getSession()->getStatusCode();
        if (200 > $statusCode || 300 <= $statusCode) {
            throw new InvalidStatusCodeException('Invalid status code: expecting between 200 and 300, got '.$statusCode);
        }
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([json_encode($schema ?: $this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => true, 'root' => true]))], 0));
    }

    /**
     * @Then /^print (?P<name>[A-z\-\_]+) JSON schema$/
     */
    public function printCollectionJsonSchema(string $name): void
    {
        echo json_encode($this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => true, 'root' => true]), JSON_PRETTY_PRINT);
    }

    /**
     * @Then /^print (?P<name>[A-z\-\_]+) item JSON schema$/
     */
    public function printItemJsonSchema(string $name): void
    {
        echo json_encode($this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => false, 'root' => true]), JSON_PRETTY_PRINT);
    }

    /**
     * @Then /^print last JSON request$/
     */
    public function printJsonData(): void
    {
        echo json_encode($this->lastRequestJson, JSON_PRETTY_PRINT);
    }
}

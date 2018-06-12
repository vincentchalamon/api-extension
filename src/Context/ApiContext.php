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

use ApiExtension\Exception\EntityNotFoundException;
use ApiExtension\Exception\InvalidStatusCodeException;
use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Populator;
use ApiExtension\SchemaGenerator\SchemaGeneratorInterface;
use ApiExtension\Transformer\TransformerInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
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
    private $transformer;

    public function __construct(SchemaGeneratorInterface $schemaGenerator, ApiHelper $helper, Populator $populator, TransformerInterface $transformer)
    {
        $this->schemaGenerator = $schemaGenerator;
        $this->helper = $helper;
        $this->populator = $populator;
        $this->transformer = $transformer;
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
     * @When /^I get a list of (?P<name>[\w\-]+)$/
     */
    public function sendGetRequestToCollection(string $name): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo('GET', $this->helper->getUri($this->helper->getReflectionClass($name)));
    }

    /**
     * @When /^I get a list of (?P<name>[\w\-]+) filtered by (?P<filters>[\w\-=&]+)$/
     */
    public function sendGetRequestToCollectionWithFilters(string $name, string $filters = null): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo('GET', $this->helper->getUri($this->helper->getReflectionClass($name))."?$filters");
    }

    /**
     * @When /^I get (?:a|an) (?P<name>[\w\-]+)$/
     */
    public function sendGetRequestToItem(string $name, ?array $ids = null): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo('GET', $this->helper->getItemUri($this->helper->getReflectionClass($name), $ids));
    }

    /**
     * @When /^I get the (?P<name>[\w\-]+) (?P<value>[^ ]+)$/
     */
    public function sendGetRequestToDesignatedItem(string $name, string $value)
    {
        $this->sendGetRequestToItem($name, $this->helper->getObjectIdentifiers($this->findObject($name, $value)));
    }

    /**
     * @When /^I delete (?:a|an) (?P<name>[\w\-]+)$/
     */
    public function sendDeleteRequestToItem(string $name, ?array $ids = null): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iSendARequestTo('DELETE', $this->helper->getItemUri($this->helper->getReflectionClass($name), $ids));
    }

    /**
     * @When /^I delete the (?P<name>[\w\-]+) (?P<value>[^ ]+)$/
     */
    public function sendDeleteRequestToDesignatedItem(string $name, string $value): void
    {
        $this->sendDeleteRequestToItem($name, $this->helper->getObjectIdentifiers($this->findObject($name, $value)));
    }

    /**
     * @When /^I update (?:a|an) (?P<name>[\w\-]+)(?: with:)?$/
     */
    public function sendPutRequestToItem(string $name, $data = null, array $ids = null): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $values = [];
        if (null !== $data) {
            $values = $data;
            if ($data instanceof TableNode) {
                $rows = $data->getRows();
                $values = array_combine(array_shift($rows), $rows[0]);
            }
        }
        $this->lastRequestJson = $this->populator->getRequestData($reflectionClass, 'put', $values);
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iAddHeaderEqualTo('Content-Type', self::FORMAT);
        $this->restContext->iSendARequestToWithBody('PUT', $this->helper->getItemUri($reflectionClass, $ids), new PyStringNode([json_encode($this->lastRequestJson)], 0));
    }

    /**
     * @When /^I update the (?P<name>[\w\-]+) (?P<value>[^ ]+)(?: with:)?$/
     */
    public function sendPutRequestToDesignatedItem(string $name, string $value, $data = null): void
    {
        $this->sendPutRequestToItem($name, $data, $this->helper->getObjectIdentifiers($this->findObject($name, $value)));
    }

    /**
     * @When /^I create (?:a|an) (?P<name>[\w\-]+)(?: with:)?$/
     */
    public function sendPostRequestToCollection(string $name, $data = null): void
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $values = [];
        if (null !== $data) {
            $values = $data;
            if ($data instanceof TableNode) {
                $rows = $data->getRows();
                $values = array_combine(array_shift($rows), $rows[0]);
            }
        }
        $this->lastRequestJson = $this->populator->getRequestData($reflectionClass, 'post', $values);
        $this->restContext->iAddHeaderEqualTo('Accept', self::FORMAT);
        $this->restContext->iAddHeaderEqualTo('Content-Type', self::FORMAT);
        $this->restContext->iSendARequestToWithBody('POST', $this->helper->getUri($reflectionClass), new PyStringNode([json_encode($this->lastRequestJson)], 0));
    }

    /**
     * @When /^I get the API doc in (?P<format>[A-z]+)$/
     */
    public function iGetTheApiDocInFormat(string $format)
    {
        // todo Do not hard-code url
        $this->restContext->iSendARequestTo('GET', $this->helper->getUrl('api_doc', ['_format' => $format]));
    }

    /**
     * @Then /^I see the API doc in (?P<format>[A-z]+)$/
     */
    public function validateApiDocSchema(string $format)
    {
        $this->minkContext->assertResponseStatus(200);
        switch ($format) {
            case 'json':
                $this->jsonContext->theResponseShouldBeInJson();
                $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([<<<'JSON'
{
    "type": "object",
    "properties": {
        "swagger": {"pattern": "^2.0$"},
        "basePath": {"type": "string"},
        "info": {
            "type": "object",
            "properties": {
                "version": {"type": "string"}
            }
        },
        "paths": {
            "type": "object"
        }
    }
}
JSON
                ], 0));
                break;
            case 'jsonld':
                $this->jsonContext->theResponseShouldBeInJson();
                $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([sprintf(<<<'JSON'
{
    "type": "object",
    "properties": {
        "@context": {
            "type": "object"
        },
        "@id": {"pattern": "^%s$"},
        "@type": {"pattern": "^hydra:ApiDocumentation$"},
        "hydra:entrypoint": {"pattern": "^%s$"},
        "hydra:supportedClass": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "@type": {"pattern": "^hydra:Class$"},
                    "hydra:supportedProperty": {
                        "type": "array"
                    }
                },
                "required": ["@type", "hydra:supportedProperty"]
            }
        }
    },
    "required": ["@context", "@id", "@type", "hydra:entrypoint", "hydra:supportedClass"]
}
JSON
                    , $this->helper->getUrl('api_doc', ['_format' => 'jsonld']), $this->helper->getUrl('api_entrypoint')
                ),
                ], 0));
                break;
            case 'html':
                $this->jsonContext->theResponseShouldNotBeInJson();
                break;
        }
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
     * @Then /^the (?P<name>[\w\_]+) has been successfully deleted$/
     */
    public function itemShouldHaveBeSuccessfullyDeleted(string $name): void
    {
        $this->minkContext->assertResponseStatus(204);
        // todo Ensure object has been deleted from database
    }

    /**
     * @Then I am forbidden to access this resource
     */
    public function iShouldBeForbiddenToAccessThisResource()
    {
        $this->minkContext->assertResponseStatus(403);
    }

    /**
     * @Then I am unauthorized to access this resource
     */
    public function iShouldBeUnauthorizedToAccessThisResource()
    {
        $this->minkContext->assertResponseStatus(401);
    }

    /**
     * @Then /^I see (?:a|an) (?P<name>[\w\-]+)$/
     */
    public function validateItemJsonSchema(string $name, $schema = null): void
    {
        $statusCode = $this->minkContext->getSession()->getStatusCode();
        if (200 > $statusCode || 300 <= $statusCode) {
            throw new InvalidStatusCodeException('Invalid status code: expecting between 200 and 300, got '.$statusCode);
        }
        $this->jsonContext->theResponseShouldBeInJson();
        if (null === $schema) {
            $schema = $this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => false, 'root' => true]);
        }
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([is_array($schema) ? json_encode($schema) : $schema], 0));
    }

    /**
     * @Transform /^(\d+)$/
     */
    public function castStringToNumber(string $value): int
    {
        return (int) $value;
    }

    /**
     * @Then /^I see a list of (?P<name>[\w\-]+)$/
     * @Then /^I see a list of (?P<total>\d+)  (?P<name>[\w\-]+)$/
     */
    public function validateCollectionJsonSchema(string $name, int $total = null, $schema = null): void
    {
        $statusCode = $this->minkContext->getSession()->getStatusCode();
        if (200 > $statusCode || 300 <= $statusCode) {
            throw new InvalidStatusCodeException('Invalid status code: expecting between 200 and 300, got '.$statusCode);
        }
        $this->jsonContext->theResponseShouldBeInJson();
        if (null === $schema) {
            $schema = $this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => true, 'root' => true]);
        }
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([is_array($schema) ? json_encode($schema) : $schema], 0));
        if (null !== $total) {
            $this->jsonContext->theJsonNodeShouldHaveElements('hydra:member', $total);
        }
    }

    /**
     * @Then /^I don't see any (?P<name>[\w\-]+)$/
     */
    public function validateCollectionIsEmpty(string $name): void
    {
        $this->validateCollectionJsonSchema($name, 0);
    }

    /**
     * @Then /^print (?P<name>[\w\-]+) list JSON schema$/
     */
    public function printCollectionJsonSchema(string $name): void
    {
        echo json_encode($this->schemaGenerator->generate($this->helper->getReflectionClass($name), ['collection' => true, 'root' => true]), JSON_PRETTY_PRINT);
    }

    /**
     * @Then /^print (?P<name>[\w\-]+) item JSON schema$/
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

    private function findObject(string $name, $value)
    {
        $reflectionClass = $this->helper->getReflectionClass($name);
        $object = $this->transformer->toObject(['targetEntity' => $reflectionClass->getName(), 'type' => ClassMetadataInfo::ONE_TO_ONE], $value);
        if (null === $object) {
            throw new EntityNotFoundException(sprintf('Unable to find an existing object of class %s with any value equal to %s.', $reflectionClass->getName(), $value));
        }

        return $object;
    }
}

**This extension is still in development!**

[![Build Status](https://travis-ci.org/vincentchalamon/api-extension.svg?branch=master)](https://travis-ci.org/vincentchalamon/api-extension)

This Behat extension use following extensions, check their documentations for installation & usage:
* [Symfony2Extension](https://github.com/Behat/Symfony2Extension)
* [Behatch](https://github.com/Behatch/contexts)
* [MinkExtension](https://github.com/Behat/MinkExtension)

## Install

```bash
composer require --dev vincentchalamon/api-extension:dev-master
```

As all services in Symfony are private, you need to override them to inject them:
```yaml
# config/services_test.yaml

# Hack for Behat: allow to inject some private services
# Waiting for Behat/Symfony2Extension to support autowiring (https://goo.gl/z8BPpG)
services:
    test.property_info:
        parent: property_info
        public: true
    test.api_platform.metadata.resource.metadata_factory.annotation:
        parent: api_platform.metadata.resource.metadata_factory.annotation
        public: true
    test.api_platform.iri_converter:
        parent: api_platform.iri_converter
        public: true
```

Declare required extensions in your Behat configuration:
```yaml
# behat.yml.dist
default:
    # ...
    suites:
        default:
            contexts:
                - Behat\MinkExtension\Context\MinkContext
                - Behatch\Context\RestContext
                - Behatch\Context\JsonContext
                - ApiExtension\Context\ApiContext
                - ApiExtension\Context\FixturesContext
    extensions:
        Behat\Symfony2Extension:
            kernel:
                bootstrap: features/bootstrap/bootstrap.php
                class: App\Kernel
        Behat\MinkExtension:
            base_url: 'http://www.example.com/'
            sessions:
                default:
                    symfony2: ~
        Behatch\Extension: ~
        # ...
        ApiExtension:
            services:
                metadataFactory: '@test.api_platform.metadata.resource.metadata_factory.annotation'
                iriConverter: '@test.api_platform.iri_converter'
                registry: '@doctrine'
                propertyInfo: '@test.property_info'
```

## Usage

FixturesContext provides the following steps:
* `the following <name>`
* `there is <nb> <name>`
* `there are <nb> <name>`
* `there is a <name>`
* `there is an <name>`

ApiContext provides the following steps:
* `I get a list of <name>`
* `I create a <name>`
* `I create an <name>`
* `I create a <name> with:`
* `I create an <name> with:`
* `I get a <name>`
* `I get an <name>`
* `I delete a <name>`
* `I delete an <name>`
* `I update a <name>`
* `I update an <name>`
* `I update a <name> with:`
* `I update an <name> with:`
* `the request is invalid`
* `the <name> is not found`
* `the method is not allowed`
* `I see a <name>`
* `I see an <name>`
* `I see a list of <name>`
* `print <name> JSON schema`
* `print <name> item JSON schema`
* `print last JSON request`

Example:
```gherkin
Feature: I can get bananas

  Background:
    Given the following gorilla:
      | email               |
      | harambe@example.com |

  Scenario: I can get a banana
    When I get a banana
    Then I see a banana

  Scenario: I can create a banana
    When I create a banana
    Then I see a banana

  Scenario: I can update a banana
    Given there is a banana
    When I update a banana
    Then I see a banana

  Scenario: I can delete a banana
    Given there is a banana
    When I delete a banana
    Then the banana has been successfully deleted

  Scenario: I can get a list of bananas
    Given there are 3 bananas
    When I get a list of bananas
    Then I see a list of bananas
```

## Add faker provider

Update your Behat configuration:
```yaml
# behat.yml.dist
default:
    # ...
    extensions:
        # ...
        ApiExtension:
            # ...
            providers:
                - App\Faker\Provider\MiscellaneousProvider
```

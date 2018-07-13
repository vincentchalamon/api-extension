# ApiExtension

[![Build Status](https://travis-ci.org/vincentchalamon/api-extension.svg?branch=master)](https://travis-ci.org/vincentchalamon/api-extension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vincentchalamon/api-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vincentchalamon/api-extension/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/vincentchalamon/api-extension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/vincentchalamon/api-extension/?branch=master)

This Behat extension requires following extensions, check their documentations for installation & usage:
* [Symfony2Extension](https://github.com/Behat/Symfony2Extension)
* [Behatch](https://github.com/Behatch/contexts)
* [MinkExtension](https://github.com/Behat/MinkExtension)

**This extension is just a shortcut to write some steps, it doesn't replace your custom steps.** For instance, if you
want to test that updating a user updates its password, you still need to add a customer step like
`And the user password has been successfully updated`.

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
    test.annotation_reader:
        parent: annotation_reader
        public: true
    test.router:
        parent: router
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
                annotationReader: '@test.annotation_reader'
                router: '@test.router'
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
* `I get a list of <name> filtered by <filter>`
* `I create a <name>`
* `I create an <name>`
* `I create a <name> with:`
* `I create an <name> with:`
* `I get a <name>`
* `I get an <name>`
* `I get the <name> <value>`
* `I delete a <name>`
* `I delete an <name>`
* `I delete the <name> <value>`
* `I update a <name>`
* `I update an <name>`
* `I update the <name> <value>`
* `I update a <name> with:`
* `I update an <name> with:`
* `I update the <name> <value> with:`
* `the request is invalid`
* `the <name> is not found`
* `the method is not allowed`
* `I see a <name>`
* `I see an <name>`
* `I see a list of <name>`
* `I see a list of <nb> <name>`
* `I don't see any <name>`
* `print <name> list JSON schema`
* `print <name> item JSON schema`
* `print last JSON request`

Example:
```gherkin
Feature: Using API-Platform, I can get, create, update & delete beers.

  Scenario: I can get a list of beers
    Given there are beers
    When I get a list of beers
    Then I see a list of beers

  Scenario: I can get a list of beers filtered by name
    Given there are beers
    When I get a list of beers filtered by name=Chouffe
    Then I don't see any beer

  Scenario: I can create a beer
    When I create a beer
    Then I see a beer

  Scenario: I can create a beer
    When I create a beer with:
      | name    |
      | Chouffe |
    Then I see a beer

  Scenario: I can update a beer
    Given there is a beer
    When I update a beer
    Then I see a beer

  Scenario: I can update a beer and fill its new name
    Given there is a beer
    When I update a beer with:
      | name    |
      | Chouffe |
    Then I see a beer

  Scenario: I can update a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I update the beer Chouffe
    Then I see a beer

  Scenario: I can update a beer by its name and fill its new name
    Given the following beer:
      | name    |
      | Chouffe |
    When I update the beer Chouffe with:
      | name |
      | Kwak |
    Then I see a beer

  Scenario: I can get a beer
    Given there is a beer
    When I get a beer
    Then I see a beer

  Scenario: I can get a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I get the beer Chouffe
    Then I see a beer

  Scenario: I cannot get a non-existing beer
    When I get a beer
    Then the beer is not found

  Scenario: I can delete a beer
    Given there is a beer
    When I delete a beer
    Then the beer has been successfully deleted

  Scenario: I can delete a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I delete the beer Chouffe
    Then the beer has been successfully deleted
```

## Add faker provider

To use a [custom faker provider](https://github.com/fzaninotto/Faker#faker-internals-understanding-providers) from
[fzaninotto/Faker](https://github.com/fzaninotto/Faker), update your Behat configuration as following:

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

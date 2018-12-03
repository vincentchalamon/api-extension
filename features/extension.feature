Feature: I can execute Behat tests with current API Extension

  Scenario: I can execute Behat tests
    When I run "behat --tags=~@debug"
    Then it should pass with:
    """
    20 scenarios (20 passed)
    """

  Scenario: I can debug beer item schema
    When I run "behat features/beer.feature:87"
    Then the JSON output should be equal to:
    """
    {
        "type": "object",
        "properties": {
            "@id": {
                "type": "string",
                "pattern": "^\/beers\/[\\w-;=]+$"
            },
            "@type": {
                "type": "string",
                "pattern": "^Beer$"
            },
            "id": {
                "type": [
                    "integer"
                ]
            },
            "company": {
                "type": "object",
                "properties": {
                    "@id": {
                        "type": "string",
                        "pattern": "^\/companies\/[\\w-;=]+$"
                    },
                    "@type": {
                        "type": "string",
                        "pattern": "^Company$"
                    },
                    "name": {
                        "type": [
                            "string"
                        ]
                    }
                },
                "required": [
                    "@id",
                    "@type",
                    "name"
                ]
            },
            "ean13": {
                "type": [
                    "string"
                ]
            },
            "name": {
                "type": [
                    "string"
                ]
            },
            "type": {
                "type": [
                    "string",
                    "null"
                ]
            },
            "volume": {
                "type": [
                    "number",
                    "null"
                ]
            },
            "active": {
                "type": "boolean"
            },
            "price": {
                "type": [
                    "number"
                ]
            },
            "ingredients": {
                "type": [
                    "array",
                    "object"
                ]
            },
            "weight": {
                "type": [
                    "integer"
                ]
            },
            "description": {
                "type": [
                    "string"
                ]
            },
            "createdAt": {
                "type": [
                    "string"
                ],
                "pattern": "^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\+\\d{2}:\\d{2}$"
            },
            "currencyCode": {
                "type": [
                    "string"
                ]
            },
            "images": {
                "type": [
                    "array",
                     "null"
                ],
                "items": {
                    "type": "object",
                    "properties": {
                        "@id": {
                            "type": "string",
                            "pattern": "^\/images\/[\\w-;=]+$"
                        },
                        "@type": {
                            "type": "string",
                            "pattern": "^Image$"
                        },
                        "name": {
                            "type": [
                                "string"
                            ]
                        }
                    },
                    "required": [
                        "@id",
                        "@type",
                        "name"
                    ]
                }
            },
            "nbImages": {
                "type": [
                    "number"
                ]
            },
            "@context": {
                "type": "string",
                "pattern": "^\/contexts\/Beer$"
            }
        },
        "required": [
            "@id",
            "@type",
            "id",
            "company",
            "name",
            "active",
            "price",
            "ingredients",
            "weight",
            "description",
            "createdAt",
            "currencyCode",
            "nbImages",
            "@context"
        ]
    }
    """

  Scenario: I can debug beer list schema
    When I run "behat features/beer.feature:91"
    Then the JSON output should be equal to:
    """
    {
        "type": "object",
        "properties": {
            "@context": {
                "type": "string",
                "pattern": "^\/contexts\/Beer$"
            },
            "@id": {
                "type": "string",
                "pattern": "^\/beers$"
            },
            "@type": {
                "type": "string",
                "pattern": "^hydra:Collection$"
            },
            "hydra:member": {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "@id": {
                            "type": "string",
                            "pattern": "^\/beers\/[\\w-;=]+$"
                        },
                        "@type": {
                            "type": "string",
                            "pattern": "^Beer$"
                        },
                        "id": {
                            "type": [
                                "integer"
                            ]
                        },
                        "name": {
                            "type": [
                                "string"
                            ]
                        }
                    },
                    "required": [
                        "@id",
                        "@type",
                        "id",
                        "name"
                    ]
                }
            }
        },
        "required": [
            "@context",
            "@id",
            "@type",
            "hydra:member"
        ]
    }
    """

  Scenario: I can debug beer last request
    When I run "behat features/beer.feature:95"
    Then the JSON output should be equal to this schema:
    """
    {
        "type": "object",
        "properties": {
            "company": {
                "type": "string",
                "pattern": "^\/companies\/\\w+"
            },
            "name": {
                "type": "string"
            },
            "active": {
                "type": "boolean"
            },
            "price": {
                "type": "number"
            },
            "ingredients": {
                "type": "object"
            },
            "weight": {
                "type": "integer"
            },
            "description": {
                "type": "string"
            },
            "createdAt": {
                "type": "string",
                "pattern": "^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\+\\d{2}:\\d{2}$"
            },
            "currencyCode": {
                "type": "string"
            },
            "images": {
                "type": "array",
                "items": {
                    "type": "string",
                    "pattern": "^\/images\/\\w+$"
                }
            },
            "misc": {
                "type": "string"
            }
        },
        "required": ["company", "name", "active", "price", "ingredients", "description", "createdAt", "currencyCode", "misc"]
    }
    """

  Scenario: I can debug company item schema
    When I run "behat features/company.feature:18"
    Then the JSON output should be equal to:
    """
    {
        "type": "object",
        "properties": {
            "@id": {
                "type": "string",
                "pattern": "^\/companies\/[\\w-;=]+$"
            },
            "@type": {
                "type": "string",
                "pattern": "^Company$"
            },
            "name": {
                "type": [
                    "string"
                ]
            },
            "@context": {
                "type": "string",
                "pattern": "^\/contexts\/Company$"
            }
        },
        "required": [
            "@id",
            "@type",
            "name",
            "@context"
        ]
    }
    """

  Scenario: I can debug company list schema
    When I run "behat features/company.feature:22"
    Then the JSON output should be equal to:
    """
    {
        "type": "object",
        "properties": {
            "@context": {
                "type": "string",
                "pattern": "^\/contexts\/Company$"
            },
            "@id": {
                "type": "string",
                "pattern": "^\/companies$"
            },
            "@type": {
                "type": "string",
                "pattern": "^hydra:Collection$"
            },
            "hydra:member": {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "@id": {
                            "type": "string",
                            "pattern": "^\/companies\/[\\w-;=]+$"
                        },
                        "@type": {
                            "type": "string",
                            "pattern": "^Company$"
                        },
                        "name": {
                            "type": [
                                "string"
                            ]
                        }
                    },
                    "required": [
                        "@id",
                        "@type",
                        "name"
                    ]
                }
            },
            "hydra:totalItems": {
                "type": "integer"
            }
        },
        "required": [
            "@context",
            "@id",
            "@type",
            "hydra:member",
            "hydra:totalItems"
        ]
    }
    """

  Scenario: I can debug company last request
    When I run "behat features/company.feature:26"
    Then the output should contain:
    """
    "No route found for "POST /companies": Method Not Allowed (Allow: GET)"
    """

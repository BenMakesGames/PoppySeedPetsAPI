* Controllers should be split into features, with a folder per feature.
* Each controller class should contain a single endpoint
* Response and request DTOs should live in the same file as their controller
* Do not fall for the trap of "false sameness": 99%+ of request and response DTOs should NOT be reused between endpoints

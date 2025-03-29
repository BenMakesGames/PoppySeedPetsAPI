### One endpoint per controller

Every controller class should contain only one endpoint.

That same file should contain the endpoint's request and response DTOs.

Request and response DTOs should not be shared between endpoints, except for data which is truly common (pet & player data, for example).

> 🧚‍♀️** Hey, listen!** Poppy Seed Pets was started before Symfony supported request DTOs with `#[MapRequestPayload]` and `#[MapQueryString]`. If you have the will and opportunity, please migrate old code to start using request DTOs!

### Don't go overboard with REST; most POST URLs _should_ read like actions to be taken

Examples of actions:
* `POST /florist/tradeForGiftPackage`
* `POST /fireplace/feedWhelp`
* `POST /pet/{petId}/feed`
* `PATCH /letter/{letterId}/read`

If you only ever use GET, POST, and maybe DELETE, that's fine - in most cases there's not much benefit to getting technical and using PATCH or PUT. (Like that PATCH endpoint for reading a letter is kind of silly; may as well be a POST.)

> 🧚‍♀️** Hey, listen!** It is still super-true that GET requests must not modify data (except for side-effects like logging or tracking the time a player was last active).

### Controller endpoints MAY contain plenty of logic

1. Start by putting all logic into a controller's endpoint.
2. Pull logic out of controller endpoints _when/if_ it needs to be shared between two endpoints.

### Don't use Doctrine repository classes

Throwing all your queries into repository classes results in huge repository classes full of unrelated code.

Very few DB queries are actually shared between endpoints.

_When/if_ a query needs to be shared, create a service class or static class.

### Don't use Symfony's serialization groups

They scatter related code across multiple files, making changes harder to make and more error-prone.

Use explicit mapping to response DTOs, instead.

> 🧚‍♀️** Hey, listen!** Poppy Seed Pets started off using serialization groups, so you'll see them a lot. If you have the will and opportunity, please migrate old code to use explicit mapping instead!

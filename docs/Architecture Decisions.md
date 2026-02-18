### One endpoint per controller

Every controller class should contain only one endpoint.

That same file should contain the endpoint's request and response DTOs.

Request and response DTOs should not be shared between endpoints, except for data which is truly common (pet & player data, for example).

> 🧚‍♀️ **Hey, listen!** Poppy Seed Pets was started before Symfony supported request DTOs with `#[MapRequestPayload]` and `#[MapQueryString]`. If you have the will and opportunity, please migrate old code to start using request DTOs!

> **💻 Note for experienced web devs:** Separate by game feature rather than technical concern (vertical slices), and tune your mental dial a touch in the CQRS direction. Just a touch. Symfony _really_ wants you to separate by technical concern and just throw tons of endpoints together, but we strive to fight those defaults where it doesn't create too much friction. (For example, it'd be rad to have service classes near the controllers that use them, but Symfony makes that onerous.)

### Most POST URLs should read like actions to be taken

Examples of actions:

* `POST /florist/tradeForGiftPackage`
* `POST /fireplace/feedWhelp`
* `POST /pet/{petId}/feed`
* `PATCH /letter/{letterId}/read`

If you only ever use GET, POST, and maybe DELETE, that's fine - in most cases there's not much benefit to getting technical and using PATCH or PUT. (Like that PATCH endpoint for reading a letter is kind of silly; may as well be a POST.)

> 🧚‍♀️ **Hey, listen!** It is still super-true that GET requests must not modify data (except for side-effects like logging or tracking the time a player was last active).

> **💻 Note for experienced web devs:** CRUD has its place, but PSP, like many complex web apps, has _business rules_ that need to be followed. Making PATCH endpoints that try to handle every operation is a path that leads to madness. When in doubt, go RPC-style; when & if you _know_ CRUD-style is correct, then go CRUD-style.

### Controller endpoints MAY contain plenty of logic

1. Start by putting all logic into a controller's endpoint.
2. Pull logic out of controller endpoints _when/if_ it needs to be shared between two endpoints.

> **💻 Note for experienced web devs:** YAGNI. KISS. The web API is _the_ API. We don't need to separate business logic from the web for imagined future use-cases.

### Don't use Doctrine repository classes

Throwing all your queries into repository classes results in huge repository classes full of unrelated code. Again: PSP separates by game feature, not technical concern.

Very few DB queries are actually shared between endpoints.

_When/if_ a query needs to be shared, create a service class or static class.

### Don't use Symfony's serialization groups

They scatter related code across multiple files, making changes harder to make and more error-prone.

Use explicit mapping to response DTOs, instead.

> 🧚‍♀️ **Hey, listen!** Poppy Seed Pets started off using serialization groups, so you'll see them a lot. If you have the will and opportunity, please migrate old code to use explicit mapping instead!

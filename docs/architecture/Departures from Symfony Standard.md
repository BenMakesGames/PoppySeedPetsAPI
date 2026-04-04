## Departures from Symfony Standard

### Vertical Slices Over Technical Layers

Organize by game feature (Fishing, Cooking, Beehive), not by technical concern (controllers, services, repositories). Think slightly CQRS-ish.

### Don't use Symfony's serialization groups

They scatter related code across multiple files, making changes harder to make and more error-prone.

Use explicit mapping to response DTOs, instead.

> 🧚‍♀️ **Hey, listen!** Poppy Seed Pets started off using serialization groups, so you'll see them a lot. If you have the will and opportunity, please migrate old code to use explicit mapping instead!

### One endpoint per controller

Every controller class should contain only one endpoint.

That same file should contain the endpoint's request and response DTOs.

Request and response DTOs should not be shared between endpoints, except for data which is truly common (pet & player data, for example).

> 🧚‍♀️ **Hey, listen!** Poppy Seed Pets was started before Symfony supported request DTOs with `#[MapRequestPayload]` and `#[MapQueryString]`. If you have the will and opportunity, please migrate old code to start using request DTOs!

> **💻 Note for experienced web devs:** Separate by game feature rather than technical concern (vertical slices), and tune your mental dial a touch in the CQRS direction. Just a touch. Symfony _really_ wants you to separate by technical concern and just throw tons of endpoints together, but we strive to fight those defaults where it doesn't create too much friction. (For example, it'd be rad to have service classes near the controllers that use them, but Symfony makes that onerous.)

### Don't use Doctrine repository classes

Throwing all your queries into repository classes results in huge repository classes full of unrelated code. Again: PSP separates by game feature, not technical concern.

Very few DB queries are actually shared between endpoints.

_When/if_ a query needs to be shared, create a service class or static class.


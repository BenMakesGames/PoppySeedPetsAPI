## Dev happiness & project longevity TODOs

### Problem: PHP

PHP:
* is loosely-typed
* will probably never have generics
* has a bad API, largely due to poor OO

Potential alternatives:
* C#
* I'm open to other strongly-typed, compiled, OO languages with strong web frameworks and ORMs

An iterative approach to moving away from PHP is required; this is not something that should be done all at once.

### Problem: fake enums

Poppy Seed Pets was created before PHP introduced enums, so it has fake enums. We should replace all those.

Careful-careful about any enums whose values are stored in the database. This problem can't be solved with a quick search-and-replace; each enum must be considered individually.

### Problem: "serialization groups"

Symfony has this thing called "serialization groups" which seem cool at first, but are worse the bigger your app gets.

All serialization groups should be removed and replaced with explicit response mapping.

### Problem: big API responses

All API responses include full user & weather data. This should be broken up for a couple reasons:

1. overall performance
2. to facilitate moving off of PHP (easier to write a new API if the responses have to do less)

I'd like to change how weather is done, anyway, to make it just be "this is the weather today", which would make solving this problem easier.

### Problem: Symfony added support for request DTOs a little bit ago, so PSP is still hardly using them

Search for `#[MapRequestPayload]`.

PSP should be doing more of that.

More info: https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects

### Problem: very few automated tests to speak of

Especially if lots of people become interested in contributing, automated tests will be increasingly important.

* 100% code coverage is a harmful goal
* it's okay to make an automated test for the purposes of a refactor and then throw it away afterward
* lasting automated tests must not make refactoring more difficult (do not test implementation details, for example)

### Problem: the database is sad when players run hours, which in turn makes players sad (slow server)

Potential solutions:
* MOAR CACHING
* less Doctrine (Doctrine insists on `SELECT *`ing everything - it's fucking awful, and they need to fix it)
* instead of the game automatically running time, have pets accumulate action points and have players send pets to do specific things 1 action point at a time
* ???

As this is a performance issue, solutions must be measured and compared. Use the `PerformanceProfiler` service to take stats, which are sent to AWS CloudWatch. 

### Problem: the PetActivityService has, like, a bajillion dependencies

That's _kind_ of to be expected, because of what a big deal pets are in Poppy Seed Pets, but it's a _little_ bananas.

If PSP moves away from "pets do whatever they want every hour, and instead players send them to do things," that would solve this problem, but there are surely other ways to solve it, too.

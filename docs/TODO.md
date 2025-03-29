## Dev happiness & project longevity TODOs

### Problem: PHP

PHP:
* is loosely-typed
* will probably never have generics
* has a bad API, largely due to poor OO

Potential alternatives:
* C#
  * Via https://www.peachpie.io/, maybe? 
* I'm open to other strongly-typed, compiled, OO languages with strong web frameworks and ORMs

An iterative approach to moving away from PHP is required; this is not something that can be done all at once.

### Problem: related code is scattered all over the file system

Symfony really likes to encourage you to scattered related code in unrelated places. These should be addressed:

#### Serialization groups (& serializers)

Symfony has a feature called "serialization groups" which feel cool and convenient at first, but get worse the bigger your app gets.  Using them scatters and mixes entity response mapping all over the entity classes.

All serialization groups should be removed and replaced with explicit response mapping to endpoint-specific response DTOs.

#### Repository classes

Repositories encourage you to put all your queries in one place, resulting in large classes containing unrelated code.

Poppy Seed Pets has almost entirely gotten away from using Doctrine repository classes! 🎉

But a few remain.

Either:
1. Bring single-use queries into the actual service that uses them
   * DRY is great, but don't overdo it - the exact same query in TWO WHOLE PLACES is probably fine, too
2. Create a new class for actually-shared queries (service, or static class)

#### Services are not co-located with their controllers

Default Symfony configuration says "all services go in `Services/`; all controllers go in `Controllers/`".

Like how repositories encourage throwing all your unrelated queries together, Symfony also encourages you to throw all your unrelated services and controllers together.

A more "vertical slice" approach would be better.

Instead of the Symfony default of:

* `Controllers/`
  * `PetController1.php`
  * `PetController2.php`
  * `PlayerController1.php`
  * `PlayerController2.php`
* `Services/`
  * `PetService.php`
  * `PlayerService.php`

We would like to organize code like:
 
* `Pet/`
  * `PetController1.php`
  * `PetController2.php`
  * `PetService.php`
* `Player/`
  * `PlayerController1.php`
  * `PlayerController2.php`
  * `PlayerService.php`

Possible solutions:
 
* Use Symfony bundles
* Maybe there's some slick `config.yaml` or attribute stuff?
* Don't use Symfony at all (related to move off PHP)

### Problem: fake enums

Poppy Seed Pets was created before PHP introduced enums, so it has fake enums. We should replace all those.

Because some enum values are stored in the DB, this problem can't be solved with a quick search-and-replace; each enum must be considered individually.

### Problem: big API responses

All API responses include full user & weather data. This should be broken up for a couple of reasons:

1. overall performance
2. to facilitate moving off of PHP (easier to write a new API if the responses have to do less)

I'd like to change how weather is done, anyway, to make it just be "this is the weather today", which would make solving this problem easier.

### Problem: Symfony added support for request DTOs a little bit ago, so PSP is still hardly using them

Search for `#[MapRequestPayload]`.

PSP should be doing more of that.

Request and response DTOs should live in the same file as the controller that uses them.

99+% of request and response DTOs should not be shared between controller endpoints.

More info: https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects

### Problem: too few automated tests, probably

Especially if lots of people become interested in contributing, automated tests will be increasingly important.

* 100% code coverage is a harmful goal
* it's okay to make an automated test for the purposes of a refactor and then throw it away afterward
* lasting automated tests must not make refactoring more difficult (do not test implementation details, for example)
* all automated tests must have a JUSTIFICATION for existence, explaining the dev pain point they solve - see existing tests for examples

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

### Problem: not containerized

You might see some docker-related stuff lying around. None of it is done or fully works.

If you could finish that up, that'd be rad.

Of course, they're less useful without an automated build & deploy pipeline.

### Problem: no automated build & deploy pipeline

Deploys are currently done like this:
1. log into the one and only web server that hosts Poppy Seed Pets
2. put the server into maintenance mode (by editing the `.env.local` file)
3. run `bin/deploy`
   * this performs a `git pull`, `composer install`, runs DB migrations, clears caches, and some other stuff
4. take the server out of maintenance mode

Of course, an actual deployment pipeline would be rad.

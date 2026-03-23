* Use `php bin/console make:entity` to create and modify entity classes
  * This tool handles creating boilerplate getters and setters 
* `make:entity` does have some bad assumptions we should fix:
  * Ensure that properties are strongly typed
  * Ensure that properties, as well as their getters and setters, are nullable only if the column is nullable (`make:entity` likes to make stuff nullable "for your convenience"; this is not convenient: inaccurate typing leads to bugs)
  * When creating a new Entity, delete the repository & repository configuration that `make:entity` creates (the repository pattern is anti-helpful when using modern ORMs like Doctrine)
* Use constructor arguments to set required properties; whenever possible, it should not be possible to create an entity without all required properties being set

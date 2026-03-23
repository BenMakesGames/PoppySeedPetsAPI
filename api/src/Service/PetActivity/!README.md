Classes in this directory are configured in Symfony to be lazily-instantiated. (See `config/services.yaml`.)

This is important because alllllll of these classes are dependencies of the `PetActivityService`, which is used for handling & responding to pet "decision making".

By using lazy instantiation, only the classes that are actually used by a pet when it takes action get instantiated.

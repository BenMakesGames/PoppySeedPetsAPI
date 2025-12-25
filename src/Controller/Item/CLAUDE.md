# Item Controllers

This directory contains controllers for usable items in Poppy Seed Pets. Each controller handles the special actions that can be performed with specific items when players click "Use" in their inventory.

## Architecture

### One Controller Per Item (or Item Family)

Unlike most of the codebase which follows "one endpoint per controller", Item controllers typically have **multiple endpoints for different actions on the same item**. This is a pragmatic exception because items often have 2-4 related actions.

**Example structure:**
```php
// src/Controller/Item/BeeLarvaController.php
#[Route("/item/beeLarva")]
class BeeLarvaController {
    #[Route("/{inventory}/hatch", methods: ["POST"])]
    public function hatch(...): JsonResponse { }

    #[Route("/{inventory}/returnToBeehive", methods: ["POST"])]
    public function returnToBeehive(...): JsonResponse { }

    #[Route("/{inventory}/giveToAntQueen", methods: ["POST"])]
    public function giveToAntQueen(...): JsonResponse { }
}
```

### How Items Connect to Controllers

1. **Database**: Items have a `useActions` JSON field in the `Item` entity (line 48)
2. **Structure**: Each use action is a 2-3 element array: `[label, action, type?]`
   - **Element 1**: Label text for the button (e.g., "Hatch!", "Paint...")
   - **Element 2**: Action identifier or URL
   - **Element 3** (optional): Action type - `"page"`, `"story"`, or `"item"` (default)
3. **Validation**: `Item::hasUseAction(string $action)` checks if an item supports a specific action
4. **Frontend**: Displays buttons for each action when viewing the item in inventory

**Action Types:**
- **`"item"`** (default if omitted): Element 2 is a URL to POST to (most common)
- **`"page"`**: Element 2 is a frontend UI identifier (e.g., opens a special interface)
- **`"story"`**: Element 2 is a backend URL that returns data for a visual novel engine

**Example useActions data:**
```json
// Bee Larva (standard item actions)
[
    ["Return to Beehive", "beeLarva/#/returnToBeehive"],
    ["Give to Ant Queen", "beeLarva/#/giveToAntQueen"],
    ["Hatch!", "beeLarva/#/hatch"]
]

// Lunchbox Paint (opens a special frontend page)
[
    ["Paint...", "lunchboxPaint", "page"]
]

// Astral Tuning Fork (plays a visual novel story - see TuningForkController)
[
    ["Listen", "tuningFork/#/listen", "story"]
]
```

## Directory Structure

- **Root** - Miscellaneous usable items (dice, tools, special items, etc.)
- **`Book/`** - Books that teach recipes or grant knowledge
- **`Blueprint/`** - Blueprints for building features (Greenhouse, Beehive, etc.)
- **`Note/`** - Notes and recipes players can read or learn
- **`PetAlteration/`** - Items that modify pets (color changes, skill scrolls, etc.)
- **`Pinata/`** - Items that can be opened/broken to reveal contents
- **`Scroll/`** - Scrolls with various magical effects

## Common Patterns

### 1. Validation with ItemControllerHelpers

**Every endpoint should start with validation:**
```php
ItemControllerHelpers::validateInventory($user, $inventory, 'itemName/#/action');
```

This validates:
- User owns the inventory item
- Item has the specified use action
- Item is in House, Basement, or Fireplace Mantle

**For items that can be used in Library too:**
```php
ItemControllerHelpers::validateInventoryAllowingLibrary($user, $inventory, 'itemName/#/action');
```

### 2. Inventory Route Parameter

All endpoints use Symfony's parameter binding to automatically load the `Inventory` entity:
```php
public function use(
    Inventory $inventory,  // Auto-loaded by Symfony from route parameter
    ResponseService $responseService,
    EntityManagerInterface $em,
    UserAccessor $userAccessor
): JsonResponse
```

### 3. Returning Success via ResponseService

Use `ResponseService::itemActionSuccess()` for simple item use responses:
```php
return $responseService->itemActionSuccess('You used the item!');

// When item is consumed/deleted:
return $responseService->itemActionSuccess('Message here', ['itemDeleted' => true]);
```

This is a convenience wrapper around `ResponseService::success()` that's optimized for item actions.

### 4. Consuming Items

When an item is consumed during use:
```php
$em->remove($inventory);
$em->flush();

return $responseService->itemActionSuccess($message, ['itemDeleted' => true]);
```

The `itemDeleted` flag tells the frontend to remove the item from the UI.

### 5. Transforming Items

Some items transform into other items when used:
```php
$inventory->changeItem(ItemRepository::findOneByName($em, 'New Item Name'));
$em->flush();

// Set reload flags based on where the item is
$responseService->setReloadPets($inventory->getHolder() !== null);
$responseService->setReloadInventory($inventory->getHolder() === null);

return $responseService->itemActionSuccess(null, ['itemDeleted' => true]);
```

**Why both reload flags?**
- If `$inventory->getHolder()` is a Pet, the item is equipped → reload pets
- If `$inventory->getHolder()` is null, the item is in house inventory → reload inventory

### 6. Space Validation

Before using "pinata" items (items that produce multiple items), validate there's space:
```php
ItemControllerHelpers::validateLocationSpace($inventory, $em);
```

This prevents pinata items from being used when the location is at its soft/hard cap for multi-item generation.

### 7. Creating Activity Logs for Pet Interactions

When an item action involves a pet (like hatching an egg or using a Horn of Plenty):
```php
$changes = new PetChanges($helperPet);  // Snapshot current state

$helperPet
    ->increaseSafety(4)
    ->increaseLove(4)
    ->increaseEsteem(4);

PetActivityLogFactory::createReadLog(
    $em,
    $helperPet,
    ActivityHelpers::PetName($helperPet) . ' found loot in a Horn of Plenty.'
)
    ->setChanges($changes->compare($helperPet))
    ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse);
```

**Note**: Use `createReadLog()` not `createUnreadLog()` when the item action already returns the same information to the player in the response message. This prevents the information from being shown twice (once in the response, once as a flash message), while still permanently logging it to the Journal for later review.

## Item Controller Helpers

`ItemControllerHelpers` is a static utility class providing common validation methods.

### validateInventory()
Validates ownership, use action, and location (House/Basement/Mantle only).

### validateInventoryAllowingLibrary()
Same as above but also allows Library location.

### validateLocationSpace()
Checks if the item's current location has capacity for multi-item generation (e.g., "pinata" items).

**Location Limits:**
- **House**: Soft cap at 150 items - pinata items blocked when 150+ items present
- **Basement**: Hard limit at 10,000 items - pinata items blocked when at capacity
- **Fireplace Mantle**: Capacity limit of 12 or 24 items, depending on Museum donations

**Note**: The house also has a 100-item soft cap where pets stop autonomous gathering, but this doesn't affect item controller actions.

## Common Services Used

### InventoryService
- `receiveItem()` - Give player/user a new item with minimal additional logic
- `petCollectsItem()` - Pet collects an item (accepts activity log parameter; may eat the item if hungry and edible, trigger equipment effects, apply status effect transformations, etc. - appending all results to the activity log)

### ResponseService
- `itemActionSuccess()` - Standard success response for item actions
- `setReloadInventory()` - Tell frontend to refresh inventory
- `setReloadPets()` - Tell frontend to refresh pet list

### IRandom
- `rngNextInt()` - Random integer in range
- `rngNextFromArray()` - Pick random element from array

## Special Considerations

### Items That Create Pets

Many items (eggs, larvae, boxes) can hatch/spawn pets. Standard pattern:

1. Check if item is in House (can't hatch elsewhere)
2. Create pet with `PetFactory::createPet()`
3. Set initial stats (love, safety, esteem, food, scale)
4. Check if house is full using `PetRepository::getNumberAtHome()`
5. If full, send pet to Daycare: `$newPet->setLocation(PetLocationEnum::DAYCARE)`
6. If not full, call `$responseService->setReloadPets()` to refresh UI
7. Remove the item from inventory

### Items That Require Other Items

Use `InventoryService::loseItem()` to consume required items:
```php
$royalJellyId = ItemRepository::getIdByName($em, 'Royal Jelly');

if($inventoryService->loseItem($user, $royalJellyId, $inventory->getLocation()) < 1) {
    return $responseService->itemActionSuccess('You need Royal Jelly to do this!');
}
```

### Items That Give Random Loot

Common pattern for "loot box" style items:
```php
$loot = $rng->rngNextSubsetFromArray([
    'Item Name', 'Item Name',
    'Other Item',
    // etc...
], 5 /* select 5 items from the above list, in this example */);

foreach($loot as $itemName) {
    $inventoryService->receiveItem(
        $itemName,
        $user,
        $user->getName() . ' found this in a Treasure Chest.',
        $inventory->getLocation(), // set the new item's location to that of the pinata item's
        $inventory->getLockedToOwner() // propagate "locked to owner" status from pinata item
    );
}
```

### Items That Transform

Items can change into other items using `Inventory::changeItem()`:
```php
// Metal Detector can be tuned to different metals
$inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Gold)'));
$em->flush();
```

## Creating New Item Controllers

1. **Update database**: Add `useActions` to the item in a migration
2. **Create controller class** in appropriate subdirectory
3. **Add route prefix**: `#[Route("/item/itemName")]`
4. **Implement endpoints** for each use action
5. **Start with validation**:
   * `ItemControllerHelpers::validateInventory()` for all items
   * `ItemControllerHelpers::validateLocationSpace()` for items that create more items
6. **Use ResponseService**: `return $responseService->itemActionSuccess()`
7. **Set reload flags** if inventory or pets change

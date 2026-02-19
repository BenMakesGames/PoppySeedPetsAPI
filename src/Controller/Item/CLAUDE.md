# Item Controllers

## Always return `itemActionSuccess` from item action endpoints

The frontend's item dialog expects all item action responses in the `itemActionSuccess` format. Even when the action can't proceed (e.g., "you already have one"), return `itemActionSuccess` with a friendly message — don't throw exceptions like `PSPInvalidOperationException`. Exceptions break the item dialog flow.

Only include `'itemDeleted' => true` when the item was actually consumed.

```php
// Item consumed — include itemDeleted flag
return $responseService->itemActionSuccess('You installed the thing!', ['itemDeleted' => true]);

// Action blocked — just a message, no flag
return $responseService->itemActionSuccess('You already have one of those!');
```

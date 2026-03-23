export enum InventoryModeEnum
{
  Browsing,
  Feeding,
  Cooking,
  Trashing,
  Moving,
  Selling
}

export function isSelectionMode(mode: InventoryModeEnum)
{
  return mode === InventoryModeEnum.Selling ||
    mode === InventoryModeEnum.Feeding ||
    mode === InventoryModeEnum.Moving ||
    mode === InventoryModeEnum.Cooking ||
    mode === InventoryModeEnum.Trashing
  ;
}
export interface MyAccountSerializationGroup
{
  id: number|null;
  email: string;
  name: string;
  icon: string|null;
  lastAllowanceCollected: string;
  moneys: number;
  recyclePoints: number;
  maxPets: number;
  maxSellPrice: number;
  maxMarketBids: number;
  roles: string[];
  unreadNews: number;
  unreadLetters: number;
  canAssignHelpers: boolean;
  lastPerformedQualityTime: string;
  isVaultOpen: boolean;
  vaultOpenUntil: string|null;
  basementSize: number;

  unlockedFeatures: { feature: string, unlockedOn: string }[];
  subscription: { monthlyAmountInCents: number }|null;

  menu: { items: UserMenuItem[], numberLocked: number };
}

export function isFeatureUnlocked(user: MyAccountSerializationGroup, feature: string): boolean
{
  return user.unlockedFeatures.find(f => f.feature === feature) !== undefined;
}

export function getFeatureUnlockedDate(user: MyAccountSerializationGroup, feature: string): string|null
{
  return user.unlockedFeatures.find(f => f.feature === feature)?.unlockedOn;
}

export interface UserMenuItem
{
  location: string;
  isNew: boolean;
  sortOrder: number;
}

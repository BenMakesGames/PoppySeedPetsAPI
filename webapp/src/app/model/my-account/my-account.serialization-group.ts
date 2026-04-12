/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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

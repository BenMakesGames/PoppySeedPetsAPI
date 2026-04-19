/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { applicationConfig, Meta, StoryObj } from '@storybook/angular';
import { InventoryItemComponent } from "./inventory-item.component";
import { UserDataService } from "../../../../service/user-data.service";
import { BehaviorSubject } from "rxjs";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { createUser } from "../../../../../../.storybook/main";

const meta: Meta<InventoryItemComponent> = {
  title: 'Shared/Inventory Item',
  tags: ['autodocs'],
  component: InventoryItemComponent,
  decorators: [
    applicationConfig({
      providers: [
        {
          provide: UserDataService,
          useValue: {
            user: new BehaviorSubject<MyAccountSerializationGroup>({
              ...createUser(),
              unlockedFeatures: [
                {
                  feature: 'Market',
                  unlockedOn: '2024-12-15'
                }
              ]
            })
          }
        }
      ]
    })
  ],
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<InventoryItemComponent>;

export const InventoryItem: Story = {
  args: {
    inventory: {
      item: {
        image: 'tool/sword/iron',
        name: 'Iron Sword',
        recycleValue: 2,
      },
      illusion: null,
    },
    lockedToOwner: false,
    showRecycleValue: false,
    museumPoints: null
  },
};

export const InventoryItemWithAnIllusion: Story = {
  args: {
    inventory: {
      item: {
        image: 'tool/sword/iron',
        name: 'Iron Sword',
        recycleValue: 2,
      },
      illusion: {
        name: 'Orange',
        image: 'fruit/orange',
      },
    },
    lockedToOwner: false,
    showRecycleValue: false,
    museumPoints: null
  },
};
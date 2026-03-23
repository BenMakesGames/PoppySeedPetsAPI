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
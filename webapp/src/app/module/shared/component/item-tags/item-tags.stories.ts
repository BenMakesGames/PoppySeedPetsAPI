import { applicationConfig, Meta, StoryObj } from '@storybook/angular';
import { ItemTagsComponent } from "./item-tags.component";
import { ActivatedRoute } from "@angular/router";

/**
 * Renders an item's properties and groups as "tags".
 */
const meta: Meta<ItemTagsComponent> = {
  title: 'Shared/Item Tags',
  tags: ['autodocs'],
  component: ItemTagsComponent,
  decorators: [
    applicationConfig({
      providers: [
        { provide: ActivatedRoute, useValue: {} }
      ]
    })
  ],
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<ItemTagsComponent>;

export const ItemTags: Story = {
  args: {
    item: {
      hat: true,
      greenhouseType: 'Plant',
      isFlammable: true,
      isFertilizer: false,
      isTreasure: false,
      recycleValue: 3,
      itemGroups: [
        { name: 'Recipe' },
        { name: 'Sword' },
      ]
    }
  },
};
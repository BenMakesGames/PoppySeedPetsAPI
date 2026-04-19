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
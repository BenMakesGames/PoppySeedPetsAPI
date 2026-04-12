/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Meta, StoryObj } from '@storybook/angular';
import { FireComponent } from "./fire.component";

/**
 * Renders an animated fireplace at a given strength.
 */
const meta: Meta<FireComponent> = {
  title: 'Fireplace/Fire',
  tags: ['autodocs'],
  component: FireComponent,
  argTypes: {
    strength: {
      description: 'The strength of the fire, from 0 to 100.',
    }
  }
};
export default meta;

type Story = StoryObj<FireComponent>;

export const Fire: Story = {
  args: {
    strength: 65
  },
};
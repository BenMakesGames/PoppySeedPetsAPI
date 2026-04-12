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
import { StatBarComponent } from "./stat-bar.component";

/**
 * Renders a bar to represent a pet's skill. At 20 (the max), it pulses with light.
 */
const meta: Meta<StatBarComponent> = {
  title: 'Shared/Stat Bar',
  tags: ['autodocs'],
  component: StatBarComponent,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<StatBarComponent>;

export const StatBar: Story = {
  args: {
    value: 13,
  },
};

export const FullStatBar: Story = {
  args: {
    value: 20,
  },
};
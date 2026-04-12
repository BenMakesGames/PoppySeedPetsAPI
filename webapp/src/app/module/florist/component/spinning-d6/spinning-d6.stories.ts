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
import { SpinningD6Component } from "./spinning-d6.component";

/**
 * A rolling die.
 */
const meta: Meta<SpinningD6Component> = {
  title: 'Florist/Spinning D6',
  tags: ['autodocs'],
  component: SpinningD6Component,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<SpinningD6Component>;

export const SpinningD6: Story = {
  name: 'Spinning D6',
  args: {
    result: null,
    size: '3rem',
  },
};

export const SpinningD6AtRest: Story = {
  name: 'Spinning D6 At Rest',
  args: {
    result: 5,
    size: '3rem',
  },
};
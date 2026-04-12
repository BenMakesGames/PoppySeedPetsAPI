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
import { MinuteTimerComponent } from "./minute-timer.component";

/**
 * Counts down from a given number of minutes, in real time, using an animated hourglass.
 *
 * TODO: add a rotate 180° animation when each minute passes.
 */
const meta: Meta<MinuteTimerComponent> = {
  title: 'Shared/Minute Timer',
  tags: ['autodocs'],
  component: MinuteTimerComponent,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<MinuteTimerComponent>;

export const MinuteTimer: Story = {
  args: {
    minutesRemaining: 45,
  },
};
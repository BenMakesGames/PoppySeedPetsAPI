/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { componentWrapperDecorator, Meta, StoryObj } from '@storybook/angular';
import { DonutChartComponent } from "./donut-chart.component";

/**
 * Used for summarizing a pet's lifetime activity stats.
 *
 * Donut chart is rendered using D3.js.
 */
const meta: Meta<DonutChartComponent> = {
  title: 'Shared/Donut Chart',
  tags: ['autodocs'],
  component: DonutChartComponent,
  decorators: [
    componentWrapperDecorator((story) => `<div style="width:2in">${story}</div>`),
  ],
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<DonutChartComponent>;

export const DonutChart: Story = {
  args: {
    data: [
      { label: 'Blue', value: 10, color: '#336699' },
      { label: 'Green', value: 20, color: '#669933' },
    ]
  },
};
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
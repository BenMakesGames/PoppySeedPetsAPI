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
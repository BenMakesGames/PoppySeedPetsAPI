import { Meta, StoryObj } from '@storybook/angular';
import { HeartMeterComponent } from "./heart-meter.component";

/**
 * Represents a pet's commitment level in a relationship.
 */
const meta: Meta<HeartMeterComponent> = {
  title: 'Shared/Friend Meter',
  tags: ['autodocs'],
  component: HeartMeterComponent,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<HeartMeterComponent>;

export const FriendMeter: Story = {
  args: {
    commitment: 3.5,
  },
};
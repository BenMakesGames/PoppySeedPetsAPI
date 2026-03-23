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
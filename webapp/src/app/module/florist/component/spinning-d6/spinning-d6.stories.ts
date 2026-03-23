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
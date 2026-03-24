import { Meta, StoryObj } from '@storybook/angular';
import { LoadingThrobberComponent } from "./loading-throbber.component";

const meta: Meta<LoadingThrobberComponent> = {
  title: 'Shared/Loading Throbber',
  tags: ['autodocs'],
  component: LoadingThrobberComponent,
  argTypes: {
    randomText: {
      control: 'boolean',
      description: 'If true, there\'s a small chance (2%) that a random loading message is used.',
    },
    text: {
      control: 'text',
    },
  }
};
export default meta;

type Story = StoryObj<LoadingThrobberComponent>;

export const LoadingThrobber: Story = {
  args: {
    randomText: false,
    text: 'Loading',
  },
};
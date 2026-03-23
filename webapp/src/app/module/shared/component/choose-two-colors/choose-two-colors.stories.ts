import { componentWrapperDecorator, Meta, StoryObj } from '@storybook/angular';
import { ChooseTwoColorsComponent } from "./choose-two-colors.component";

/**
 * Allows the user to choose two colors, primarily used for selecting pet colors.
 */
const meta: Meta<ChooseTwoColorsComponent> = {
  title: 'Shared/Choose Two Colors',
  tags: ['autodocs'],
  component: ChooseTwoColorsComponent,
  decorators: [
    componentWrapperDecorator((story) => `<label style="margin-top:17em">Choose two colors</label>${story}`),
  ],
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<ChooseTwoColorsComponent>;

export const ChooseTwoColors: Story = {
  args: {
    colorA: '339966',
    colorB: '9966ff',
  },
};
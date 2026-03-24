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
import { Meta, StoryObj } from '@storybook/angular';
import { ThemePreviewComponent } from "./theme-preview.component";
import { ThemeService } from "../../../shared/service/theme.service";

const meta: Meta<ThemePreviewComponent> = {
  title: 'Painter/Theme Preview',
  tags: ['autodocs'],
  component: ThemePreviewComponent,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<ThemePreviewComponent>;

export const ThemePreview: Story = {
  args: {
    theme: ThemeService.Themes[0]
  },
};

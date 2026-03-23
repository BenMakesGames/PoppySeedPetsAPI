import { componentWrapperDecorator, Preview } from "@storybook/angular";
import '../src/polyfills';

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
  decorators: [
    componentWrapperDecorator((story) => `<main>${story}</main>`),
  ],
};

export default preview;

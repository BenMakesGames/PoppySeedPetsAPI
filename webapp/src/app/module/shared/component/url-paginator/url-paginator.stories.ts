import { applicationConfig, Meta, StoryObj } from '@storybook/angular';
import { UrlPaginatorComponent } from "./url-paginator.component";
import { ActivatedRoute } from "@angular/router";

/**
 * `UrlPaginator` replaces the old paginator. This paginator interacts with the URL and browser history - a better user experience!
 */
const meta: Meta<UrlPaginatorComponent> = {
  title: 'Shared/Url Paginator',
  tags: ['autodocs'],
  component: UrlPaginatorComponent,
  decorators: [
    applicationConfig({
      providers: [
        { provide: ActivatedRoute, useValue: {} }
      ]
    })
  ],
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<UrlPaginatorComponent>;

export const UrlPaginator: Story = {
  args: {
    path: '/',
    page: 2,
    pageCount: 25,
  },
};
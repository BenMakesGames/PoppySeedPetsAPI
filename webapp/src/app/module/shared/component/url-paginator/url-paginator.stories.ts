/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
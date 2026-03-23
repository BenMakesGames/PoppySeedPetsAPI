import { Meta, StoryObj } from '@storybook/angular';
import { ItemPriceHistoryComponent } from "./item-price-history.component";

/**
 * Used for rendering an item's price in the player market.
 *
 * Data can be viewed as either a graph (using D3.js), or a table.
 */
const meta: Meta<ItemPriceHistoryComponent> = {
  title: 'Shared/Item Price History',
  tags: ['autodocs'],
  component: ItemPriceHistoryComponent,
  argTypes: {
  }
};
export default meta;

type Story = StoryObj<ItemPriceHistoryComponent>;

let yesterday = new Date();
yesterday.setDate(yesterday.getDate() - 1);

let twoDaysAgo = new Date();
twoDaysAgo.setDate(twoDaysAgo.getDate() - 2);

let threeDaysAgo = new Date();
threeDaysAgo.setDate(threeDaysAgo.getDate() - 3);

export const ItemPriceHistory: Story = {
  args: {
    data: {
      history: [
        {
          daysAgo: 3,
          date: threeDaysAgo.toISOString().split('T')[0],
          minPrice: 2,
          maxPrice: 4,
          averagePrice: 3
        },
        {
          daysAgo: 2,
          date: twoDaysAgo.toISOString().split('T')[0],
          minPrice: 10,
          maxPrice: 13,
          averagePrice: 11.2
        },
        {
          daysAgo: 1,
          date: yesterday.toISOString().split('T')[0],
          minPrice: 8,
          maxPrice: 12,
          averagePrice: 10.7
        },
      ],
      lastHistory: {
        daysAgo: 1,
        date: yesterday.toISOString().split('T')[0],
        minPrice: 8,
        maxPrice: 12,
        averagePrice: 10.7
      }
    }
  },
};
export const ItemPriceHistoryWithOneItem: Story = {
  args: {
    data: {
      history: [

      ],
      lastHistory: {
        daysAgo: 1,
        date: yesterday.toISOString().split('T')[0],
        minPrice: 8,
        maxPrice: 12,
        averagePrice: 10.7
      }
    }
  },
};
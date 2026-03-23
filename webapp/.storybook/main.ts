import type { StorybookConfig } from "@storybook/angular";
import { MyAccountSerializationGroup } from "../src/app/model/my-account/my-account.serialization-group";

const config: StorybookConfig = {
  stories: ["../src/**/*.mdx", "../src/**/*.stories.@(js|jsx|mjs|ts|tsx)"],
  addons: [
    "@storybook/addon-essentials",
  ],
  framework: {
    name: "@storybook/angular",
    options: {
      styles: [
        "../src/styles.scss",
      ]
    },
  },
  staticDirs: [ '../src' ]
};
export default config;

export function createUser(): MyAccountSerializationGroup
{
  return {
    id: 1,
    name: 'Test',
    email: 'test@poppyseedpets.com',
    icon: null,
    lastAllowanceCollected: '2004-04-04',
    moneys: 100,
    recyclePoints: 100,
    maxPets: 3,
    maxSellPrice: 150,
    maxMarketBids: 5,
    roles: [],
    unreadNews: 0,
    unreadLetters: 0,
    canAssignHelpers: true,
    unlockedFeatures: [],
    subscription: null,
    lastPerformedQualityTime: '2004-04-04',
    menu: {
      items: [],
      numberLocked: 0,
    }
  };
}
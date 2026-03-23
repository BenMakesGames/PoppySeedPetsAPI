export interface GlobalStatsTimeSeriesModel {
  date: string;
  numberOfPlayers1Day: number;
  numberOfPlayers3Day: number;
  numberOfPlayers7Day: number;
  numberOfPlayers28Day: number;
  totalMoneys1Day: number;
  totalMoneys3Day: number;
  totalMoneys7Day: number;
  totalMoneys28Day: number;
  newPlayers1Day: number;
  newPlayers3Day: number;
  newPlayers7Day: number;
  newPlayers28Day: number;
  unlockedTrader1Day: number;
  unlockedTrader3Day: number;
  unlockedTrader7Day: number;
  unlockedTrader28Day: number;
  unlockedFireplace1Day: number;
  unlockedFireplace3Day: number;
  unlockedFireplace7Day: number;
  unlockedFireplace28Day: number;
  unlockedGreenhouse1Day: number;
  unlockedGreenhouse3Day: number;
  unlockedGreenhouse7Day: number;
  unlockedGreenhouse28Day: number;
  unlockedBeehive1Day: number;
  unlockedBeehive3Day: number;
  unlockedBeehive7Day: number;
  unlockedBeehive28Day: number;
  unlockedPortal1Day: number;
  unlockedPortal3Day: number;
  unlockedPortal7Day: number;
  unlockedPortal28Day: number;
}

export interface GlobalStatsMetric {
  label: string;
  value: string;
}

export const GLOBAL_STATS_METRICS: GlobalStatsMetric[] = [
  { label: 'New Players', value: 'newPlayers' },
  { label: 'Active Players', value: 'numberOfPlayers' },
  { label: 'Total Moneys', value: 'totalMoneys' },
  { label: 'Have Trader', value: 'unlockedTrader' },
  { label: 'Have Fireplace', value: 'unlockedFireplace' },
  { label: 'Have Greenhouse', value: 'unlockedGreenhouse' },
  { label: 'Have Beehive', value: 'unlockedBeehive' },
  { label: 'Have Hollow Earth', value: 'unlockedPortal' }
];

export const GLOBAL_STATS_PERIODS: { label: string; value: string }[] = [
  { label: '1-day', value: '1Day' },
  { label: '3-day', value: '3Day' },
  { label: 'Week', value: '7Day' },
  { label: 'Month', value: '28Day' }
]; 
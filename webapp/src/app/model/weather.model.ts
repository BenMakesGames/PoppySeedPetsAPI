export interface WeatherDataModel
{
  date: string;
  holidays: string[];
  sky: WeatherSky;
}

export enum WeatherSky
{
  Clear = 'clear',
  Cloudy = 'cloudy',
  Rainy = 'rainy',
  Snowy = 'snowy',
  Stormy = 'stormy',
}

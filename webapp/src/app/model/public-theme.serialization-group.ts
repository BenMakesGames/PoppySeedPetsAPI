import { ThemeInterface } from "./theme.interface";

export interface PublicThemeSerializationGroup extends ThemeInterface
{
  id: number;
  user: {
    name: string,
    id: number,
    icon: string
  }
}

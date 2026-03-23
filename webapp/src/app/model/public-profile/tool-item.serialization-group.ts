import {ToolItemGripSerializationGroup} from "./tool-item-grip.serialization-group";

export interface ToolItemSerializationGroup
{
  name: string;
  image: string;
  tool?: ToolItemGripSerializationGroup;
  modifiers?: string[];
  hat: any|null;
}

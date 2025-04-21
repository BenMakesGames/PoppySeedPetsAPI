import {StoryChoiceActionModel} from "./story-choice-action.model";

export interface StoryChoiceModel
{
  text: string;
  actions: StoryChoiceActionModel[];
  requiredInventory?: string;
}
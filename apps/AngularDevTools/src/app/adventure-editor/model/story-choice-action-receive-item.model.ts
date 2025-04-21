import {StoryChoiceActionModel} from "./story-choice-action.model";

export interface StoryChoiceActionReceiveItemModel extends StoryChoiceActionModel
{
  item: string;
  description: string;
}
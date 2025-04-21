import {StoryChoiceModel} from "./story-choice.model";

export interface StorySectionModel
{
  id: number;
  story_id: number;
  style: string;
  background: string|null;
  image: string|null;
  content: string;
  choices: StoryChoiceModel[];
}
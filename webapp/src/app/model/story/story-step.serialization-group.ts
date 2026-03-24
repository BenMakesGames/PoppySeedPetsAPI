import {StoryStepChoiceSerializationGroup} from "./story-step-choice.serialization-group";

export interface StoryStepSerializationGroup
{
  storyTitle: string;
  style: string;
  background: string;
  image: string;
  content: string;
  choices: StoryStepChoiceSerializationGroup[];
}

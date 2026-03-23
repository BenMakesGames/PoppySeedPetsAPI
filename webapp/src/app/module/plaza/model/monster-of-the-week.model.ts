export interface MonsterOfTheWeekModel
{
  id: number;
  type: string;
  level: number;
  communityTotal: number;
  personalContribution: number;
  milestones: MonsterOfTheWeekMilestoneModel[];
}

export interface MonsterOfTheWeekMilestoneModel
{
  value: number;
  item: { name: string, image: string };
}
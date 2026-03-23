import {ToolSerializationGroup} from "../public-profile/tool.serialization-group";
import {SpiritCompanionSerializationGroup} from "./spirit-companion.serialization-group";
import {LunchboxItemSerializationGroup} from "./lunchbox-item.serialization-group";
import {ComputedPetSkillsSerializationGroup} from "./computed-pet-skills.serialization-group";

export interface MyPetSerializationGroup
{
  id: number;
  name: string;
  colorA: string;
  colorB: string;
  tool: ToolSerializationGroup;
  hat: any;
  skills: ComputedPetSkillsSerializationGroup;
  species: { image: string, handX: number, handY: number, handAngle: number, hatX: number, hatY: number, hatAngle: number, flipX: boolean, handBehind: boolean, pregnancyStyle: number, eggImage: string, family: string, name: string };
  level: number;
  note: string;
  costume: string;
  affectionLevel: number;
  affectionRewardsClaimed: number;
  merits: { name: string }[];
  spiritCompanion: SpiritCompanionSerializationGroup|null;
  hasRelationships: boolean;
  lastParkEvent: string;
  parkEventType?: string;
  canInteract: boolean;
  canParticipateInParkEvents: boolean;
  houseTime?: { activityTime: number };
  needs: {
    food: { description: string, percent?: number },
    safety: { description: string, percent?: number },
    love: { description: string, percent?: number },
    esteem: { description: string, percent?: number },
  },
  statuses: string[];
  pregnancy: { eggColor: string, pregnancyProgress: string };
  poisonLevel: string;
  alcoholLevel: string;
  hallucinogenLevel: string;
  isFertile: boolean;
  canPickTalent: string|null;
  flavor: string;
  maximumFriends: number;
  lunchboxItems: LunchboxItemSerializationGroup[];
  selfReflectionPoint: number;
  craving: string|null;
  location?: string;
  renamingCharges: number;

  birthDate: string;
  scale: number;
  emoji?: string;

  lunchboxIndex: number;

  badges: { badge: string, dateAcquired: string }[];
}

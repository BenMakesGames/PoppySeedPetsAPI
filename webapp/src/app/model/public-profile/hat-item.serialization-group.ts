export interface HatItemSerializationGroup
{
  name: string;
  image: string;
  hat?: { headX: number, headY: number, headScale: number, headAngle: number, headAngleFixed: boolean };
}

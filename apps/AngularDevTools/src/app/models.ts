export interface GripModel
{
  image?: string;
  x: number;
  y: number;
  angle: number;
  angleFixed: boolean;
  scale: number;
}

export interface HatModel
{
  image?: string;
  x: number;
  y: number;
  angle: number;
  angleFixed: boolean;
  scale: number;
}

export interface SpeciesModel
{
  image?: string;
  flipX: boolean;
  handX: number;
  handY: number;
  handAngle: number;
  handBehind: boolean;
  hatX: number;
  hatY: number;
  hatAngle: number;
}
export interface FieldGuideEntry
{
  entry: {
    type: string,
    name: string,
    image: string,
    description: string,
  };
  discoveredOn: string;
  comment: string;
}
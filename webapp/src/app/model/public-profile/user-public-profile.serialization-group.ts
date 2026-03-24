export interface UserPublicProfileSerializationGroup
{
  id: number;
  name: string;
  icon: string;
  lastActivity: string;
  registeredOn: string;
  following?: { note: string };
}

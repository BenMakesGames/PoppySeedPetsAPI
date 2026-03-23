export interface GuildMemberSerializationGroup {
  id: number;
  name: string;
  colorA: string;
  colorB: string;
  species: { image: string };
  guildMembership: { joinedOn: string, rank: string };
}

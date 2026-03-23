import { TraderOffer } from "./trader-offer.serialization-group";

export interface TradeGroup
{
  title: string;
  trades: TraderOffer[];
}

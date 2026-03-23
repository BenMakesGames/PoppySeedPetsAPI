import { TradeGroup } from "./trade-group.serialization-group";

export interface TradeOffersSerializationGroup
{
  message: string;
  trades: TradeGroup[];
}
import { TraderCostOrYieldModel } from "../../../model/trader-cost-or-yield.model";

export interface TraderOffer
{
  id: string;
  cost: TraderCostOrYieldModel[];
  'yield': TraderCostOrYieldModel[];
  comment: string;
  canMakeExchange: number;
  lockedToAccount: boolean;
}

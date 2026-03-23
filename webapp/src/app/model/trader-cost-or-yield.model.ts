export interface TraderCostOrYieldModel
{
  type: 'money'|'item'|'recyclingPoints';
  item?: { name: string, image: string };
  quantity: number;
}

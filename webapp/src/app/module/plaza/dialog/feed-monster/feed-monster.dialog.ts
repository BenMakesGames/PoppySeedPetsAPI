import { Component, Inject, OnDestroy } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { ThemeService } from "../../../shared/service/theme.service";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { MonsterOfTheWeekModel } from "../../model/monster-of-the-week.model";

@Component({
    templateUrl: './feed-monster.dialog.html',
    styleUrls: ['./feed-monster.dialog.scss'],
    standalone: false
})
export class FeedMonsterDialog implements OnDestroy {

  monster: MonsterOfTheWeekModel;

  getFoodsSubscription: Subscription;

  lastClicked = [];
  loading = false;
  foods: MonsterFood[] = null;
  selected: any = {};
  numSelected = 0;

  constructor(
    private dialogRef: MatDialogRef<FeedMonsterDialog>,
    private themeService: ThemeService,
    private api: ApiService,
    @Inject(MAT_DIALOG_DATA) private data: any
  ) {
    this.monster = data.monster;

    this.getFoodsSubscription = this.api.get<MonsterFood[]>('/monsterOfTheWeek/' + this.monster.id + '/getFood').subscribe({
      next: (r: ApiResponseModel<MonsterFood[]>) => {
        this.foods = r.data.sort((a, b) => a.points != b.points ? (b.points - a.points) : a.item.name.localeCompare(b.item.name));
      }
    });
  }

  ngOnDestroy() {
    this.getFoodsSubscription.unsubscribe();
  }

  recordLastClicked(inventory: MonsterFood)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doDoubleClickItem(inventory: MonsterFood)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'doubleClick')
      return;

    if(!this.lastClicked.every(c => c === inventory))
      return;

    this.multiSelect(inventory);
  }

  private multiSelect(food: MonsterFood)
  {
    if(this.loading) return;

    const select = !this.selected.hasOwnProperty(food.id);

    if(select)
    {
      this.foods.filter(f => f.item.name === food.item.name).forEach(f => {
        if(!this.selected.hasOwnProperty(f.id))
        {
          this.selected[f.id] = true;
          this.numSelected++;
        }
      });
    }
    else
    {
      this.foods.filter(f => f.item.name === food.item.name).forEach(f => {
        if(this.selected.hasOwnProperty(f.id)) {
          delete this.selected[f.id];
          this.numSelected--;
        }
      });
    }
  }

  doSelectFood(food: MonsterFood)
  {
    this.recordLastClicked(food);

    if(this.selected.hasOwnProperty(food.id)) {
      delete this.selected[food.id];
      this.numSelected--;
    }
    else
    {
      this.selected[food.id] = true;
      this.numSelected++;
    }
  }

  doFeedMonster()
  {
    if(this.loading || this.numSelected === 0) return;

    this.loading = true;

    this.api.post<ContributionData>('/monsterOfTheWeek/' + this.monster.id + '/contribute', { items: Object.keys(this.selected) }).subscribe({
      next: (r: ApiResponseModel<ContributionData>) => {
        this.dialogRef.close(r.data);
      },
      error: () => {
        this.loading = false;
      }
    })

  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog, monster: MonsterOfTheWeekModel): MatDialogRef<FeedMonsterDialog>
  {
    return matDialog.open(FeedMonsterDialog, {
      data: {
        monster: monster
      }
    });
  }
}

interface MonsterFood
{
  id: number;
  item: { name: string, image: string };
  points: number;
}

export interface ContributionData
{
  personalContribution: number;
  communityTotal: number;
}
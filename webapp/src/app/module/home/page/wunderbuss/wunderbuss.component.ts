import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: './wunderbuss.component.html',
    styleUrls: ['./wunderbuss.component.scss'],
    standalone: false
})
export class WunderbussComponent implements OnInit {
  inventoryId: number;
  route: string;

  alreadyMadeWish = false;
  DialogState = DialogState;
  state = DialogState.Loading;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  doRestartDialog()
  {
    this.state = this.alreadyMadeWish ? DialogState.AlreadyWished : DialogState.MakeAWish;
  }

  doAskWhoWunderbossIs()
  {
    this.state = DialogState.AskAboutWunderboss;
  }

  doAskAboutTheContract()
  {
    this.state = DialogState.AskAboutContract;
  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.api.get<boolean>('/item/wunderbuss/' + this.inventoryId + '/usedWish').subscribe({
      next: r => {
        this.alreadyMadeWish = r.data;
        this.doRestartDialog();
      }
    })
  }

  doIt(itemId: number|null)
  {
    if(itemId === null) return;

    if(this.state === DialogState.MakingWish) return;

    this.state = DialogState.MakingWish;

    this.api.post<any>('/item/wunderbuss/' + this.inventoryId, { itemId: itemId })
      .subscribe({
        next: _ => {
          this.router.navigateByUrl('/home');
        },
        error: () => {
          this.state = DialogState.MakeAWish;
        }
      })
    ;
  }

}

enum DialogState
{
  Loading,
  MakeAWish,
  AskAboutWunderboss,
  AskAboutContract,
  MakingWish,
  AlreadyWished,
  WishFulfilled
}

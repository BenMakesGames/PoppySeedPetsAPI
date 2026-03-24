import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import { ActivatedRoute, Router } from "@angular/router";

@Component({
    templateUrl: './dragon-tongue.component.html',
    styleUrls: ['./dragon-tongue.component.scss'],
    standalone: false
})
export class DragonTongueComponent implements OnInit {
  inventoryId: number;

  speech: {greetings: string[], thanks: string[]}|null = null;
  loading = true;
  saving = false;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute,
    private router: Router
  )
  {
  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.api.get<{greetings: string[], thanks: string[]}>('/item/dragonTongue/' + this.inventoryId + '/speech')
      .subscribe({
        next: r => {
          this.speech = r.data;
          this.loading = false;
        },
        error: () => {
          this.loading = false;
        }
      });
  }

  doSetSpeech()
  {
    if(this.saving) return;

    this.saving = true;

    this.api.post<any>('/item/dragonTongue/' + this.inventoryId + '/speech', this.speech)
      .subscribe({
        next: (r) => {
          this.router.navigateByUrl('/home');
        },
        error: () => {
          this.saving = false;
        }
      })
    ;
  }

}

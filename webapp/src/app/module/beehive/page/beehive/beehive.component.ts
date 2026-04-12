/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MyBeehiveSerializationGroup} from "../../../../model/my-beehive.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {ItemDetailsDialog} from "../../../../dialog/item-details/item-details.dialog";
import {Subscription} from "rxjs";
import { SelectPetDialog } from "../../../../dialog/select-pet/select-pet.dialog";
import { MessagesService } from "../../../../service/messages.service";
import { InteractWithAwayPetDialog } from "../../../pet-helpers/dialog/interact-with-away-pet/interact-with-away-pet-dialog.component";
import { MatDialog } from "@angular/material/dialog";
import { FeedBeehiveDialog } from "../../dialog/feed-beehive/feed-beehive.dialog";

@Component({
    templateUrl: './beehive.component.html',
    styleUrls: ['./beehive.component.scss'],
    standalone: false
})
export class BeehiveComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Beehive' };

  dialog = null;
  loading = true;
  beehive: MyBeehiveSerializationGroup;
  user: MyAccountSerializationGroup;
  interacting = false;
  beehiveAjax = Subscription.EMPTY;
  userSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userDataService: UserDataService, private matDialog: MatDialog,
    private messages: MessagesService
  ) {
    this.userSubscription = userDataService.user.subscribe(u => {
      this.user = u;
    });
  }

  ngOnInit() {
    this.beehiveAjax = this.api.get<MyBeehiveSerializationGroup>('/beehive').subscribe({
      next: (r: ApiResponseModel<MyBeehiveSerializationGroup>) => {
        this.loadBeehive(r.data);
        this.loading = false;
      }
    });
  }

  ngOnDestroy(): void {
    this.beehiveAjax.unsubscribe();
    this.userSubscription.unsubscribe();
  }

  private loadBeehive(beehive: MyBeehiveSerializationGroup)
  {
    this.beehive = beehive;
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }

  doGiveItem()
  {
    FeedBeehiveDialog.open(this.matDialog).afterClosed().subscribe({
      next: (data: MyBeehiveSerializationGroup|null|undefined) => {
        if(data)
        {
          this.beehive = data;
        }
      }
    })
  }

  doHarvest()
  {
    this.postInteraction('harvest');
  }

  private postInteraction(action: string, data: any = {})
  {
    if(this.interacting) return;

    this.interacting = true;

    this.api.post<MyBeehiveSerializationGroup>('/beehive/' + action, data).subscribe({
      next: (r: ApiResponseModel<MyBeehiveSerializationGroup>) => {
        this.dialog = null;
        this.beehive = r.data;
        this.interacting = false;
      },
      error: () => {
        this.dialog = null;
        this.interacting = false;
      }
    });
  }

  doAssignHelper()
  {
    SelectPetDialog.open(this.matDialog)
      .afterClosed()
      .subscribe(pet => {
        if(pet)
        {
          this.interacting = true;

          const everHadAHelper = this.user.canAssignHelpers;

          this.api.post('/beehive/assignHelper/' + pet.id).subscribe({
            next: (r: ApiResponseModel<MyBeehiveSerializationGroup>) => {
              this.dialog = null;
              this.beehive = r.data;
              this.interacting = false;

              if(this.userDataService.user.value.canAssignHelpers && !everHadAHelper)
              {
                this.dialog = 'Many thanks bzzbzz. Your pets are very industrious creatures. They possess the spirit of the bee. Perhaps they can assist you in other ways, as well bzzbzz.';

                this.messages.addGenericMessage('Bzzbzz! Your pets possess the spirit of the bee! (Who knew!) You can now assign them to help out in many of your house add-ons!');
              }
            },
            error: () => {
              this.dialog = null;
              this.interacting = false;
            }
          });
        }
      })
    ;
  }

  doRecallHelper()
  {
    if(this.interacting)
      return;

    this.interacting = true;

    this.api.post('/pet/' + this.beehive.helper.id + '/stopHelping').subscribe({
      next: _ => {
        this.beehive.helper = null;
        this.interacting = false;
      },
      error: _ => {
        this.interacting = false;
      }
    });
  }

  doViewHelper()
  {
    InteractWithAwayPetDialog.open(this.matDialog, this.beehive.helper.id, this.beehive.helper.name, [])
      .afterClosed()
      .subscribe({
        next: v => {
          if(v && v.newPet)
          {
            this.beehive.helper.name = v.newPet.name;
          }
        }
      })
    ;
  }
}

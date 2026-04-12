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
import {ApiService} from "../../../../shared/service/api.service";
import {MyPetSerializationGroup} from "../../../../../model/my-pet/my-pet.serialization-group";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {MyAccountSerializationGroup} from "../../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../../service/user-data.service";
import {ChoiceModel} from "../../../../../dialog/choose-one/choose-one.dialog";
import {EnterPassphraseDialog} from "../../../../../dialog/enter-passphrase/enter-passphrase.dialog";
import {InteractWithPetShelterPetDialog} from "../../../dialog/interact-with-pet-shelter-pet/interact-with-pet-shelter-pet.dialog";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";
import { NavService } from "../../../../../service/nav.service";
import { CreatePetSearchModel, PetSearchModel } from "../../../../../model/search/pet-search-model";
import { ReallyReleasePetDialog } from "../../../dialog/really-release-pet/really-release-pet.dialog";

@Component({
    templateUrl: './pick-up.component.html',
    styleUrls: ['./pick-up.component.scss'],
    standalone: false
})
export class PickUpComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Pet Shelter - Daycare Services' };

  user: MyAccountSerializationGroup;

  loaded = false;
  takingAction = false;
  actionsBarIsSticking = false;

  originalHousePets: MyPetSerializationGroup[] = [];
  housePets: MyPetSerializationGroup[]|null = null;
  houseHasChanges = false;

  dialog = 'The Daycare is a free service. You can drop off and pick up pets at your convenience.';
  daycareResults: FilterResultsSerializationGroup<MyPetSerializationGroup>|null = null;

  filter = CreatePetSearchModel();

  myPetsAjax = Subscription.EMPTY;
  daycarePageAjax = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userData: UserDataService, private matDialog: MatDialog,
    private navService: NavService
  ) { }

  ngOnInit() {
    this.user = this.userData.user.getValue();

    this.doChangePage(0);

    this.myPetsAjax = this.api.get<MyPetSerializationGroup[]>('/pet/my').subscribe({
      next: (r: ApiResponseModel<MyPetSerializationGroup[]>) => {
        this.housePets = r.data;
        this.originalHousePets = [ ... this.housePets ];

        this.checkIfLoaded();
      }
    });
  }

  ngOnDestroy(): void {
    this.myPetsAjax.unsubscribe();
    this.daycarePageAjax.unsubscribe();
  }

  doExplainMechanics1()
  {
    this.dialog = 'Pets in daycare won\'t go out hunting, or fishing, or anything like that, but we keep them fed and comfy. Friends can come visit pets in daycare, and if your pet is in any groups - like a band - they can still participate in group activities.';
  }

  doExplainMechanics2()
  {
    this.dialog = 'We have _tons_ of space here! You can only keep a few pets at home; we\'re happy to hold on to as many pets as you like!';
  }

  doExplainMechanics3()
  {
    this.dialog = 'They\'re released into The Wilds, where they continue to live out their lives, especially their social lives: like pets in daycare, pets in The Wilds can still be visited by friends, and can still participate in group activities.\n\nFinally: I\'m not sure if you\'ve seen one yet, but there\'s a scroll going around that\'s capable of summoning new pets to your household, seemingly randomly, or out of nowhere. In fact, any pets in The Wilds may be summoned by this scroll, so a pet you "give up" will probably end up in someone else\'s care, eventually.';
  }

  doChangePage(page: number)
  {
    this.daycarePageAjax.unsubscribe();

    this.daycarePageAjax = this.api.get<FilterResultsSerializationGroup<MyPetSerializationGroup>>('/pet/daycare', { 
      page: page, 
      filter: this.filter,
      orderBy: this.filter.orderBy
    }).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<MyPetSerializationGroup>>) => {
        this.daycareResults = r.data;

        this.checkIfLoaded();
      },
      error: () => {
        this.checkIfLoaded();
      }
    });
  }

  public petIsAtHome(pet: MyPetSerializationGroup)
  {
    return this.housePets.findIndex(p => p.id === pet.id) >= 0;
  }

  private checkIfLoaded()
  {
    this.loaded = !!this.housePets && !!this.daycareResults;
  }

  doFilter(filter: PetSearchModel)
  {
    this.filter = filter;
    this.doSearch();
  }

  doSearch()
  {
    this.doChangePage(0);
  }

  doClickPet(pet: MyPetSerializationGroup)
  {
    let choices: ChoiceModel[] = [];

    if(this.housePets.some(p => p.id === pet.id))
      choices.push({ label: 'Drop Off', value: 'drop-off' });
    else if(this.housePets.length < this.user.maxPets)
      choices.push({ label: 'Pick Up', value: 'pick-up' });

    choices.push({ label: 'Give Up', value: 'set-free' });

    InteractWithPetShelterPetDialog.open(this.matDialog, pet, choices).afterClosed()
      .subscribe((c: ChoiceModel) => {
        if(!c) return;

        if (c.value === 'pick-up')
          this.pickUp(pet);
        else if (c.value === 'drop-off')
          this.dropOff(pet);
        else if(c.value === 'set-free')
          this.throwAway(pet);
      })
    ;
  }

  private throwAway(pet: MyPetSerializationGroup) {
    ReallyReleasePetDialog.open(this.matDialog, pet).afterClosed()
      .subscribe(c => {
        if (c)
          this.confirmThrowAway(pet);
      })
    ;
  }

  private confirmThrowAway(pet: MyPetSerializationGroup)
  {
    EnterPassphraseDialog.open(this.matDialog).afterClosed()
      .subscribe(p => {
        if(!p) return;

        this.takingAction = true;

        this.api.post('/pet/' + pet.id + '/release', { confirmPassphrase: p }).subscribe({
          next: () => {
            this.takingAction = false;

            if(this.housePets.findIndex(p => p.id === pet.id) >= 0)
            {
              this.housePets = this.housePets.filter(p => p.id !== pet.id);
              this.originalHousePets = [ ...this.housePets ];
            }

            if(this.daycareResults.results.findIndex(p => p.id === pet.id) >= 0)
            {
              if(this.daycareResults.page === this.daycareResults.pageCount - 1 && this.daycareResults.results.length > 1)
                this.daycareResults.results = this.daycareResults.results.filter(p => p.id !== pet.id);
              else
                this.doChangePage(this.daycareResults.page);
            }
          },
          error: () => {
            this.takingAction = false;
          }
        })
      })
    ;
  }

  doSetSticky(sticky: boolean)
  {
    this.actionsBarIsSticking = sticky;
    this.navService.disableHeaderShadow.next(sticky);
  }

  public doSaveChanges()
  {
    if(this.takingAction) return;

    this.takingAction = true;

    const data = { pets: this.housePets.map(p => p.id) };

    this.api.post('/pet/daycare/arrange', data).subscribe({
      next: _ => {
        this.takingAction = false;
        this.houseHasChanges = false;
        this.originalHousePets = [ ...this.housePets ];
      },
      error: () => {
        this.takingAction = false;
      }
    })
  }

  public doRevert()
  {
    this.housePets = [ ... this.originalHousePets ];
    this.houseHasChanges = false;
  }

  public pickUp(pet: MyPetSerializationGroup)
  {
    if(this.takingAction) return;

    this.housePets.push(pet);

    this.checkForChanges();
  }

  public dropOff(pet: MyPetSerializationGroup)
  {
    if(this.takingAction) return;

    this.housePets = this.housePets.filter(p => p.id !== pet.id);

    this.checkForChanges();
  }

  private checkForChanges()
  {
    this.houseHasChanges =
      this.housePets.some(p => this.originalHousePets.findIndex(o => o.id === p.id) < 0) ||
      this.originalHousePets.some(o => this.housePets.findIndex(p => p.id === o.id) < 0);
  }
}

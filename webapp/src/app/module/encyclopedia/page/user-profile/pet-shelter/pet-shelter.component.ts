import {Component, OnDestroy, OnInit} from '@angular/core';
import {UserPublicProfileSerializationGroup} from "../../../../../model/public-profile/user-public-profile.serialization-group";
import {UserPublicProfilePetSerializationGroup} from "../../../../../model/public-profile/user-public-profile-pet.serialization-group";
import {MyAccountSerializationGroup} from "../../../../../model/my-account/my-account.serialization-group";
import {Subscription} from "rxjs";
import {ActivatedRoute} from "@angular/router";
import {ApiService} from "../../../../shared/service/api.service";
import {UserDataService} from "../../../../../service/user-data.service";
import {filter} from "rxjs/operators";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {PetPublicProfileSerializationGroup} from "../../../../../model/public-profile/pet-public-profile.serialization-group";

@Component({
    templateUrl: './pet-shelter.component.html',
    styleUrls: ['./pet-shelter.component.scss'],
    standalone: false
})
export class PetShelterComponent implements OnInit, OnDestroy {

  loadingProfile = false;
  profileId: string = '';
  profile: UserPublicProfileSerializationGroup;
  pets: FilterResultsSerializationGroup<UserPublicProfilePetSerializationGroup>;
  user: MyAccountSerializationGroup;
  userSubscription = Subscription.EMPTY;
  petAjax = Subscription.EMPTY;
  profileAjax = Subscription.EMPTY;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService, private userData: UserDataService
  ) {

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.profileId = params.get('user');

      this.userSubscription = this.userData.user
        .pipe(
          filter(u => u !== UserDataService.UNLOADED && (!this.user || !u || u.id !== this.user.id))
        )
        .subscribe(u => {
          this.user = u;

          this.loadProfile();
        })
      ;
    });
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.petAjax.unsubscribe();
    this.profileAjax.unsubscribe();
  }

  doChangePage(page = 0)
  {
    this.petAjax.unsubscribe();

    const data = {
      page: page,
      filter: {
        owner: this.profile.id,
        location: 'daycare'
      }
    };

    this.petAjax = this.api.get<FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>>('/pet', data).subscribe({
      next: r => {
        this.pets = r.data;
      }
    });
  }

  loadProfile()
  {
    if(!this.profileId || this.loadingProfile) return;

    this.loadingProfile = true;

    this.profileAjax = this.api.get<UserPublicProfileSerializationGroup>('/account/' + this.profileId + '/minimal').subscribe({
      next: (r: ApiResponseModel<UserPublicProfileSerializationGroup>) => {
        this.profile = r.data;
        this.loadingProfile = false;
        this.doChangePage();
      },
      error: () => {
        this.loadingProfile = false;
      }
    });
  }
}

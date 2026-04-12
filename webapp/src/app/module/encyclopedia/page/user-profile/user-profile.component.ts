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
import {ActivatedRoute} from "@angular/router";
import {Subscription} from "rxjs";
import {filter} from "rxjs/operators";
import {UserPublicProfileSerializationGroup} from "../../../../model/public-profile/user-public-profile.serialization-group";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {UserPublicProfilePetSerializationGroup} from "../../../../model/public-profile/user-public-profile-pet.serialization-group";
import {FireplaceMantleSerializationGroup} from "../../../../model/fireplace/fireplace-mantle.serialization-group";
import {Title} from "@angular/platform-browser";
import { ThemeService } from "../../../shared/service/theme.service";
import { ThemeInterface } from "../../../../model/theme.interface";
import { PublicThemeSerializationGroup } from "../../../../model/public-theme.serialization-group";
import { MessagesService } from "../../../../service/messages.service";

@Component({
    templateUrl: './user-profile.component.html',
    styleUrls: ['./user-profile.component.scss'],
    standalone: false
})
export class UserProfileComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Resident' };

  useThemeSubscription = Subscription.EMPTY;

  loadingProfile = false;
  profileId: string = '';
  profile: UserPublicProfileSerializationGroup;
  pets: UserPublicProfilePetSerializationGroup[];
  mantle: FireplaceMantleSerializationGroup[];
  links: { website: string, nameOrId: string }[] = [];
  user: MyAccountSerializationGroup;
  stocking: { appearance: string, colorA: string, colorB: string }|null;
  userSubscription: Subscription;
  profileAjax: Subscription;
  myTheme: ThemeInterface;
  profileTheme: PublicThemeSerializationGroup;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService, private userData: UserDataService,
    private titleService: Title, private themeService: ThemeService, private messages: MessagesService
  ) {

  }

  ngOnInit() {
    this.myTheme = this.themeService.getTheme();

    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.profileId = params.get('user');

      this.userSubscription = this.userData.user
        .pipe(
          filter(u => u !== undefined && (!this.user || !u || u.id !== this.user.id))
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
    this.themeService.setTheme(this.myTheme);

    if(this.userSubscription)
      this.userSubscription.unsubscribe();

    if(this.profileAjax)
      this.profileAjax.unsubscribe();
  }

  loadProfile()
  {
    if(!this.profileId || this.loadingProfile) return;

    this.loadingProfile = true;

    this.profileAjax = this.api.get<ProfileResponse>('/account/' + this.profileId).subscribe({
      next: (r: ApiResponseModel<ProfileResponse>) => {
        this.profile = r.data.user;
        this.links = r.data.links;
        this.pets = r.data.pets;
        this.mantle = r.data.mantle;
        this.stocking = r.data.stocking;
        this.loadingProfile = false;
        this.profileTheme = r.data.theme;
        this.titleService.setTitle('Poppy Seed Pets - Poppyopedia - Resident - ' + this.profile.name);
        this.themeService.setTheme(r.data.theme);
      },
      error: () => {
        this.loadingProfile = false;
      }
    });
  }

  doUseTheme()
  {
    this.useThemeSubscription = this.api.patch('/style/current', this.profileTheme).subscribe({
      next: () => {
        this.myTheme = this.profileTheme;
        this.messages.addGenericMessage('Got it!');
        this.themeService.setTheme(this.profileTheme);
      }
    });
  }
}

interface ProfileResponse
{
  user: UserPublicProfileSerializationGroup;
  links: { website: string, nameOrId: string }[];
  pets: UserPublicProfilePetSerializationGroup[];
  mantle?: FireplaceMantleSerializationGroup[];
  stocking?: { appearance: string, colorA: string, colorB: string };
  theme: PublicThemeSerializationGroup;
}

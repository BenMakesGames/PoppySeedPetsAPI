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
import {Subscription} from 'rxjs';
import {UserDataService} from "../../../../service/user-data.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
    selector: 'app-park',
    templateUrl: './park.component.html',
    styleUrls: ['./park.component.scss'],
    standalone: false
})
export class ParkComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Park', song: 'the-ocean' };

  userSubscription: Subscription;
  user;
  petsThatCanParticipate: MyPetSerializationGroup[];
  petsThatCannotParticipate: MyPetSerializationGroup[];
  loading = true;
  myPetsAjax: Subscription;

  constructor(private api: ApiService, private userData: UserDataService) {
  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe(u => { this.user = u; });

    this.myPetsAjax = this.api.get<MyPetSerializationGroup[]>('/pet/my').subscribe({
      next: (r: ApiResponseModel<MyPetSerializationGroup[]>) => {
        this.petsThatCanParticipate = r.data.filter(p => p.canParticipateInParkEvents);
        this.petsThatCannotParticipate = r.data.filter(p => !p.canParticipateInParkEvents);
        this.loading = false;
      }
    });
  }

  doChangeSelection(pet: MyPetSerializationGroup)
  {
    const data = {
      parkEventType: pet.parkEventType === 'null' ? null : pet.parkEventType,
    };

    // don't ever unsubscribe/cancel this AJAX request
    this.api.post('/park/signUpPet/' + pet.id, data).subscribe();
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.myPetsAjax.unsubscribe();
  }

}

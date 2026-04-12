/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnInit } from '@angular/core';
import { UserDataService } from "../../../../service/user-data.service";
import {
  isFeatureUnlocked,
  MyAccountSerializationGroup
} from "../../../../model/my-account/my-account.serialization-group";
import { LocationEnum } from "../../../../model/location.enum";

@Component({
    selector: 'app-summary',
    templateUrl: './summary.component.html',
    styleUrls: ['./summary.component.scss'],
    standalone: false
})
export class SummaryComponent implements OnInit {

  user: MyAccountSerializationGroup;

  unlockedLocations = [
    LocationEnum.Home,
    LocationEnum.Wardrobe,
    LocationEnum.Lunchbox,
  ];

  constructor(private userData: UserDataService) {
    this.user = userData.user.value;
  }

  ngOnInit(): void {
    if(isFeatureUnlocked(this.user, 'Basement'))
      this.unlockedLocations.push(LocationEnum.Basement);

    if(isFeatureUnlocked(this.user, 'Fireplace'))
      this.unlockedLocations.push(LocationEnum.Mantle);
  }

}

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

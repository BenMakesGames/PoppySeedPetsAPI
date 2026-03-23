import { Component, OnInit } from '@angular/core';
import { environment } from "../../../../../../environments/environment";
import { UserDataService } from "../../../../../service/user-data.service";

@Component({
    templateUrl: './patron-rewards.component.html',
    styleUrls: ['./patron-rewards.component.scss'],
    standalone: false
})
export class PatronRewardsComponent implements OnInit {
  pageMeta = { title: 'Settings - Patron Rewards' };

  patreonConfig = environment.patreon;
  userId = 0;

  constructor(private userData: UserDataService) {
    this.userId = userData.user.value.id;
  }

  ngOnInit() {
  }

  protected readonly encodeURIComponent = encodeURIComponent;
}

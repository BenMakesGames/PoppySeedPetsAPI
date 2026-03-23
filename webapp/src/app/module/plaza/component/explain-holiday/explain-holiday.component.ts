import { Component, Input, OnInit } from '@angular/core';
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";

@Component({
    selector: 'app-explain-holiday',
    templateUrl: './explain-holiday.component.html',
    styleUrls: ['./explain-holiday.component.scss'],
    standalone: false
})
export class ExplainHolidayComponent implements OnInit {

  @Input() holiday: string;

  user: MyAccountSerializationGroup;

  constructor(private userData: UserDataService) { }

  ngOnInit(): void {
    this.user = this.userData.user.getValue();
  }

}

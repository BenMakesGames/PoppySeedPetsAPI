import {Component, Input, OnInit} from '@angular/core';
import {Router} from "@angular/router";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";

@Component({
    selector: 'app-journal-nav',
    templateUrl: './journal-nav.component.html',
    styleUrls: ['./journal-nav.component.scss'],
    standalone: false
})
export class JournalNavComponent implements OnInit {

  @Input() active: string;

  user: MyAccountSerializationGroup;

  constructor(private router: Router, private userData: UserDataService) { }

  ngOnInit(): void {
    this.user = this.userData.user.getValue();
  }

  doNav(path: string) {
    this.router.navigate([ path ]);
  }
}

import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { MessagesService } from "../../../../service/messages.service";

@Component({
    templateUrl: './rename-yourself.component.html',
    styleUrls: ['./rename-yourself.component.scss'],
    standalone: false
})
export class RenameYourselfComponent implements OnInit {

  scrollId: number;
  newName = '';
  renaming = false;
  user: MyAccountSerializationGroup;

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute,
    private userData: UserDataService, private messages: MessagesService
  )
  {

  }

  ngOnInit()
  {
    this.user = this.userData.user.getValue();
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doRename()
  {
    if(this.renaming) return;

    this.newName = this.newName.trim();

    if(this.newName.length < 2 || this.newName.length > 30)
    {
      this.messages.addGenericMessage('Your name must be between 2 and 30 characters long.');
      return;
    }

    if(this.newName === this.user.name)
    {
      this.messages.addGenericMessage('That\'s already your name! :P');
      return;
    }

    this.renaming = true;

    const data = {
      pet: this.user.id,
      name: this.newName
    };

    this.api.patch('/item/renamingScroll/' + this.scrollId + '/readToSelf', data)
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.renaming = false;
        }
      })
  }

}

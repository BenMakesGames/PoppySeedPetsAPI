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

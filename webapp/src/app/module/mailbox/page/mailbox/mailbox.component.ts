/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy, OnInit } from '@angular/core';
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {MyLetterSerializationGroup} from "../../../../model/my-letter.serialization-group";
import {LetterDialog} from "../../dialog/letter/letter.dialog";
import { MatDialog } from "@angular/material/dialog";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    selector: 'app-mailbox',
    templateUrl: './mailbox.component.html',
    styleUrls: ['./mailbox.component.scss'],
    standalone: false
})
@HasSounds([ 'open-letter' ])
export class MailboxComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Mailbox' };

  mailboxSubscription = Subscription.EMPTY;
  user: MyAccountSerializationGroup|undefined;
  results: FilterResultsSerializationGroup<MyLetterSerializationGroup>;

  constructor(
    private readonly api: ApiService, private readonly userService: UserDataService,
    private readonly matDialog: MatDialog, private readonly sounds: SoundsService
  ) { }

  doViewLetter(letter: MyLetterSerializationGroup)
  {
    if(!letter.isRead)
    {
      letter.isRead = true;

      this.api.patch('/letter/' + letter.id + '/read').subscribe();
    }

    this.sounds.playSound('open-letter');
    LetterDialog.open(this.matDialog, letter);
  }

  ngOnInit(): void {
    this.user = this.userService.user.getValue();

    this.mailboxSubscription = this.api.get<FilterResultsSerializationGroup<MyLetterSerializationGroup>>('/letter').subscribe({
      next: r => {
        this.results = r.data;
      }
    });
  }

  ngOnDestroy(): void {
    this.mailboxSubscription.unsubscribe();
  }
}

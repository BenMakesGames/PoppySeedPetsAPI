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

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
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ItemDetailsDialog} from "../../../../dialog/item-details/item-details.dialog";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    templateUrl: './bookstore.component.html',
    styleUrls: ['./bookstore.component.scss'],
    standalone: false
})
@HasSounds([ 'chaching' ])
export class BookstoreComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Bookstore' };

  activeAisle: AisleData;
  dialogState = 'greeting';
  dialog = 'Welcome to the Bookstore!';
  defaultDialog = '';
  waitingForAjaxResponse = false;
  bookstoreData: BookstoreData;
  quest: BookstoreQuest|null;
  bookstoreAjax: Subscription;

  private readonly comeBackSoon = [
    'Hey, come back soon!',
    'Don\'t forget to visit again!',
    'Don\'t be a stranger!',
  ];

  private readonly weGetNewBooks = [
    'We get new books from time to time!',
    'We get new books now and again!',
    'Sometimes new books come in!'
  ];

  private comeBackSoonIndex: number;
  private weGetNewBooksIndex: number;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private sounds: SoundsService,
  ) {
    this.comeBackSoonIndex = Math.floor(Math.random() * this.comeBackSoon.length);
    this.weGetNewBooksIndex = Math.floor(Math.random() * this.weGetNewBooks.length);
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }

  doQuestMaybeLater()
  {
    this.dialogState = 'greeting';
    this.dialog = this.defaultDialog;
  }

  doGiveItem(item: string)
  {
    if(this.waitingForAjaxResponse) return;

    this.waitingForAjaxResponse = true;

    this.api.post<BookstoreData>('/bookstore/giveItem/' + item).subscribe({
      next: (r: ApiResponseModel<BookstoreData>) => {
        this.bookstoreData = r.data;
        this.quest = r.data.quest;

        if(!this.quest)
        {
          this.dialogState = 'greeting';
          this.dialog = 'Wow! Well, I think that\'s about it! Thanks a lot! I hope you\'re making good use of those renaming scrolls!';
        }

        this.waitingForAjaxResponse = false;
      },
      error: () => {
        this.waitingForAjaxResponse = false;
      }
    });
  }

  doBuy(forSale: BookForSale)
  {
    if(this.waitingForAjaxResponse) return;

    this.waitingForAjaxResponse = true;

    this.api.post('/bookstore/' + forSale.item.id + '/buy').subscribe({
      next: () => {
        this.sounds.playSound('chaching');
        this.setRandomDialog(forSale.item.name);
        this.waitingForAjaxResponse = false;
      },
      error: () => {
        this.waitingForAjaxResponse = false;
      }
    })
  }

  private setRandomDialog(itemName: string)
  {
    if(itemName === 'Welcome Note')
      this.dialog = 'Oh, did you lose yours? Well, here you go!';
    else if(this.activeAisle.name === 'Toys')
    {
      this.dialog = 'Ooh, ' + itemName + '! Have fun with that!';
    }
    else if(this.activeAisle.name === 'Café')
    {
      this.dialog = 'Enjoy your ' + itemName + '!';
    }
    else
    {
      const r = Math.floor(Math.random() * 6);

      if(r === 0)
        this.dialog = itemName + '? Fun!';
      else if(r === 1)
        this.dialog = 'Oh, ' + itemName + ', nice!';
      else if(r === 2)
        this.dialog = 'I think you\'ll like ' + itemName + '!';
      else if(r === 3)
        this.dialog = 'I hope you like it!';
      else if(r === 4)
        this.dialog = 'Enjoy your copy of ' + itemName + '!';
      else
        this.dialog = itemName + ' - that\'s a good read!';
    }

    this.dialog += "\n\n" + this.comeBackSoon[this.comeBackSoonIndex];
    this.comeBackSoonIndex = (this.comeBackSoonIndex + 1) % this.comeBackSoon.length;

    if(this.activeAisle.name !== 'Toys' && this.activeAisle.name !== 'Café')
    {
      this.dialog += ' ' + this.weGetNewBooks[Math.floor(Math.random() * this.weGetNewBooks.length)];
      this.weGetNewBooksIndex = (this.weGetNewBooksIndex + 1) % this.weGetNewBooks.length;
    }
  }

  ngOnInit() {
    this.bookstoreAjax = this.api.get<BookstoreData>('/bookstore').subscribe({
      next: (r: ApiResponseModel<BookstoreData>) => {
        this.bookstoreData = r.data;
        this.activeAisle = r.data.aisles[0];
        this.quest = r.data.quest;
        this.dialog = r.data.dialog;
        this.defaultDialog = r.data.dialog;
      }
    });
  }

  ngOnDestroy()
  {
    this.bookstoreAjax.unsubscribe();
  }

}

interface BookstoreData {
  dialog: string;
  aisles: AisleData[];
  quest: BookstoreQuest|null;
}

interface AisleData {
  name: string;
  icon: string;
  inventory: BookForSale[];
}

interface BookstoreQuest {
  askingFor: string[];
  dialog: string;
}

interface BookForSale
{
  price: number;
  item: { id: number, name: string, image: string };
}

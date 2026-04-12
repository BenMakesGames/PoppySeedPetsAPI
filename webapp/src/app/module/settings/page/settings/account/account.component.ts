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
import {Subscription} from "rxjs";
import {UserDataService} from "../../../../../service/user-data.service";
import {ApiService} from "../../../../shared/service/api.service";
import {MyAccountSerializationGroup} from "../../../../../model/my-account/my-account.serialization-group";
import {EnterPassphraseDialog} from "../../../../../dialog/enter-passphrase/enter-passphrase.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './account.component.html',
    styleUrls: ['./account.component.scss'],
    standalone: false
})
export class AccountComponent implements OnInit {
  pageMeta = { title: 'Settings - Account' };

  userId = '';
  passphrase1 = '';
  passphrase2 = '';
  passphraseError: string;
  name: string;
  email: string;
  loading = true;

  userDataSubscription: Subscription;

  constructor(private userData: UserDataService, private api: ApiService, private matDialog: MatDialog) {
  }

  ngOnInit() {
    this.userDataSubscription = this.userData.user.subscribe(
      (u: MyAccountSerializationGroup) => {
        this.userId = u.id.toString();
        this.name = u.name;
        this.email = u.email;
        this.loading = false;
      }
    );
  }

  ngOnDestroy() {
    this.userDataSubscription.unsubscribe();
  }

  doChangeEmail()
  {
    if(this.loading) return;

    this.loading = true;

    EnterPassphraseDialog.open(this.matDialog).afterClosed().subscribe(
      (passphrase) => {
        if(passphrase && passphrase.length > 0)
        {
          this.api.post('/account/updateEmail', { confirmPassphrase: passphrase, newEmail: this.email }).subscribe({
            next: () => {
              this.loading = false;
            },
            error: () => {
              this.loading = false;
            }
          });
        }
        else
          this.loading = false;
      }
    );
  }

  doChangePassphrase()
  {
    if(this.loading) return;

    this.loading = true;

    const p1 = this.passphrase1.trim();
    const p2 = this.passphrase2.trim();

    if(p1 !== p2)
    {
      this.passphraseError = 'Passphrases do not match.';
      this.loading = false;
      return;
    }
    else if(p1.length < 12)
    {
      this.passphraseError = 'Passphrase must be at least 12 characters. "Special characters" are not required - use a short sentence or a password manager.';
      this.loading = false;
      return;
    }

    this.passphraseError = null;

    EnterPassphraseDialog.open(this.matDialog).afterClosed().subscribe(
      (passphrase) => {
        if(passphrase && passphrase.length > 0)
        {
          this.api.post('/account/updatePassphrase', { confirmPassphrase: passphrase, newPassphrase: this.passphrase1 }).subscribe({
            next: () => {
              this.passphrase1 = '';
              this.passphrase2 = '';
              this.loading = false;
            },
            error: () => {
              this.loading = false;
            }
          });
        }
        else
          this.loading = false;
      }
    );
  }
}

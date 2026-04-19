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
import {ApiService} from "../../module/shared/service/api.service";
import { ActivatedRoute, Router } from "@angular/router";
import {UserDataService} from "../../service/user-data.service";
import {Subscription} from "rxjs";
import {MyAccountSerializationGroup} from "../../model/my-account/my-account.serialization-group";

@Component({
    selector: 'app-reset-password',
    templateUrl: './reset-passphrase.component.html',
    styleUrls: ['./reset-passphrase.component.scss'],
    standalone: false
})
export class ResetPassphraseComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Reset Passphrase', song: 'the-ocean' };

  loading = true;
  success = false;
  resetCode;

  passphrase = '';

  private userSubscription: Subscription;
  public user: MyAccountSerializationGroup;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute, private userData: UserDataService,
    private router: Router
  ) {

  }

  ngOnInit() {
    this.activatedRoute.paramMap.subscribe(
      params => {
        this.resetCode = params.get('code');
        this.loading = false;
      }
    );

    this.userSubscription = this.userData.user.subscribe(u => {
      this.user = u;
    })
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
  }

  doReset()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/account/requestPassphraseReset/' + this.resetCode, { passphrase: this.passphrase }).subscribe({
      next: () => {
        this.loading = false;
        this.success = true;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  doLoggedIn()
  {
    this.router.navigateByUrl('/home');
  }

}

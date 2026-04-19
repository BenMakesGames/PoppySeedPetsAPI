/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component } from '@angular/core';
import {ApiService} from "../../module/shared/service/api.service";

@Component({
    selector: 'app-request-passphrase-reset',
    templateUrl: './request-passphrase-reset.component.html',
    styleUrls: ['./request-passphrase-reset.component.scss'],
    standalone: false
})
export class RequestPassphraseResetComponent {

  loading = false;
  done = false;
  email = '';

  constructor(private api: ApiService) { }

  doRequestReset()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/account/requestPassphraseReset', { email: this.email }).subscribe({
      next: () => {
        this.done = true;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

}

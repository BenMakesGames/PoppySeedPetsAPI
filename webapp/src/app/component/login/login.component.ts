/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import {UserSessionService} from "../../service/user-session.service";
import { ThemeService } from "../../module/shared/service/theme.service";

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss'],
    standalone: false
})
export class LoginComponent implements OnInit {

  @Output() onLogIn = new EventEmitter<void>();

  loading = false;
  email = '';
  passphrase = '';
  rememberMe = true;

  constructor(private userSession: UserSessionService, private theme: ThemeService) { }

  ngOnInit() {
    if(window.localStorage.getItem('lastEmailAddress'))
      this.email = window.localStorage.getItem('lastEmailAddress');

    this.rememberMe = window.localStorage.getItem('rememberMe') !== 'no';
  }

  doLogIn()
  {
    if(this.loading) return;

    this.loading = true;

    window.localStorage.setItem('rememberMe', this.rememberMe ? 'yes' : 'no');

    if(!this.rememberMe)
      window.localStorage.removeItem('lastEmailAddress');

    this.userSession.logIn(this.email, this.passphrase, this.rememberMe).subscribe({
      next: r => {
        if(this.rememberMe)
          window.localStorage.setItem('lastEmailAddress', this.email);

        if(r.data.currentTheme)
          this.theme.setTheme(r.data.currentTheme);

        this.onLogIn.emit();
      },
      error: () => {
        this.loading = false;
      }
    })
  }

}

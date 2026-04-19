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
import {NavService} from "../../service/nav.service";
import {UserDataService} from "../../service/user-data.service";
import {Subscription} from "rxjs";
import {ArticleSerializationGroup} from "../../model/article.serialization-group";
import {ApiService} from "../../module/shared/service/api.service";
import {ApiResponseModel} from "../../model/api-response.model";
import {MyAccountSerializationGroup} from "../../model/my-account/my-account.serialization-group";

@Component({
    templateUrl: './portal.component.html',
    styleUrls: ['./portal.component.scss'],
    standalone: false
})
export class PortalComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Welcome!', song: 'the-ocean' };

  private userSubscription: Subscription;
  private newsAjax: Subscription;

  public user: MyAccountSerializationGroup|undefined;
  public article: ArticleSerializationGroup;

  public callAttentionToSignUpButton = true;

  constructor(
    private userData: UserDataService, private navService: NavService, private api: ApiService
  ) {

  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe(
      r => { this.user = r }
    );

    this.newsAjax = this.api.get<ArticleSerializationGroup>('/article/latest').subscribe(
      (d: ApiResponseModel<ArticleSerializationGroup>) => { this.article = d.data; }
    );

    if(window.localStorage.getItem('dontCallAttentionToSignUpButton'))
      this.callAttentionToSignUpButton = false;
    else
      window.localStorage.setItem('dontCallAttentionToSignUpButton', '1');
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
    this.newsAjax.unsubscribe();
  }

  doLogIn()
  {
    this.navService.openNav();
    setTimeout(() => {
      if((<any>document.getElementById('logInEmail')).value.length > 0)
        document.getElementById('logInPassphrase').focus();
      else
        document.getElementById('logInEmail').focus();
    });
  }
}

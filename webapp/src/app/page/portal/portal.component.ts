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

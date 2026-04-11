import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from "rxjs";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { ArticleSerializationGroup } from "../../../../model/article.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { ApiService } from "../../../shared/service/api.service";
import { UpsertArticleDialog } from "../../dialog/upsert-article/upsert-article.dialog";
import { AreYouSureDialog } from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './news.component.html',
    styleUrls: ['./news.component.scss'],
    standalone: false
})
export class NewsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'News' };

  postingToSocialMedia = false;
  page: number = 0;
  results: FilterResultsSerializationGroup<ArticleSerializationGroup>;
  user: MyAccountSerializationGroup;
  userSubscription: Subscription;
  articleSearchAjax: Subscription;

  constructor(private api: ApiService, private userData: UserDataService, private matDialog: MatDialog) {

  }

  ngOnInit() {
    this.doSearch();

    this.userSubscription = this.userData.user.subscribe(u => { this.user = u; });
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.articleSearchAjax.unsubscribe();
  }

  doNew()
  {
    if(NewsComponent.isLocalHost())
      this.confirmLocalhost(() => { this.reallyCreateNew(); });
    else
      this.reallyCreateNew();
  }

  private reallyCreateNew()
  {
    const upsertArticleDialog = UpsertArticleDialog.open(this.matDialog, null);

    const reloadSubscription = upsertArticleDialog.componentInstance.reloadArticles.subscribe(() => {
      this.doSearch();
    });

    upsertArticleDialog.afterClosed().subscribe(() => {
      reloadSubscription.unsubscribe();
    });
  }

  doEdit(article) {
    if(NewsComponent.isLocalHost())
      this.confirmLocalhost(() => { this.reallyEdit(article); });
    else
      this.reallyEdit(article);
  }

  private reallyEdit(article)
  {
    if(this.postingToSocialMedia) return;

    const upsertArticleDialog = UpsertArticleDialog.open(this.matDialog, { ...article });

    const reloadSubscription = upsertArticleDialog.componentInstance.reloadArticles.subscribe(() => {
      this.doSearch();
    });

    upsertArticleDialog.afterClosed().subscribe(() => {
      reloadSubscription.unsubscribe();
    });
  }

  private static isLocalHost(): boolean
  {
    return window.location.host.startsWith('localhost');
  }

  private confirmLocalhost(callback)
  {
    return AreYouSureDialog.open(this.matDialog, "This is localhost!", "Is this really where you want to be?")
      .afterClosed()
      .subscribe({
        next: r => {
          if(r)
            callback();
        }
      })
    ;
  }

  doSearch()
  {
    this.results = null;

    const data = {
      page: this.page
    };

    this.articleSearchAjax = this.api.get<FilterResultsSerializationGroup<ArticleSerializationGroup>>('/article', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<ArticleSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }

}

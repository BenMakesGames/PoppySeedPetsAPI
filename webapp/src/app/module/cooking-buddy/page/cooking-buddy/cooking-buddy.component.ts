import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {KnownRecipeSerializationGroup} from "../../../../model/known-recipe.serialization-group";
import {Subscription} from "rxjs";
import { ConfirmRecipeQuantityDialog } from "../../../home/dialog/confirm-recipe-quantity/confirm-recipe-quantity.dialog";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './cooking-buddy.component.html',
    styleUrls: ['./cooking-buddy.component.scss'],
    standalone: false
})
export class CookingBuddyComponent implements OnInit, OnDestroy {

  cookingBuddy: CookingBuddy|null = null;
  dialog = 'Hello! ^-^';
  page: number = 0;
  results: FilterResultsSerializationGroup<KnownRecipeSerializationGroup>;
  search: string = '';
  cookingBuddyAjax = Subscription.EMPTY;
  knownRecipesAjax = Subscription.EMPTY;
  location: number = 0;
  user: MyAccountSerializationGroup;

  constructor(
    private api: ApiService, private matDialog: MatDialog,
    private userData: UserDataService
  ) {
    this.user = userData.user.getValue();
  }

  ngOnInit() {
    this.cookingBuddyAjax = this.api.get<CookingBuddy>('/cookingBuddy').subscribe({
      next: r => this.cookingBuddy = r.data
    });

    this.doSearch();
  }

  ngOnDestroy(): void {
    this.cookingBuddyAjax.unsubscribe();
    this.knownRecipesAjax.unsubscribe();
  }

  doSwitchLocation(location: number)
  {
    this.dialog = 'Okay ^-^ Checking ' + this.describeLocation(location) + '...';
    this.location = location;
    this.page = 0;
    this.doSearch();
  }

  doChangePage()
  {


    this.doSearch();
  }

  doChangeFilters()
  {
    this.page = 0;
    this.doSearch();
  }

  doSearch()
  {
    if(!this.knownRecipesAjax.closed)
      return;

    this.results = null;

    this.search = this.search.trim();

    let data: any = {
      page: this.page,
      location: this.location,
      filter: {}
    };

    if(this.search)
      data.filter.name = this.search;

    this.knownRecipesAjax = this.api.get<CookingBuddyResponse>('/cookingBuddy/knownRecipes', data).subscribe(
      r => {
        this.results = r.data.results;
        this.page = r.data.results.page;
        this.location = r.data.location;

        this.dialog = 'Hello! ^-^ Let\'s see what\'s ' + this.describeLocation(r.data.location) + '!';
      }
    );
  }

  describeLocation(l: number)
  {
    if(l == 0)
      return 'in your house';
    else if(l == 1)
      return 'in your basement';
    else if(l == 2)
      return 'on your fireplace mantle';

    return '';
  }

  doPrepare(recipe: KnownRecipeSerializationGroup)
  {
    ConfirmRecipeQuantityDialog.open(this.matDialog, this.location, recipe)
      .afterClosed()
      .subscribe(r => {
        if(r as string)
        {
          this.dialog = r;
          this.doSearch();
        }
      })
  }

}

interface CookingBuddyResponse
{
  results: FilterResultsSerializationGroup<KnownRecipeSerializationGroup>;
  location: number;
}

interface CookingBuddy
{
  appearance: string;
  name: string;
}
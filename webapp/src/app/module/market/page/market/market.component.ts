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
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {MarketItemSerializationGroup} from "../../../../model/market/market-item.serialization-group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {AreYouSureDialog} from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import {ItemDetailsDialog} from "../../../../dialog/item-details/item-details.dialog";
import {ActivatedRoute, ParamMap, Router} from "@angular/router";
import {
  CreateItemSearchModel, CreateItemSearchModelFromQueryObject,
  CreateRequestDtoFromItemSearchModel,
  ItemSearchModel
} from "../../../../model/search/item-search.model";
import {QueryStringService} from "../../../../service/query-string.service";
import { MatDialog } from "@angular/material/dialog";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    templateUrl: './market.component.html',
    styleUrls: ['./market.component.scss'],
    standalone: false
})
@HasSounds([ 'chaching' ])
export class MarketComponent implements OnInit {
  pageMeta = { title: 'Market' };

  page: number = 0;
  results: FilterResultsSerializationGroup<MarketItemSerializationGroup>|null = null;
  userSubscription = Subscription.EMPTY;
  marketSearchAjax = Subscription.EMPTY;
  user;
  search: ItemSearchModel = CreateItemSearchModel();
  buying = false;

  constructor(
    private api: ApiService, private userData: UserDataService, private matDialog: MatDialog,
    private activatedRoute: ActivatedRoute, private router: Router, private sounds: SoundsService
  ) {
  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe(u => { this.user = u; });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        this.search = ('filter' in params)
          ? CreateItemSearchModelFromQueryObject(params.filter)
          : CreateItemSearchModel();

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.runSearch();
      }
    });
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.marketSearchAjax.unsubscribe();
  }

  static IT_WILL_COST_YOU = [
    'It\'ll cost you a cool $~~m~~.',
    'Yours now for $~~m~~!',
    'for $~~m~~. How about it?',
    'For $~~m~~?!?',
    'for $~~m~~, and not a moneys less!',
    'for $~~m~~; no take-backsies.',
    'That\'ll run ya\' $~~m~~.',
  ];

  doViewItem(inventory)
  {
    ItemDetailsDialog.open(this.matDialog, inventory.item.name, {
      hideMarketSearch: true,
      showMarketHistory: true,
      bonus: inventory.enchantment,
      spice: inventory.spice
    });
  }

  doBuy(item: MarketItemSerializationGroup)
  {
    if(this.buying) return;

    this.buying = true;

    const isHot = [ 'Hot Dog', 'Crazy-hot Torch', 'Spicy Peps', 'Firestone', 'Liquid-hot Magma', 'Ghost Pepper' ].indexOf(item.item.name) >= 0;

    const template = (isHot && Math.random() < 0.02)
      ? item.item.name + '? That\'s quite the... _hot_ commodity! :D\n\n...\n\n...\n\n...\n\nUh \\*ahem\\* sorry. It costs $~~m~~.'
      : MarketComponent.IT_WILL_COST_YOU[Math.floor(Math.random() * MarketComponent.IT_WILL_COST_YOU.length)]
    ;

    const description = template
      .replace('$', Math.ceil(item.minimumSellPrice * 1.02).toString())
    ;

    AreYouSureDialog.open(this.matDialog, 'Really Buy ' + item.item.name + '?', '![](https://poppyseedpets.com/assets/images/items/' + item.item.image + '.svg) ' + description)
      .afterClosed()
      .subscribe({
        next: (confirmed) => {
          if(confirmed)
          {
            const data = {
              item: item.item.id,
              bonus: item.enchantment?.id,
              spice: item.spice?.id,
              sellPrice: item.minimumSellPrice,
            };

            this.api.post('/market/buy', data).subscribe({
              next: () => {
                this.sounds.playSound('chaching');
                this.buying = false;
                this.runSearch();
              },
              error: () => {
                this.buying = false;
              }
            })
          }
          else
            this.buying = false;
        }
      })
    ;
  }

  doSearch() {
    this.page = 0;

    this.results = null;

    const queryParams = QueryStringService.convertToAngularParams({
      page: this.page,
      filter: this.search
    });

    this.router.navigate([], { queryParams: queryParams });

    // @TODO this shouldn't be needed...
    return false;
  }

  runSearch()
  {
    if(this.buying) return;

    this.marketSearchAjax.unsubscribe();

    let data: { page: number, filter: ItemSearchModel } = {
      page: this.page,
      filter: CreateRequestDtoFromItemSearchModel(this.search)
    };

    this.marketSearchAjax = this.api.get<FilterResultsSerializationGroup<MarketItemSerializationGroup>>('/market/search', data).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<MarketItemSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    });
  }
}

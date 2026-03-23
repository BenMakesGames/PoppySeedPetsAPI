import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {MuseumRoutingModule} from "./museum-routing.module";
import {MuseumComponent} from "./page/museum/museum.component";
import {MuseumDonateComponent} from "./page/museum/museum-donate/museum-donate.component";
import {MuseumUpgradeComponent} from "./page/museum/museum-upgrade/museum-upgrade.component";
import {MuseumTopDonatorsComponent} from "./page/museum/museum-top-donators/museum-top-donators.component";
import {MuseumWingComponent} from "./page/museum/museum-wing/museum-wing.component";
import { MuseumUnlockProgressComponent } from './component/museum-unlock-progress/museum-unlock-progress.component';
import { SelectIconDialog } from './dialog/select-icon/select-icon.dialog';
import {FormsModule} from "@angular/forms";
import { MarkdownModule } from "ngx-markdown";
import { GiftShopComponent } from './page/gift-shop/gift-shop.component';
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { PlayerNameComponent } from "../shared/component/player-name/player-name.component";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { ItemNameWithBonusComponent } from "../shared/component/item-name-with-bonus/item-name-with-bonus.component";
import { MilestoneProgressComponent } from "../shared/component/milestone-progress/milestone-progress.component";
import { RankHatComponent } from "./component/rank-hat/rank-hat.component";

@NgModule({
  declarations: [
    MuseumComponent,
    MuseumDonateComponent,
    MuseumUpgradeComponent,
    MuseumTopDonatorsComponent,
    MuseumWingComponent,
    MuseumUnlockProgressComponent,
    SelectIconDialog,
    GiftShopComponent,
    RankHatComponent
  ],
  imports: [
    CommonModule,
    MuseumRoutingModule,
    FormsModule,
    MarkdownModule,
    NpcDialogComponent,
    UrlPaginatorComponent,
    LoadingThrobberComponent,
    InventoryItemComponent,
    DateAndTimeComponent,
    PlayerNameComponent,
    PaginatorComponent,
    ItemNameWithBonusComponent,
    MilestoneProgressComponent,
  ]
})
export class MuseumModule { }

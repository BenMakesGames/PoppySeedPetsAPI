import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FloristRoutingModule } from "./florist-routing.module";
import { FloristComponent } from "./page/florist/florist.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { SpinningD6Component } from "./component/spinning-d6/spinning-d6.component";
import { RecyclingPointsComponent } from "../shared/component/recycling-points/recycling-points.component";
import { RecyclingGameComponent } from "./page/recycling-game/recycling-game.component";


@NgModule({
  declarations: [
    FloristComponent,
    RecyclingGameComponent,
    SpinningD6Component,
  ],
  imports: [
    CommonModule,
    FloristRoutingModule,
    NpcDialogComponent,
    InventoryItemComponent,
    MoneysComponent,
    LoadingThrobberComponent,
    RecyclingPointsComponent,
  ]
})
export class FloristModule { }

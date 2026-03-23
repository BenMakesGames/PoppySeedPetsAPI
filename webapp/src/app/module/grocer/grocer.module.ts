import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GrocerRoutingModule } from "./grocer-routing.module";
import { GrocerComponent } from "./page/grocer/grocer.component";
import { FormsModule } from "@angular/forms";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { RecyclingPointsComponent } from "../shared/component/recycling-points/recycling-points.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { HasUnlockedFeaturePipe } from "../shared/pipe/has-unlocked-feature.pipe";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";


@NgModule({
  declarations: [
    GrocerComponent,
  ],
  imports: [
    CommonModule,
    GrocerRoutingModule,
    FormsModule,
    MoneysComponent,
    RecyclingPointsComponent,
    LoadingThrobberComponent,
    NpcDialogComponent,
    HasUnlockedFeaturePipe,
    InventoryItemComponent,
  ]
})
export class GrocerModule { }

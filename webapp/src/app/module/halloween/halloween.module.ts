import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {TrickOrTreaterComponent} from "./page/trick-or-treater/trick-or-treater.component";
import {HalloweenRoutingModule} from "./halloween-routing.module";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { MilestoneProgressComponent } from "../shared/component/milestone-progress/milestone-progress.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { ErrorsComponent } from "../shared/component/errors/errors.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";

@NgModule({
  declarations: [
    TrickOrTreaterComponent,
  ],
  imports: [
    CommonModule,
    HalloweenRoutingModule,
    InventoryItemComponent,
    MilestoneProgressComponent,
    PetAppearanceComponent,
    ErrorsComponent,
    LoadingThrobberComponent,
  ],
  exports: [
    TrickOrTreaterComponent,
  ]
})
export class HalloweenModule { }

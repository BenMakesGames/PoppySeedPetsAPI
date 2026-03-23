import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BeehiveComponent } from './page/beehive/beehive.component';
import {BeehiveRoutingModule} from "./beehive-routing.module";
import { MatDialogModule } from "@angular/material/dialog";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { ProgressBarComponent } from "../shared/component/progress-bar/progress-bar.component";
import { DialogTitleWithIconsComponent } from "../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";
import {HelpLinkComponent} from "../shared/component/help-link/help-link.component";

@NgModule({
  declarations: [
    BeehiveComponent,
  ],
  imports: [
    CommonModule,
    BeehiveRoutingModule,
    MatDialogModule,
    LoadingThrobberComponent,
    InventoryItemComponent,
    NpcDialogComponent,
    PetAppearanceComponent,
    ProgressBarComponent,
    DialogTitleWithIconsComponent,
    HelpLinkComponent,
  ],
  exports: [
    BeehiveRoutingModule
  ]
})
export class BeehiveModule { }

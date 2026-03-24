import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DragonComponent } from './page/dragon/dragon.component';
import {DragonRoutingModule} from "./dragon-routing.module";
import {GiveTreasureDialog} from "./dialog/give-treasure/give-treasure.dialog";
import { MarkdownModule } from "ngx-markdown";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import {ImageComponent} from "../shared/component/image/image.component";
import {
    DialogTitleWithIconsComponent
} from "../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";


@NgModule({
  declarations: [
    DragonComponent,
    GiveTreasureDialog
  ],
    imports: [
        CommonModule,
        DragonRoutingModule,
        MarkdownModule.forChild(),
        NpcDialogComponent,
        LoadingThrobberComponent,
        InventoryItemComponent,
        PetAppearanceComponent,
        ImageComponent,
        DialogTitleWithIconsComponent,
    ]
})
export class DragonModule { }

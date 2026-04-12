/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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

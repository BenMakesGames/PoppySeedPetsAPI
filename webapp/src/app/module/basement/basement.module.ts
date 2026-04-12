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
import {BasementComponent} from "./page/basement/basement.component";
import {BasementRoutingModule} from "./basement-routing.module";
import { FormsModule } from "@angular/forms";
import { InventoryControlComponent } from "../shared/component/inventory-control/inventory-control.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { ItemSearchComponent } from "../shared/component/item-search/item-search.component";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import {HasUnlockedFeaturePipe} from "../shared/pipe/has-unlocked-feature.pipe";


@NgModule({
  declarations: [
    BasementComponent
  ],
    imports: [
        CommonModule,
        BasementRoutingModule,
        FormsModule,
        InventoryControlComponent,
        LoadingThrobberComponent,
        ItemSearchComponent,
        PaginatorComponent,
        InventoryItemComponent,
        HasUnlockedFeaturePipe,
    ]
})
export class BasementModule { }

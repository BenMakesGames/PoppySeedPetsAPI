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
import { LibraryComponent } from "./page/library/library.component";
import { InventoryControlComponent } from "../shared/component/inventory-control/inventory-control.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { ItemSearchComponent } from "../shared/component/item-search/item-search.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { NgForOf, NgIf } from "@angular/common";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { LibraryRoutingModule } from "./library-routing.module";
import {HasUnlockedFeaturePipe} from "../shared/pipe/has-unlocked-feature.pipe";
import { SvgIconComponent } from "../shared/component/svg-icon/svg-icon.component";

@NgModule({
  declarations: [
    LibraryComponent
  ],
  imports: [
    InventoryControlComponent,
    InventoryItemComponent,
    ItemSearchComponent,
    LoadingThrobberComponent,
    NgForOf,
    NgIf,
    UrlPaginatorComponent,
    LibraryRoutingModule,
    HasUnlockedFeaturePipe,
    SvgIconComponent,
  ]
})
export class LibraryModule { }

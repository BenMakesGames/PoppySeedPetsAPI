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

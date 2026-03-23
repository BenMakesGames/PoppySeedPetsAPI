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

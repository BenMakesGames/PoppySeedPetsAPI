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
import { FireplaceComponent } from './page/fireplace/fireplace.component';
import {FireplaceRoutingModule} from "./fireplace-routing.module";
import {FeedFireplaceDialog} from "./dialog/feed-fireplace/feed-fireplace.dialog";
import {FuelRatingPipe} from "./pipe/fuel-rating.pipe";
import {FeedWhelpDialog} from "./dialog/feed-whelp/feed-whelp.dialog";
import {DescribeDragonFoodPipe} from "./pipe/describe-dragon-food.pipe";
import { CustomizeStockingDialog } from './dialog/customize-stocking/customize-stocking.dialog';
import { MatDialogModule } from "@angular/material/dialog";
import {InventoryControlComponent} from "../shared/component/inventory-control/inventory-control.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { ImageComponent } from "../shared/component/image/image.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { ProgressBarComponent } from "../shared/component/progress-bar/progress-bar.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { DescribeAgePipe } from "../shared/pipe/describe-age.pipe";
import { ChooseTwoColorsComponent } from "../shared/component/choose-two-colors/choose-two-colors.component";
import { FireComponent } from "./components/fire/fire.component";
import {
    DialogTitleWithIconsComponent
} from "../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";

@NgModule({
  declarations: [
    FireplaceComponent,
    FeedFireplaceDialog,
    FuelRatingPipe,
    FeedWhelpDialog,
    DescribeDragonFoodPipe,
    CustomizeStockingDialog,
    FireComponent,
  ],
    imports: [
        CommonModule,
        FireplaceRoutingModule,
        MatDialogModule,
        InventoryControlComponent,
        InventoryItemComponent,
        ImageComponent,
        LoadingThrobberComponent,
        ProgressBarComponent,
        PetAppearanceComponent,
        DescribeAgePipe,
        ChooseTwoColorsComponent,
        DialogTitleWithIconsComponent,
    ],
  exports: [
    FuelRatingPipe
  ]
})
export class FireplaceModule { }

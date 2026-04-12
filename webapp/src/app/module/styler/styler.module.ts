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
import { StylerComponent } from './page/styler/styler.component';
import { StylerRoutingModule } from "./styler-routing.module";
import { ShareComponent } from './page/share/share.component';
import { ManageComponent } from './page/manage/manage.component';
import { SaveAsDialog } from "./dialog/save-as/save-as.dialog";
import { EditThemeDialog } from "./dialog/edit-theme/edit-theme.dialog";
import { FormsModule } from "@angular/forms";
import { ThemePreviewComponent } from './component/theme-preview/theme-preview.component';
import { BuiltInComponent } from './page/built-in/built-in.component';
import { ThemeGridComponent } from './component/theme-grid/theme-grid.component';
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { PetComponent } from "../shared/component/pet/pet.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { ColorPickerDirective } from "ngx-color-picker";


@NgModule({
  declarations: [
    StylerComponent,
    ShareComponent,
    ManageComponent,
    SaveAsDialog,
    EditThemeDialog,
    ThemePreviewComponent,
    BuiltInComponent,
    ThemeGridComponent,
  ],
  imports: [
    CommonModule,
    StylerRoutingModule,
    FormsModule,
    UrlPaginatorComponent,
    NpcDialogComponent,
    InventoryItemComponent,
    PetComponent,
    LoadingThrobberComponent,
    ColorPickerDirective
  ]
})
export class StylerModule { }

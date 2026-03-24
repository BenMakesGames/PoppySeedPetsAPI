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

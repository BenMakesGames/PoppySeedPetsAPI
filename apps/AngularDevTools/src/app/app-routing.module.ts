import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {NewItemComponent} from "./make-a-tool/page/new-item/new-item.component";
import {NewHatComponent} from "./make-a-hat/page/new-hat/new-hat.component";
import {NewSpeciesComponent} from "./make-a-species/page/new-species/new-species.component";
import {VnEditorComponent} from "./vn-editor/page/vn-editor.component";
import {AdventureEditorComponent} from "./adventure-editor/page/adventure-editor/adventure-editor.component";

const routes: Routes = [
  { path: '', redirectTo: '/adventureEditor', pathMatch: 'full' },
  { path: 'newTool', component: NewItemComponent },
  { path: 'newHat', component: NewHatComponent },
  { path: 'newSpecies', component: NewSpeciesComponent },
  { path: 'vnEditor', component: VnEditorComponent },
  { path: 'adventureEditor', component: AdventureEditorComponent },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

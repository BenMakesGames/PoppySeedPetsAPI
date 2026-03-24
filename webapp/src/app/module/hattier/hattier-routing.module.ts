import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { SelectionComponent } from "./page/selection/selection.component";
import { DressingRoomComponent } from "./page/dressing-room/dressing-room.component";
import { CollectionComponent } from "./page/collection/collection.component";
import { IllusionistComponent } from "./page/illusionist/illusionist.component";

const routes: Routes = [
  { path: '', component: SelectionComponent },
  { path: 'collection', component: CollectionComponent },
  { path: 'illusionist', component: IllusionistComponent },
  { path: ':petId', component: DressingRoomComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class HattierRoutingModule { }

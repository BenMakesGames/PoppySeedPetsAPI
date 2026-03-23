import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {PlazaComponent} from "./page/plaza/plaza.component";
import { EventCalendarComponent } from "./page/event-calendar/event-calendar.component";

const routes: Routes = [
  { path: '', component: PlazaComponent },
  { path: 'calendar', component: EventCalendarComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PlazaRoutingModule { }

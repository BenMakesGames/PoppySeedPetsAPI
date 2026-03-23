import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {BookstoreComponent} from "./page/bookstore/bookstore.component";

const routes: Routes = [
  { path: '', component: BookstoreComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BookstoreRoutingModule { }

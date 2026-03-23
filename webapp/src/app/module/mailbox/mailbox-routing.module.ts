import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {MailboxComponent} from "./page/mailbox/mailbox.component";

const routes: Routes = [
  { path: '', component: MailboxComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MailboxRoutingModule { }

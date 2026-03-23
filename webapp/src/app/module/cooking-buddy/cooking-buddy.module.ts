import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CookingBuddyComponent } from "./page/cooking-buddy/cooking-buddy.component";
import { CookingBuddyRoutingModule } from "./cooking-buddy-routing.module";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { MarkdownComponent } from "ngx-markdown";
import { FormsModule } from "@angular/forms";
import { HasUnlockedFeaturePipe } from "../shared/pipe/has-unlocked-feature.pipe";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";

@NgModule({
  declarations: [
    CookingBuddyComponent,
  ],
  imports: [
    CommonModule,
    CookingBuddyRoutingModule,
    NpcDialogComponent,
    LoadingThrobberComponent,
    MarkdownComponent,
    FormsModule,
    HasUnlockedFeaturePipe,
    PaginatorComponent,
  ]
})
export class CookingBuddyModule { }

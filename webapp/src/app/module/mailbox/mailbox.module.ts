import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MailboxComponent } from './page/mailbox/mailbox.component';
import {MailboxRoutingModule} from "./mailbox-routing.module";
import {MarkdownModule} from "ngx-markdown";
import {LetterDialog} from "./dialog/letter/letter.dialog";
import { DateOnlyComponent } from "../shared/component/date-only/date-only.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import {EnvelopeStylePipe} from "./pipe/envelope-style.pipe";

@NgModule({
  declarations: [
    MailboxComponent,
    LetterDialog
  ],
    imports: [
        CommonModule,
        MailboxRoutingModule,
        MarkdownModule,
        DateOnlyComponent,
        LoadingThrobberComponent,
        EnvelopeStylePipe,
    ]
})
export class MailboxModule { }

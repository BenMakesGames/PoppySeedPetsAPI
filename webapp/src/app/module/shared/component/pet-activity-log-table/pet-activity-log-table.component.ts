import {Component, Input} from '@angular/core';
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import { MarkdownModule } from "ngx-markdown";
import { PetChangesComponent } from "../pet-changes/pet-changes.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-pet-activity-log-table',
    templateUrl: './pet-activity-log-table.component.html',
    imports: [
        MarkdownModule,
        PetChangesComponent,
        CommonModule
    ],
    styleUrls: ['./pet-activity-log-table.component.scss']
})
export class PetActivityLogTableComponent {

  @Input() logs: PetActivitySerializationGroup[];

}

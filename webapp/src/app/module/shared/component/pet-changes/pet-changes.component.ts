import {Component, Input} from '@angular/core';
import {PetChangesSerializationGroup} from "../../../../model/pet-activity-logs/pet-changes.serialization-group";
import { VagueChangeComponent } from "../vague-change/vague-change.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-pet-changes',
    templateUrl: './pet-changes.component.html',
    imports: [
        VagueChangeComponent,
        CommonModule
    ],
    styleUrls: ['./pet-changes.component.scss']
})
export class PetChangesComponent {

  @Input() changes: PetChangesSerializationGroup;

  constructor() { }

}

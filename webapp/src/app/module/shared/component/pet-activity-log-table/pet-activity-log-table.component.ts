/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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

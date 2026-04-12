/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, OnInit} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import { StatBarComponent } from "../stat-bar/stat-bar.component";
import { PetSkillModifiersComponent } from "../pet-skill-modifiers/pet-skill-modifiers.component";
import { CommonModule, TitleCasePipe } from "@angular/common";

@Component({
    selector: '[appSkillTableRow]',
    templateUrl: './skill-table-row.component.html',
    imports: [
        StatBarComponent,
        PetSkillModifiersComponent,
        TitleCasePipe,
        CommonModule
    ],
    styleUrls: ['./skill-table-row.component.scss']
})
export class SkillTableRowComponent implements OnInit {

  @Input() compact: boolean = false;
  @Input() skill: TotalPetSkillsSerializationGroup;
  @Input() name: string;
  @Input() index: number;

  constructor() { }

  ngOnInit(): void {
  }

}

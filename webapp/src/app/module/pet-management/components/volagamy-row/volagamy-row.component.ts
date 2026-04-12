/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { FormsModule } from "@angular/forms";

@Component({
  selector: 'app-volagamy-row',
  templateUrl: './volagamy-row.component.html',
  styleUrls: ['./volagamy-row.component.scss'],
  imports: [
    FormsModule
  ],
})
export class VolagamyRowComponent {

  @Input() pet: MyPetSerializationGroup;

  togglingFertility = false;

  constructor(private api: ApiService) { }

  doToggleFertility()
  {
    if(this.togglingFertility) return;

    this.togglingFertility = true;

    this.api.patch('/pet/' + this.pet.id + '/setFertility', { fertility: !this.pet.isFertile }).subscribe({
      next: () => {
        this.pet.isFertile = !this.pet.isFertile;
        this.togglingFertility = false;
      },
      error: () => {
        this.togglingFertility = false;
      }
    });
  }

}

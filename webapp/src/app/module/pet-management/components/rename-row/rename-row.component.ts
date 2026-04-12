/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { RenamePetDialog } from "../../dialogs/rename-pet/rename-pet.dialog";
import { MatDialog } from "@angular/material/dialog";
import { FormsModule } from "@angular/forms";

@Component({
  selector: 'app-rename-row',
  templateUrl: './rename-row.component.html',
  styleUrls: ['./rename-row.component.scss'],
  imports: [
    FormsModule
  ],
})
export class RenameRowComponent {

  @Input() pet: MyPetSerializationGroup;
  @Output() onUpdate = new EventEmitter<MyPetSerializationGroup>();

  constructor(private matDialog: MatDialog) { }

  doRename()
  {
    RenamePetDialog.open(this.matDialog, this.pet).afterClosed().subscribe({
      next: (r) => {
        if(r && r.newPet)
        {
          this.onUpdate.emit(r.newPet);
        }
      }
    });
  }

}

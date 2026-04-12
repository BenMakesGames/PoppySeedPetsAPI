/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject} from '@angular/core';
import {ParkEventSerializationGroup} from "../../../../model/park/park-event.serialization-group";
import {Router} from "@angular/router";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './park-event-details.dialog.html',
    styleUrls: ['./park-event-details.dialog.scss'],
    standalone: false
})
export class ParkEventDetailsDialog {

  event: ParkEventSerializationGroup;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any, private router: Router,
    private dialog: MatDialogRef<ParkEventSerializationGroup>
  ) {
    this.event = data.event;
  }

  public doShowPet(pet)
  {
    this.router.navigate([ '/poppyopedia/pet/' + pet.id ]);
    this.dialog.close();
  }

  public static open(matDialog: MatDialog, event: ParkEventSerializationGroup)
  {
    matDialog.open(ParkEventDetailsDialog, {
      data: {
        event: event
      }
    });
  }
}

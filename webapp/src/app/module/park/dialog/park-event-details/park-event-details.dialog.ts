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

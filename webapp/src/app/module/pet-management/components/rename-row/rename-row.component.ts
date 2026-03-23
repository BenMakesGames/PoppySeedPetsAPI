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

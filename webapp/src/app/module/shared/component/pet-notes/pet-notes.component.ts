import { Component, EventEmitter, model, OnChanges, Output } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../service/api.service";
import {GuessFavoriteFlavorDialog} from "../../../home/dialog/guess-favorite-flavor/guess-favorite-flavor.dialog";
import { MatDialog } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { DateAndTimeComponent } from "../date-and-time/date-and-time.component";
import { FormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";
import { ColorNamePipe } from "../../pipe/color-name.pipe";
import { RenameRowComponent } from "../../../pet-management/components/rename-row/rename-row.component";
import { DialogResponseModel } from "../../../../model/dialog-response.model";

@Component({
    selector: 'app-pet-notes',
    templateUrl: './pet-notes.component.html',
  imports: [
    DateAndTimeComponent,
    FormsModule,
    CommonModule,
    RouterModule,
    ColorNamePipe,
    RenameRowComponent
  ],
    styleUrls: ['./pet-notes.component.scss']
})
export class PetNotesComponent implements OnChanges {
  pet = model.required<MyPetSerializationGroup>();
  @Output('loading') loadingEmitter = new EventEmitter<boolean>();
  @Output() done = new EventEmitter();

  loading = false;
  petNote: string;

  constructor(
    private api: ApiService,
    private matDialog: MatDialog
  ) {
  }

  ngOnChanges()
  {
    this.petNote = this.pet().note;
  }

  doGuessFavoriteFlavor()
  {
    GuessFavoriteFlavorDialog.open(this.matDialog, this.pet()).afterClosed().subscribe({
      next: pet => {
        if(pet) {
          this.pet.set(pet);
        }
      }
    });
  }

  doClose()
  {
    this.done.emit();
  }

  doRename(newPet)
  {
    this.pet.set(newPet);
  }

  doSaveNote()
  {
    if(this.loading) return;

    this.loading = true;
    this.loadingEmitter.emit(true);

    this.api.post('/pet/' + this.pet().id + '/updateNote', { note: this.petNote }).subscribe({
      next: () => {
        this.loading = false;
        this.loadingEmitter.emit(false);
        this.pet.set({
          ...this.pet(),
          note: this.petNote
        });
      },
      error: () => {
        this.loading = false;
      }
    });
  }

}

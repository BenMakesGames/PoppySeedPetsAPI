import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { RenamePetDialog } from "../../dialogs/rename-pet/rename-pet.dialog";
import { MatDialog } from "@angular/material/dialog";
import { FormsModule } from "@angular/forms";
import { STATUS_EFFECTS } from "../../../../model/status-effects";
import { VolagamyRowComponent } from "../volagamy-row/volagamy-row.component";
import { MarkdownComponent } from "ngx-markdown";
import { RouterLink } from "@angular/router";
import { TitleCasePipe } from "@angular/common";

@Component({
  selector: 'app-pet-status-effects-tab',
  templateUrl: './pet-status-effects-tab.component.html',
  styleUrls: ['./pet-status-effects-tab.component.scss'],
  imports: [
    FormsModule,
    VolagamyRowComponent,
    MarkdownComponent,
    RouterLink,
    TitleCasePipe
  ],
})
export class PetStatusEffectsTabComponent implements OnChanges {

  @Input() pet: MyPetSerializationGroup;
  @Output() onUpdate = new EventEmitter<MyPetSerializationGroup>();
  @Output() onNavigate = new EventEmitter<void>();

  hasStatusEffect = false;
  hasVolagamy = false;
  hasPrehensileTongue = false;

  protected readonly STATUS_EFFECTS = STATUS_EFFECTS;

  constructor(private matDialog: MatDialog) { }

  ngOnChanges(changes: SimpleChanges) {
    this.hasVolagamy = this.pet.merits.some(m => m.name === 'Volagamy');
    this.hasPrehensileTongue = this.pet.merits.some(m => m.name === 'Prehensile Tongue');

    this.hasStatusEffect =
      this.pet.statuses.length > 0 ||
      !!this.pet.pregnancy ||
      this.pet.poisonLevel !== 'none' ||
      this.pet.alcoholLevel !== 'none' ||
      this.pet.hallucinogenLevel !== 'none' ||
      !!this.pet.craving
    ;
  }

  doClickLink()
  {
    this.onNavigate.emit();
  }

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

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

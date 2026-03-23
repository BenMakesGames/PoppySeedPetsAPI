import { Component, Input, OnDestroy } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { MeritEncyclopediaSerializationGroup } from "../../../../model/encyclopedia/merit-encyclopedia.serialization-group";
import { Subscription } from "rxjs";
import { ApiService } from "../../service/api.service";
import { CommonModule } from "@angular/common";
import { MarkdownComponent } from "ngx-markdown";

@Component({
    imports: [
        CommonModule,
        MarkdownComponent
    ],
    selector: 'app-pet-merits',
    templateUrl: './pet-merits.component.html',
    styleUrls: ['./pet-merits.component.scss']
})
export class PetMeritsComponent implements OnDestroy {
  @Input() pet: MyPetSerializationGroup;

  merits: MeritEncyclopediaSerializationGroup[];
  visibleMerit;
  meritAjax = Subscription.EMPTY;

  constructor(private api: ApiService) {
  }

  ngOnDestroy() {
    this.meritAjax.unsubscribe();
  }

  doSelectMerit(meritName: string)
  {
    if(!this.merits)
    {
      this.meritAjax = this.api.get<MeritEncyclopediaSerializationGroup[]>('/pet/' + this.pet.id + '/merits').subscribe({
        next: r => {
          this.merits = r.data.map(m => {
            return {
              name: m.name,
              description: m.description.replace(/%pet\.name%/g, this.pet.name)
            };
          });
          this.visibleMerit = this.merits.find(m => m.name == meritName);
        }
      })
    }
    else
    {
      if(meritName === this.visibleMerit?.name)
        this.visibleMerit = null;
      else
        this.visibleMerit = this.merits.find(m => m.name == meritName);
    }
  }
}

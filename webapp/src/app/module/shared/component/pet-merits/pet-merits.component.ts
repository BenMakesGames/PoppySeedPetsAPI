/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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

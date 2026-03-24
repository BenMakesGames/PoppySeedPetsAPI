import {Component, EventEmitter, Input, OnChanges, OnDestroy, Output, SimpleChanges} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {MeritSerializationGroup} from "../../../../model/my-pet/merit.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ApiService} from "../../../shared/service/api.service";
import {PetSkillsEnum} from "../../../../model/pet-skills.enum";
import {Subscription} from "rxjs";
import { MarkdownComponent } from "ngx-markdown";
import { FormsModule } from "@angular/forms";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";

@Component({
    selector: 'app-pet-pick-affection-reward',
    templateUrl: './pet-pick-affection-reward.component.html',
    styleUrls: ['./pet-pick-affection-reward.component.scss'],
    imports: [
        MarkdownComponent,
        FormsModule,
        CommonModule,
        LoadingThrobberComponent,
    ]
})
export class PetPickAffectionRewardComponent implements OnChanges, OnDestroy {

  @Input() pet: MyPetSerializationGroup;
  @Output() selectAffectionReward = new EventEmitter<{ type: AffectionRewardTypeEnum, value: string }>();

  skillList = Object.keys(PetSkillsEnum).map(k => PetSkillsEnum[k]);

  selectedSkill = '';

  availableMeritsAjax: Subscription;

  public availableMerits: MeritSerializationGroup[];

  constructor(private api: ApiService) { }

  doSelectMerit(merit: string)
  {
    this.selectAffectionReward.emit({ type: AffectionRewardTypeEnum.MERIT, value: merit });
  }

  doSelectSkill()
  {
    if(!this.selectedSkill) return;

    this.selectAffectionReward.emit({ type: AffectionRewardTypeEnum.SKILL, value: this.selectedSkill });
  }

  ngOnChanges(changes: SimpleChanges): void
  {
    if(changes.pet)
    {
      this.availableMeritsAjax = this.api.get<MeritSerializationGroup[]>('/pet/' + this.pet.id + '/availableMerits').subscribe({
        next: (r: ApiResponseModel<MeritSerializationGroup[]>) => {
          this.availableMerits = r.data
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((m: MeritSerializationGroup) => {
              return {
                name: m.name,
                description: m.description.replace(/%pet\.name%/g, this.pet.name)
              }
            })
          ;
        }
      });
    }
  }

  ngOnDestroy(): void {
    this.availableMeritsAjax.unsubscribe();
  }

}

export enum AffectionRewardTypeEnum
{
  MERIT,
  SKILL
}

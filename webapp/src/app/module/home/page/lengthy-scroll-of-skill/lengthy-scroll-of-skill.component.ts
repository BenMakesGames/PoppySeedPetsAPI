import { Component, OnInit } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {PetSkillsEnum} from "../../../../model/pet-skills.enum";

@Component({
    templateUrl: './lengthy-scroll-of-skill.component.html',
    styleUrls: ['./lengthy-scroll-of-skill.component.scss'],
    standalone: false
})
export class LengthyScrollOfSkillComponent implements OnInit {

  scrollId: number;
  state = 'findPet';
  loading = false;
  pet: MyPetSerializationGroup;
  skills: string[];
  extol = '';

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute,
  )
  {

  }

  ngOnInit()
  {
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doSelectPet(pet: MyPetSerializationGroup)
  {
    this.pet = pet;

    if(this.pet)
    {
      this.state = 'extolSkill';
      this.extol = '';

      this.skills = Object.keys(PetSkillsEnum).map(k => PetSkillsEnum[k]).filter(skill => {
        return this.pet.skills[skill].base >= 10 && this.pet.skills[skill].base <= 20;
      });
    }
    else
      this.state = 'findPet';
  }

  doExtol()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/item/lengthySkill/' + this.scrollId + '/read', { pet: this.pet.id, skill: this.extol })
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.loading = false;
        }
      })
    ;
  }
}

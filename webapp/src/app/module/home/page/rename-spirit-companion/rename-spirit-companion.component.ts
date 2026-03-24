import { Component, OnInit } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: './rename-spirit-companion.component.html',
    styleUrls: ['./rename-spirit-companion.component.scss'],
    standalone: false
})
export class RenameSpiritCompanionComponent implements OnInit {

  scrollId: number;
  state = 'findPet';
  pet: MyPetSerializationGroup;
  newName = '';
  renaming = false;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  disableCondition = p => !p.spiritCompanion;

  doShowRename(pet)
  {
    if(!pet) return;
    if(!pet.spiritCompanion) return;

    this.pet = pet;
    this.state = 'renamePet';
  }

  doCancelRename()
  {
    if(this.renaming) return;

    this.state = 'findPet';
  }

  doRename()
  {
    if(this.renaming) return;

    this.newName = this.newName.trim();

    if(this.newName === this.pet.name)
      return;

    this.renaming = true;

    const data = {
      pet: this.pet.id,
      name: this.newName
    };

    this.api.patch('/item/renamingScroll/' + this.scrollId + '/readToSpiritCompanion', data)
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.renaming = false;
        }
      })
  }

}

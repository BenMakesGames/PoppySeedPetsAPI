import { Component, OnInit } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: './renaming-scroll.component.html',
    styleUrls: ['./renaming-scroll.component.scss'],
    standalone: false
})
export class RenamingScrollComponent implements OnInit {

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

  doShowRename(pet)
  {
    if(pet === null) return;

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

    this.api.patch('/item/renamingScroll/' + this.scrollId + '/read', data)
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

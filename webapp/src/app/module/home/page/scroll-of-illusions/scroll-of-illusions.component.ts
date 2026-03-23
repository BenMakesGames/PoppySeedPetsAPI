import {Component, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ActivatedRoute, Router} from "@angular/router";
import {MessagesService} from "../../../../service/messages.service";

@Component({
    templateUrl: './scroll-of-illusions.component.html',
    styleUrls: ['./scroll-of-illusions.component.scss'],
    standalone: false
})
export class ScrollOfIllusionsComponent implements OnInit {

  scrollId: number;
  state = 'findPet';
  pet: MyPetSerializationGroup;
  forget = '';

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute,
    private messages: MessagesService
  )
  {

  }

  ngOnInit()
  {
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doSelectPet(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    if(!pet.tool)
    {
      this.messages.addGenericMessage(pet.name + ' has no tool to enchant!');
      return;
    }

    this.pet = pet;
    this.state = 'chooseAppearance';
  }

  enchanting = false;

  doEnchant(item: number|null)
  {
    if(this.enchanting)
      return;

    this.enchanting = true;

    const data = {
      petId: this.pet.id,
      illusionId: item,
    }

    this.api.post<any>('/scrollOfIllusions/' + this.scrollId + '/read', data).subscribe({
      next: _ => {
        this.router.navigateByUrl('/home');
      },
      error: () => {
        this.enchanting = false;
      }
    })
  }
}

import {Component, OnDestroy, OnInit} from '@angular/core';
import {MyDragonSerializationGroup} from "../../../../model/my-dragon-serialization.group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {GiveTreasureDialog} from "../../dialog/give-treasure/give-treasure.dialog";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { SelectPetDialog } from "../../../../dialog/select-pet/select-pet.dialog";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { InteractWithAwayPetDialog } from "../../../pet-helpers/dialog/interact-with-away-pet/interact-with-away-pet-dialog.component";
import { MatDialog } from "@angular/material/dialog";
import { ChoiceModel } from "../../../../dialog/choose-one/choose-one.dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    selector: 'app-dragon',
    templateUrl: './dragon.component.html',
    styleUrls: ['./dragon.component.scss'],
    standalone: false
})
export class DragonComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Dragon Den' };

  dialog: string;
  dragon: MyDragonSerializationGroup;
  dragonSubscription = Subscription.EMPTY;
  user: MyAccountSerializationGroup;
  treasureHoardOffset = 0;
  hoardDescription = '';

  assignHelperAjax = Subscription.EMPTY;
  dismissingHostageAjax = Subscription.EMPTY;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private api: ApiService, private matDialog: MatDialog,
    private userDataService: UserDataService
  ) {
  }

  private updateDragon(dragon: MyDragonSerializationGroup)
  {
    this.dragon = dragon;
    this.treasureHoardOffset = Math.max(0, 300 - this.dragon.treasureCount / 3);

    if(this.dragon.treasureCount >= 5000)
      this.hoardDescription = 'A mind-boggling mega-hoard of';
    else if(this.dragon.treasureCount >= 4000)
      this.hoardDescription = 'A wondrously colossal hoard of';
    else if(this.dragon.treasureCount >= 3000)
      this.hoardDescription = 'An astronomical hoard of';
    else if(this.dragon.treasureCount >= 2000)
      this.hoardDescription = 'A gargantuan hoard of';
    else if(this.dragon.treasureCount >= 1500)
      this.hoardDescription = 'A monstrous hoard of';
    else if(this.dragon.treasureCount >= 1000)
      this.hoardDescription = 'A humongous hoard of';
    else if(this.dragon.treasureCount >= 800)
      this.hoardDescription = 'An enormous hoard of';
    else if(this.dragon.treasureCount >= 600)
      this.hoardDescription = 'A mighty hoard of';
    else if(this.dragon.treasureCount >= 400)
      this.hoardDescription = 'A large hoard of';
    else if(this.dragon.treasureCount >= 300)
      this.hoardDescription = 'A hoard of';
    else if(this.dragon.treasureCount >= 200)
      this.hoardDescription = 'A small hoard of';
    else if(this.dragon.treasureCount >= 100)
      this.hoardDescription = 'A small pile of';
    else
      this.hoardDescription = '';
  }

  ngOnInit(): void {
    this.dragonSubscription = this.api.get<MyDragonSerializationGroup>('/dragon').subscribe({
      next: r => {
        this.updateDragon(r.data);
        this.dialog = Math.randomFromList(this.dragon.greetings);
      }
    });

    this.user = this.userDataService.user.getValue();
  }

  ngOnDestroy()
  {
    this.dragonSubscription.unsubscribe();
  }

  doGiveTreasure()
  {
    if(!this.assignHelperAjax.closed)
      return;

    GiveTreasureDialog.open(this.matDialog).afterClosed().subscribe({
      next: r => {
        if(r && r.dragon) {
          this.updateDragon(r.dragon);
          this.dialog = Math.randomFromList(this.dragon.thanks);
        }
      }
    });
  }

  doKickOutHostage()
  {
    if(!this.dismissingHostageAjax.closed)
      return;

    this.dismissingHostageAjax = this.api.post<MyDragonSerializationGroup>('/dragon/dismissHostage').subscribe({
      next: r => {
        this.updateDragon(r.data);
        this.dialog = 'Thank you. What a troublesome creature. And promoting such unfounded stereotypes about dragons...';
        window.scroll(0, 0);
      }
    });
  }

  doAssignHelper()
  {
    if(!this.assignHelperAjax.closed)
      return;

    SelectPetDialog.open(this.matDialog)
      .afterClosed()
      .subscribe(pet => {
        if(pet)
        {
          this.assignHelperAjax = this.api.post('/dragon/assignHelper/' + pet.id).subscribe({
            next: (r: ApiResponseModel<MyDragonSerializationGroup>) => {
              this.updateDragon(r.data);
            }
          });
        }
      })
    ;
  }

  doRecallHelper()
  {
    if(!this.assignHelperAjax.closed)
      return;

    this.assignHelperAjax = this.api.post('/pet/' + this.dragon.helper.id + '/stopHelping').subscribe({
      next: _ => {
        this.dragon.helper = null;
      }
    });
  }

  doViewHelper()
  {
    if(!this.assignHelperAjax.closed)
      return;

    const choices: ChoiceModel[] = [
      {
        label: 'Take Home',
        value: 'take-home'
      }
    ];

    InteractWithAwayPetDialog.open(this.matDialog, this.dragon.helper.id, this.dragon.helper.name, choices)
      .afterClosed()
      .subscribe({
        next: v => {
          if(v && v.value == 'take-home')
          {
            this.doRecallHelper();
          }
          else if(v && v.newPet)
          {
            this.dragon.helper.name = v.newPet.name;
          }
        }
      })
    ;
  }
}

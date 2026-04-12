/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, OnDestroy} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {DialogResponseModel} from "../../../../model/dialog-response.model";
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import {
  PetPickTalentComponent,
  PetPickTalentSelectModel
} from "../../component/pet-pick-talent/pet-pick-talent.component";
import {
  AffectionRewardTypeEnum,
  PetPickAffectionRewardComponent
} from "../../component/pet-pick-affection-reward/pet-pick-affection-reward.component";
import {
  PetPickSelfReflectionComponent,
  PetPickSelfReflectionModel
} from "../../component/pet-pick-self-reflection/pet-pick-self-reflection.component";
import {Subscription} from "rxjs";
import { WeatherService } from "../../../shared/service/weather.service";
import { ConfirmEquipOrUnequipDialog } from "../confirm-equip-or-unequip/confirm-equip-or-unequip.dialog";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { CommonModule } from "@angular/common";
import { PetNotesComponent } from "../../../shared/component/pet-notes/pet-notes.component";
import { FormsModule } from "@angular/forms";
import { PetFriendsComponent } from "../../../shared/component/pet-friends/pet-friends.component";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { PetLogsLinksComponent } from "../../../shared/component/pet-logs-links/pet-logs-links.component";
import { PetActivityLogTableComponent } from "../../../shared/component/pet-activity-log-table/pet-activity-log-table.component";
import { InventoryItemComponent } from "../../../shared/component/inventory-item/inventory-item.component";
import { PetMeritsComponent } from "../../../shared/component/pet-merits/pet-merits.component";
import { PetBadgeTableComponent } from "../../../shared/pet-badge-table/pet-badge-table.component";
import { PetLunchboxComponent } from "../../component/pet-lunchbox/pet-lunchbox.component";
import { PetSkillsAndAttributesPanelComponent } from "../../../shared/component/pet-skills-and-attributes-panel/pet-skills-and-attributes-panel.component";
import { PetStatusEffectsTabComponent } from "../../../pet-management/components/pet-status-effects-tab/pet-status-effects-tab.component";

@Component({
  templateUrl: './interact-with-pet.dialog.html',
  styleUrls: ['./interact-with-pet.dialog.scss'],
  imports: [
    CommonModule,
    PetNotesComponent,
    FormsModule,
    PetFriendsComponent,
    LoadingThrobberComponent,
    PetLogsLinksComponent,
    PetActivityLogTableComponent,
    InventoryItemComponent,
    PetMeritsComponent,
    PetBadgeTableComponent,
    PetPickAffectionRewardComponent,
    PetPickTalentComponent,
    PetPickSelfReflectionComponent,
    PetLunchboxComponent,
    PetSkillsAndAttributesPanelComponent,
    PetStatusEffectsTabComponent,
  ]
})
export class InteractWithPetDialog implements OnDestroy {

  InteractTabEnum = InteractTabEnum;

  loading = false;

  petNote = '';
  petCostume = '';

  inventory: MyInventorySerializationGroup[];
  user: MyAccountSerializationGroup;
  pet: MyPetSerializationGroup;
  canWearHats: boolean;
  tools: MyInventorySerializationGroup[];
  hats: MyInventorySerializationGroup[];
  tab: InteractTabEnum;
  viewedFriends = false;
  viewedAffectionRewards = false;
  inventoryChanged = false;

  logs: FilterResultsSerializationGroup<PetActivitySerializationGroup>;

  isHalloween = false;
  isOctober = false;

  petLogsAjax = Subscription.EMPTY;
  weatherSubscription = Subscription.EMPTY;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private dialogRef: MatDialogRef<InteractWithPetDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService,
    private weatherService: WeatherService,
    private userData: UserDataService,
    private matDialog: MatDialog,
  )
  {
    this.pet = data.pet;
    this.user = this.userData.user.getValue();

    this.canWearHats = this.pet.merits.some(m => m.name === 'Behatted');

    this.petNote = this.pet.note;
    this.petCostume = this.pet.costume;

    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: w => {
        const today = w?.find(w => new Date().toISOString().startsWith(w.date)) || null;

        this.isOctober = (new Date()).getUTCMonth() === 9; // months start counting at 0 >_>
        this.isHalloween = today?.holidays.indexOf('Halloween') >= 0;
      }
    })

    this.inventory = data.inventory;

    const hasSameGroup = (i, i2) => i2.item.name === i.item.name && (!!i2.sellPrice) === (!!i.sellPrice) && i2.enchantment?.id === i.enchantment?.id;

    this.tools = data.inventory
      .filter((i: MyInventorySerializationGroup) => i.item.tool)
      .filter((i: MyInventorySerializationGroup, index, self) => {
        return self.findIndex(i2 => hasSameGroup(i, i2)) === index;
      })
      .sort((i1, i2) => {
        if(i1.item.name.toLowerCase() < i2.item.name.toLowerCase()) return -1;
        else if(i1.item.name.toLowerCase() > i2.item.name.toLowerCase()) return 1;
        else return 0;
      })
    ;

    this.hats = data.inventory
      .filter((i: MyInventorySerializationGroup) => i.item.hat)
      .filter((i: MyInventorySerializationGroup, index, self) => {
        return self.findIndex(i2 => hasSameGroup(i, i2)) === index;
      })
      .sort((i1, i2) => {
        if(i1.item.name.toLowerCase() < i2.item.name.toLowerCase()) return -1;
        else if(i1.item.name.toLowerCase() > i2.item.name.toLowerCase()) return 1;
        else return 0;
      })
    ;

    this.doChangeTab(data.startingTab);

    this.petLogsAjax = this.api.get<FilterResultsSerializationGroup<PetActivitySerializationGroup>>('/pet/' + this.pet.id + '/logs').subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<PetActivitySerializationGroup>>) => {
        this.logs = r.data;
      }
    });
  }

  ngOnDestroy(): void {
    this.petLogsAjax.unsubscribe();
    this.weatherSubscription.unsubscribe();
  }

  doSaveCostume()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.patch('/pet/' + this.pet.id + '/costume', { costume: this.petCostume }).subscribe({
      next: () => {
        this.pet.costume = this.petCostume;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  doChangeTab(tab: InteractTabEnum)
  {
    if(this.loading) return;

    this.tab = tab;

    if(this.tab === InteractTabEnum.FRIENDS)
      this.viewedFriends = true;
    else if(this.tab === InteractTabEnum.AFFECTION_REWARDS)
      this.viewedAffectionRewards = true;
  }

  doMaybeEquip(tool: MyInventorySerializationGroup)
  {
    if(this.loading) return;

    ConfirmEquipOrUnequipDialog.openToEquip(this.matDialog, tool).afterClosed().subscribe({
      next: r => {
        if(!r)
          return;

        this.loading = true;
        this.inventoryChanged = true;

        this.api.post('/pet/' + this.pet.id + '/equip/' + tool.id).subscribe(
          r => {
            this.dialogRef.close(<DialogResponseModel>{
              updatedPet: r.data
            });
          }
        );
      }
    });
  }

  doHat(hat: MyInventorySerializationGroup)
  {
    if(this.loading) return;

    this.loading = true;
    this.inventoryChanged = true;

    this.api.post('/pet/' + this.pet.id + '/hat/' + hat.id).subscribe(
      r => {
        this.dialogRef.close(<DialogResponseModel>{
          updatedPet: r.data
        });
      }
    );
  }

  doPickTalent(selection: PetPickTalentSelectModel)
  {
    if(this.loading) return;

    this.loading = true;

    const urlPart = selection.type === 'talent' ? 'pickTalent' : 'pickExpertise';
    const data = selection.type === 'talent' ? { talent: selection.merit } : { expertise: selection.merit };

    this.api.post('/pet/' + this.pet.id + '/' + urlPart, data).subscribe({
      next: (r) => {
        this.dialogRef.close(<DialogResponseModel>{
          updatedPet: r.data
        });
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  doPickSelfReflection(choice: PetPickSelfReflectionModel)
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post<MyPetSerializationGroup>('/pet/' + this.pet.id + '/selfReflection/' + choice.route, choice.data).subscribe({
      next: (r) => {
        this.dialogRef.close(<DialogResponseModel>{
          updatedPet: r.data
        });
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  doRename(newPet)
  {
    this.dialogRef.close(<DialogResponseModel>{
      updatedPet: { ...this.pet, ...newPet }
    });
  }

  doAffectionReward(choice: { type: AffectionRewardTypeEnum, value: string })
  {
    if(this.loading) return;

    this.loading = true;

    if(choice.type === AffectionRewardTypeEnum.MERIT)
    {
      this.api.post('/pet/' + this.pet.id + '/chooseAffectionReward/merit', { merit: choice.value }).subscribe({
        next: (r) => {
          this.dialogRef.close(<DialogResponseModel>{
            updatedPet: r.data
          });
        },
        error: () => {
          this.loading = false;
        }
      });
    }
    else if(choice.type === AffectionRewardTypeEnum.SKILL)
    {
      this.api.post('/pet/' + this.pet.id + '/chooseAffectionReward/skill', { skill: choice.value }).subscribe({
        next: (r) => {
          this.dialogRef.close(<DialogResponseModel>{
            updatedPet: r.data
          });
        },
        error: () => {
          this.loading = false;
        }
      });
    }
  }

  doMaybeUnequip()
  {
    if(this.loading) return;

    ConfirmEquipOrUnequipDialog.openToUnequip(this.matDialog, this.pet.tool).afterClosed().subscribe({
      next: r => {
        if(!r)
          return;

        this.loading = true;
        this.inventoryChanged = true;

        this.api.post('/pet/' + this.pet.id + '/unequip').subscribe(
          r => {
            this.dialogRef.close(<DialogResponseModel>{
              updatedPet: r.data,
            });
          }
        );
      }
    });
  }

  doUnhat()
  {
    if(this.loading) return;

    this.loading = true;
    this.inventoryChanged = true;

    this.api.post('/pet/' + this.pet.id + '/unhat').subscribe(
      r => {
        this.dialogRef.close(<DialogResponseModel>{
          updatedPet: r.data,
        });
      }
    );
  }

  doPetPet()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post<{ pet: MyPetSerializationGroup, emoji: string }>('/pet/' + this.pet.id + '/pet').subscribe(
      r => {
        this.dialogRef.close(<DialogResponseModel>{
          updatedPet: { ...r.data.pet, emoji: r.data.emoji }
        });
      }
    );
  }

  doLoading(loading: boolean)
  {
    this.loading = loading;
  }

  doClose()
  {
    this.dialogRef.close();
  }

  doInventoryChanged()
  {
    this.inventoryChanged = true;
  }

  public static open(
    matDialog: MatDialog, pet: MyPetSerializationGroup, inventory: MyInventorySerializationGroup[],
    startingTab: InteractTabEnum
  ): MatDialogRef<InteractWithPetDialog>
  {
    return matDialog.open(InteractWithPetDialog, {
      data: {
        pet: pet,
        inventory: inventory,
        startingTab: startingTab
      }
    })
  }
}

export enum InteractTabEnum
{
  STATUS_EFFECTS,
  AFFECTION_REWARDS,
  LUNCHBOX,
  LOGS,
  FRIENDS,
  TOOL,
  HAT,
  SKILLS,
  NOTE,
  MERITS,
  BADGES,
  HALLOWEEN
}

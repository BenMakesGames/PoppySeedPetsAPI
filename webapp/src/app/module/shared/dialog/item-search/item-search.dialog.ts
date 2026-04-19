/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { ItemSearchModel } from "../../../../model/search/item-search.model";
import { HasUnlockedFeaturePipe } from "../../pipe/has-unlocked-feature.pipe";
import { InArrayPipe } from "../../pipe/in-array.pipe";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { FlavorEnum } from "../../../../model/flavor.enum";
import { PetActivityLogTagComponent } from "../../component/pet-activity-log-tag/pet-activity-log-tag.component";
import { ThemeService } from "../../service/theme.service";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { SkillNamePipe } from "../../pipe/skill-name.pipe";
import { InputYesNoBothComponent } from "../../../filters/components/input-yes-no-both/input-yes-no-both.component";

@Component({
  templateUrl: './item-search.dialog.html',
  imports: [
    CommonModule,
    FormsModule,
    HasUnlockedFeaturePipe,
    InArrayPipe,
    PetActivityLogTagComponent,
    SkillNamePipe,
    InputYesNoBothComponent
  ],
  styleUrls: ['./item-search.dialog.scss']
})
export class ItemSearchDialog implements OnInit
{
  isOctober = false;
  itemTag: { title: string, color: string }|null;

  foodFlavors: string[] = [];

  equipStats = [
    'arcana',
    'brawl',
    'crafts',
    'music',
    'nature',
    'science',
    'stealth',

    'climbing',
    'electronics',
    'umbra',
    'fishing',
    'gathering',
    'hacking',
    'magicBinding',
    'mining',
    'physics',
    'smithing',
  ];

  filter: ItemSearchModel;
  user: MyAccountSerializationGroup;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    @Inject(MAT_DIALOG_DATA) data,
    private dialogRef: MatDialogRef<ItemSearchDialog>,
    private userData: UserDataService,
    private themeService: ThemeService
  )
  {
    this.filter = data.filter;
    this.user = userData.user.getValue();
  }

  ngOnInit(): void {
    for(const flavor in FlavorEnum)
    {
      if(!Number(flavor))
        this.foodFlavors.push(flavor);
    }

    this.isOctober = (new Date()).getUTCMonth() === 9;

    this.doChange();
  }

  doSearch()
  {
    this.dialogRef.close(this.filter);
  }

  doClose()
  {
    this.dialogRef.close();
  }

  doChangeFilterArray(event)
  {
    const array = event.target.name;
    const value = event.target.value;

    let index = this.filter[array].indexOf(value);

    if(event.target.checked)
    {
      // add if not present
      if (index == -1)
        this.filter[array].push(value);
    }
    else
    {
      // remove if present
      if (index >= 0)
        this.filter[array].splice(index, 1);
    }

    this.doChange();
  }

  doClearTag()
  {
    this.filter.itemGroup = null;
    this.itemTag = null;
  }

  doChange() {
    if(this.filter.edible !== true)
      this.filter.foodFlavors = [];

    if(this.filter.equipable !== true)
      this.filter.equipStats = [];

    if(this.filter.itemGroup)
    {
      const primaryColor = this.themeService.getStyleColor('color-link-and-button');
      this.itemTag = { title: this.filter.itemGroup, color: primaryColor.replace('#', '') };
    }
    else
      this.itemTag = null;
  }

  public static open(matDialog: MatDialog, filter: ItemSearchModel): MatDialogRef<ItemSearchDialog>
  {
    return matDialog.open(ItemSearchDialog, {
      width: '6.5in',
      data: {
        filter: JSON.parse(JSON.stringify(filter))
      }
    });
  }
}

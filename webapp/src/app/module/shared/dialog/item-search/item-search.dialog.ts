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
import { ApiService } from "../../service/api.service";

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
  itemGroups: string[] = [];
  filteredItemGroups: string[] = [];
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
    private themeService: ThemeService,
    private api: ApiService
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

    this.api.get<string[]>('/encyclopedia/item-groups').subscribe({
      next: r => {
        this.itemGroups = r.data ?? [];
        this.filterItemGroups();
      }
    });
  }

  filterItemGroups() {
    const val = (this.filter.itemGroup ?? '').toLowerCase();
    this.filteredItemGroups = val
      ? this.itemGroups.filter(g => g.toLowerCase().includes(val))
      : this.itemGroups;
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

    this.filterItemGroups();
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

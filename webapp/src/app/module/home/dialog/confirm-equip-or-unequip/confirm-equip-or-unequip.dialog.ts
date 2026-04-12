/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject} from '@angular/core';
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { ToolSerializationGroup } from "../../../../model/public-profile/tool.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { CommonModule } from "@angular/common";

@Component({
    templateUrl: './confirm-equip-or-unequip.dialog.html',
    styleUrls: ['./confirm-equip-or-unequip.dialog.scss'],
    imports: [CommonModule]
})
export class ConfirmEquipOrUnequipDialog {

  unequip: ToolSerializationGroup|null;
  equip: MyInventorySerializationGroup|null;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<ConfirmEquipOrUnequipDialog>,
  ) {
    this.unequip = data.unequip;
    this.equip = data.equip;
  }

  doCancel()
  {
    this.dialogRef.close(false);
  }

  doSubmit()
  {
    this.dialogRef.close(true);
  }

  public static openToUnequip(matDialog: MatDialog, tool: ToolSerializationGroup): MatDialogRef<ConfirmEquipOrUnequipDialog>
  {
    return matDialog.open(ConfirmEquipOrUnequipDialog, {
      data: {
        unequip: tool,
        equip: null,
        location: location
      }
    });
  }

  public static openToEquip(matDialog: MatDialog, tool: MyInventorySerializationGroup): MatDialogRef<ConfirmEquipOrUnequipDialog>
  {
    return matDialog.open(ConfirmEquipOrUnequipDialog, {
      data: {
        unequip: null,
        equip: tool,
        location: location
      }
    });
  }

  protected readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;
}

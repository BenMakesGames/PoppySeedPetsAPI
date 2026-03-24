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

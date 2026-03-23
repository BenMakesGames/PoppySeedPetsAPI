import {Component, Inject, OnInit} from '@angular/core';
import {MyInventorySerializationGroup} from "../../model/my-inventory/my-inventory.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
  standalone: true,
  templateUrl: './choose-tool-and-enchantment.dialog.html',
  styleUrls: ['./choose-tool-and-enchantment.dialog.scss']
})
export class ChooseToolAndEnchantmentDialog implements OnInit {

  tool: MyInventorySerializationGroup;
  bonus: MyInventorySerializationGroup;

  constructor(private dialog: MatDialogRef<ChooseToolAndEnchantmentDialog>, @Inject(MAT_DIALOG_DATA) private data: any)
  {
    this.tool = data.tool;
    this.bonus = data.bonus;
  }

  ngOnInit(): void {

  }

  doSwap()
  {
    const temp = this.tool;
    this.tool = this.bonus;
    this.bonus = temp;
  }

  doCancel()
  {
    this.dialog.close();
  }

  doConfirm()
  {
    this.dialog.close({
      tool: this.tool,
      bonus: this.bonus,
    });
  }

  public static open(
    matDialog: MatDialog, item1: MyInventorySerializationGroup, item2: MyInventorySerializationGroup
  ): MatDialogRef<ChooseToolAndEnchantmentDialog>
  {
    return matDialog.open(ChooseToolAndEnchantmentDialog, {
      data: {
        tool: item1,
        bonus: item2
      }
    });
  }

}

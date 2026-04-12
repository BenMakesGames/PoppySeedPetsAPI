/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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

/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { FormsModule } from "@angular/forms";
import { ApiService } from "../../../shared/service/api.service";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { VaultItem } from "../../model/vault-item";
import { isFeatureUnlocked, MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";

@Component({
    templateUrl: './move-out-item.dialog.html',
    styleUrls: ['./move-out-item.dialog.scss'],
    imports: [
        FormsModule,
        LoadingThrobberComponent,
    ]
})
export class MoveOutItemDialog {

  item: VaultItem;
  user: MyAccountSerializationGroup;

  quantity = 1;
  location = 0;
  working = false;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<MoveOutItemDialog>,
    private api: ApiService
  ) {
    this.item = data.item;
    this.user = data.user;

    if(data.initialLocation != null)
      this.location = data.initialLocation;
  }

  hasFeature(feature: string): boolean {
    return isFeatureUnlocked(this.user, feature);
  }

  clampQuantity() {
    this.quantity = Math.max(1, Math.min(this.item.quantity, this.quantity));
  }

  doCancel() {
    this.dialogRef.close();
  }

  doConfirm() {
    if (this.working) return;

    this.working = true;

    this.api.post('/vault/moveOut', {
      vaultItemId: this.item.id,
      quantity: this.quantity,
      location: this.location,
    }).subscribe({
      next: () => {
        this.dialogRef.close(this.location);
      },
      error: () => {
        this.working = false;
      }
    });
  }

  public static open(matDialog: MatDialog, item: VaultItem, user: MyAccountSerializationGroup, initialLocation: number | null = null): MatDialogRef<MoveOutItemDialog> {
    return matDialog.open(MoveOutItemDialog, {
      data: { item, user, initialLocation }
    });
  }
}

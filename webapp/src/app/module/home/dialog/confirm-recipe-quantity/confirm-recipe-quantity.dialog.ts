import {Component, Inject} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import { KnownRecipeSerializationGroup } from "../../../../model/known-recipe.serialization-group";
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { MessagesService } from "../../../../service/messages.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";

@Component({
    templateUrl: './confirm-recipe-quantity.dialog.html',
    styleUrls: ['./confirm-recipe-quantity.dialog.scss'],
    imports: [CommonModule, FormsModule]
})
export class ConfirmRecipeQuantityDialog {

  quantity = 1;
  cooking = false;
  maxQuantity = 100;
  location: number;

  recipe: KnownRecipeSerializationGroup;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<ConfirmRecipeQuantityDialog>,
    private api: ApiService, private messages: MessagesService
  ) {
    this.recipe = data.recipe;
    this.location = data.location;

    this.recipe.ingredients.forEach(ingredient => {
      this.maxQuantity = Math.min(
        this.maxQuantity,
        Math.floor(ingredient.available / ingredient.quantity)
      );
    });
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doSubmit()
  {
    if(this.cooking) return;

    if(this.quantity == 0)
    {
      this.doCancel();
      return;
    }

    this.cooking = true;

    this.api.post<MyInventorySerializationGroup[]>('/cookingBuddy/prepare/' + this.recipe.id + '/' + this.quantity, { location: this.location }).subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        const message = 'You made ' + r.data.map(i => i.item.name).join(', ') + '!';

        this.messages.addGenericMessage(message);
        this.dialogRef.close(message);
      },
      error: (r: ApiResponseModel<any>) => {
        this.messages.addGenericMessage(r.errors.join(' ') + ' T_T');
        this.cooking = false;
      }
    });
  }

  public static open(matDialog: MatDialog, location: number, recipe: KnownRecipeSerializationGroup): MatDialogRef<ConfirmRecipeQuantityDialog>
  {
    return matDialog.open(ConfirmRecipeQuantityDialog, {
      data: {
        recipe: recipe,
        location: location
      }
    });
  }
}

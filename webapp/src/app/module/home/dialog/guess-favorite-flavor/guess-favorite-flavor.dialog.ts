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
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {FlavorEnum} from "../../../../model/flavor.enum";
import {ApiService} from "../../../shared/service/api.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { FormsModule } from "@angular/forms";

@Component({
    templateUrl: './guess-favorite-flavor.dialog.html',
    styleUrls: ['./guess-favorite-flavor.dialog.scss'],
    imports: [CommonModule, LoadingThrobberComponent, FormsModule]
})
export class GuessFavoriteFlavorDialog implements OnInit {

  pet: MyPetSerializationGroup;

  submitText: string;
  loading = false;

  flavors: string[] = [];
  guess: string;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<GuessFavoriteFlavorDialog>,
    private api: ApiService
  ) {
    this.pet = data.pet;
  }

  ngOnInit(): void {
    for(const flavor in FlavorEnum)
    {
      if(!Number(flavor))
        this.flavors.push(flavor);
    }

    this.flavors.sort();

    const possibleText = [
      'This one?',
      'It\'s gotta\' be!',
      'Probably.',
      'Maybe.',
      'Easy every time!',
      'I got this!'
    ];

    this.submitText = possibleText[Math.floor(Math.random() * possibleText.length)];
  }

  doSubmit()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/pet/' + this.pet.id + '/guessFavoriteFlavor', { flavor: this.guess }).subscribe({
      next: (r) => {
        if(r.data)
          this.dialogRef.close(r.data);
        else
          this.dialogRef.close();
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  public static open(matDialog: MatDialog, pet: MyPetSerializationGroup): MatDialogRef<GuessFavoriteFlavorDialog>
  {
    return matDialog.open(GuessFavoriteFlavorDialog, {
      data: {
        pet: pet
      }
    });
  }
}

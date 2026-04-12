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
import {StoryStepSerializationGroup} from "../../model/story/story-step.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { StoryComponent } from "../../component/story/story.component";

@Component({
    templateUrl: './story.dialog.html',
    imports: [
        StoryComponent
    ],
    styleUrls: ['./story.dialog.scss']
})
export class StoryDialog {

  public storyEndpoint: string;
  public storyStep: StoryStepSerializationGroup;

  constructor(private matDialogRef: MatDialogRef<StoryDialog>, @Inject(MAT_DIALOG_DATA) private data: any)
  {
    this.storyEndpoint = data.storyEndpoint;
  }

  public doClose()
  {
    this.matDialogRef.close();
  }

  public static open(matDialog: MatDialog, storyEndpoint: string): MatDialogRef<StoryDialog>
  {
    return matDialog.open(StoryDialog, {
      data: {
        storyEndpoint: storyEndpoint
      }
    });
  }
}

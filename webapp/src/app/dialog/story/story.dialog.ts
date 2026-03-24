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

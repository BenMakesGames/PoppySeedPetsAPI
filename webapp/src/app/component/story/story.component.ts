/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {UserDataService} from "../../service/user-data.service";
import {ApiService} from "../../module/shared/service/api.service";
import {StoryStepSerializationGroup} from "../../model/story/story-step.serialization-group";
import {ApiResponseModel} from "../../model/api-response.model";
import {StoryStepChoiceSerializationGroup} from "../../model/story/story-step-choice.serialization-group";
import {MyAccountSerializationGroup} from "../../model/my-account/my-account.serialization-group";
import { NpcDialogComponent } from "../../module/shared/component/npc-dialog/npc-dialog.component";
import { MarkdownComponent } from "ngx-markdown";
import { LoadingThrobberComponent } from "../../module/shared/component/loading-throbber/loading-throbber.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-story',
    templateUrl: './story.component.html',
    imports: [
        NpcDialogComponent,
        MarkdownComponent,
        LoadingThrobberComponent,
        CommonModule
    ],
    styleUrls: ['./story.component.scss']
})
export class StoryComponent implements OnInit {

  @Input() removeNegativeMargins = false;
  @Input() smallerNPCImage = false;
  @Input() endpoint: string;
  @Output('storyStep') step = new EventEmitter<StoryStepSerializationGroup>();
  @Output() exit = new EventEmitter();

  public choosing = false;
  public storyStep: StoryStepSerializationGroup;
  public storyStepContent: string;
  public choiceText: string[];
  private user: MyAccountSerializationGroup;

  constructor(private userData: UserDataService, private api: ApiService) {
    this.user = this.userData.user.getValue();
  }

  ngOnInit() {
    this.api.post<StoryStepSerializationGroup>(this.endpoint).subscribe({
      next: (r: ApiResponseModel<StoryStepSerializationGroup>) => {
        this.processResponse(r.data);
      }
    })
  }

  private processResponse(data: StoryStepSerializationGroup)
  {
    this.storyStep = data;
    this.formatStoryStepContent();
    this.step.emit(this.storyStep);
  }

  private formatStoryStepContent()
  {
    this.storyStepContent = this.storyStep.content
      .replace('%user.name%', this.user.name)
      .replace('%user.moneys%', this.user.moneys + '~~m~~')
    ;

    this.choiceText = this.storyStep.choices.map(s => s.text
      .replace('%user.name%', this.user.name)
      .replace('%user.moneys%', this.user.moneys + '~~m~~')
    );
  }

  doMakeChoice(choice: StoryStepChoiceSerializationGroup)
  {
    if(this.choosing) return;

    this.choosing = true;

    this.api.post<StoryStepSerializationGroup>(this.endpoint, { choice: choice.text }).subscribe({
      next: (r: ApiResponseModel<StoryStepSerializationGroup>) => {
        this.processResponse(r.data);

        if(choice.exitOnSelect)
          this.exit.emit();
        else
          this.choosing = false;
      },
      error: () => {
        this.choosing = false;
      }
    })
  }
}

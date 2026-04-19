/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { Subscription } from "rxjs";
import { ApiService } from "../../../shared/service/api.service";
import { ActivatedRoute } from "@angular/router";
import { AssembleTeamDialog } from "../../dialog/assemble-team/assemble-team.dialog";
import { MissionResultsDialog } from "../../dialog/mission-results/mission-results.dialog";
import { MatDialog } from "@angular/material/dialog";
import { ResetRemixDialog } from "../../dialog/reset-remix/reset-remix.dialog";

@Component({
    selector: 'app-view',
    templateUrl: './view.component.html',
    styleUrls: ['./view.component.scss'],
    standalone: false
})
export class ViewComponent implements OnInit {

  @ViewChild('Spotlight', { static: false }) spotlight: ElementRef|undefined;

  @HostListener('document:mousemove', ['$event'])
  mouseMove(event: any) {
    this.moveSpotlight(event.clientX, event.clientY);
  }

  @HostListener('document:touchstart', ['$event'])
  touchStart(event: any) {
    this.moveSpotlight(event.touches[0].clientX, event.touches[0].clientY);
  }

  @HostListener('document:touchmove', ['$event'])
  touchMove(event: any) {
    this.moveSpotlight(event.touches[0].clientX, event.touches[0].clientY);
  }

  moveSpotlight(x: number, y: number)
  {
    if(this.spotlight) {
      this.spotlight.nativeElement.style.maskImage = `radial-gradient(min(max(25vw, 25vh), 200px) at ${x}px ${y}px, transparent 100%, black 100%)`;
      this.spotlight.nativeElement.style['-webkit-mask-image'] = `radial-gradient(min(max(25vw, 25vh), 200px) at ${x}px ${y}px, transparent 100%, black 100%)`;
    }
  }

  paramSubscription = Subscription.EMPTY;
  storySubscription = Subscription.EMPTY;

  storyId: string|null = null;
  story: StoryDto|null = null;

  availableSteps: AvailableStoryStepModel[] = [];
  completedSteps: CompletedDto[] = [];
  canAdventure = false;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute,
    private matDialog: MatDialog
  ) {
  }

  ngOnInit(): void {
    this.paramSubscription = this.activatedRoute.paramMap.subscribe(params => {
      this.storyId = params.get('storyId');

      this.loadStory();
    });
  }

  ngOnDestroy()
  {
    this.storySubscription.unsubscribe();
    this.paramSubscription.unsubscribe();
  }

  doAssignToMission(step: AvailableStoryStepModel)
  {
    if(!this.story) return;

    AssembleTeamDialog.open(this.matDialog, step).afterClosed().subscribe({
      next: results => {
        if(!results)
          return;

        MissionResultsDialog.open(this.matDialog, step.title, results.text);

        this.loadStory();
      }
    });
  }

  doShowStory(story: CompletedDto)
  {
    MissionResultsDialog.open(this.matDialog, story.adventureStep.title, story.adventureStep.narrative);
  }

  loadStory()
  {
    this.storySubscription.unsubscribe();

    this.storySubscription = this.api.get<ResponseDto>('/starKindred/' + this.storyId).subscribe({
      next: r => {
        this.story = r.data.story;
        this.availableSteps = r.data.stepsAvailable
          .map(a => this.addPinDirection(a))
        ;
        this.completedSteps = r.data.stepsComplete;

        this.canAdventure = Date.now() >= Date.parse(r.data.canNextPlayOn);

        if(this.availableSteps.length === 0 && r.data.story.isREMIX)
        {
          ResetRemixDialog.open(this.matDialog, this.storyId).afterClosed().subscribe(
            reload => {
              if(reload) this.loadStory()
            }
          );
        }
      }
    });
  }

  addPinDirection(s: AvailableStoryStepModel)
  {
    if(s.pinOverride)
      return s;
    else
      return { ...s, pinOverride: this.pinDirection(s.x, s.y) };
  }

  pinDirection(x: number, y: number): string
  {
    if(x < 15)
      return 'Left';

    if(x >= 85)
      return 'Right';

    if(y >= 50)
      return 'Bottom';

    return 'Top';
  }
}

interface ResponseDto
{
  story: StoryDto;
  stepsAvailable: AvailableStoryStepModel[];
  stepsComplete: CompletedDto[];
  canNextPlayOn: string;
}

interface StoryDto
{
  releaseYear: number;
  releaseMonth: number;
  isDark: boolean;
  isREMIX: boolean;
}

interface CompletedDto
{
  adventureStep: {
    id: number,
    title: string,
    type: string,
    x: number,
    y: number,
    narrative: string,
  };
  completedOn: string;
}

interface AvailableStoryStepModel
{
  id: number;
  title: string;
  type: string;
  x: number;
  y: number;
  minPets: number;
  maxPets: number;
  pinOverride: string;
}
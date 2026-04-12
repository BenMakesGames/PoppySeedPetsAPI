/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {TrickOrTreaterSerializationGroup} from "../../model/trick-or-treater.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {PetPublicProfileSerializationGroup} from "../../../../model/public-profile/pet-public-profile.serialization-group";
import {Router} from "@angular/router";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {interval, Subscription} from "rxjs";

@Component({
    selector: 'app-trick-or-treater',
    templateUrl: './trick-or-treater.component.html',
    styleUrls: ['./trick-or-treater.component.scss'],
    standalone: false
})
export class TrickOrTreaterComponent implements OnInit, OnDestroy {

  loading = true;
  givingCandy = false;
  trickOrTreater: PetPublicProfileSerializationGroup;
  candy: MyInventorySerializationGroup[];
  errors: string[];
  timeRemaining: string;
  intervalSubscription = Subscription.EMPTY;
  nextTrickOrTreater: number;
  sillyMessage: string;
  trickOrTreaterAjax = Subscription.EMPTY;
  totalCandyGiven: number;
  badTaste = false;
  selected: MyInventorySerializationGroup;

  milestones = [
    1, 3, 8, 15, 25, 40, 60
  ];

  private static readonly possibleSillyMessages = [
    'But of course, there\'s only one right choice...',
    'It\'s like that phrase has meaning beyond the individual words that make it up... (I believe they call that an "idiom".)',
    'But have you ever actually met anyone who chooses "trick"?',
    'I hope you get some nice treats!',
    'It\'s weird that every trick-or-treater waits exactly 15 minutes after the last leaves, right??',
    'I mean... wine isn\'t food, so... it\'s candy, right?',
  ];

  constructor(private api: ApiService, private router: Router) { }

  ngOnInit() {
    this.sillyMessage = TrickOrTreaterComponent.possibleSillyMessages[Math.floor(Math.random() * TrickOrTreaterComponent.possibleSillyMessages.length)];

    this.load();
  }

  ngOnDestroy()
  {
    this.intervalSubscription.unsubscribe();
    this.trickOrTreaterAjax.unsubscribe();
  }

  private load()
  {
    this.trickOrTreaterAjax = this.api.get<TrickOrTreaterSerializationGroup>('/halloween/trickOrTreater').subscribe({
      next: (r: ApiResponseModel<TrickOrTreaterSerializationGroup>) => {
        this.trickOrTreater = r.data.trickOrTreater;
        this.candy = r.data.candy;
        this.loading = false;
        this.totalCandyGiven = r.data.totalCandyGiven;

        if(!this.trickOrTreater && r.data.nextTrickOrTreater)
        {
          // Safari is REALLY PICKY, and wants a "T" to separate the date from the time:
          this.nextTrickOrTreater = Date.parse(r.data.nextTrickOrTreater.replace(' ', 'T') + 'Z');

          this.intervalSubscription.unsubscribe(); // probably needless? just being safe...
          this.intervalSubscription = interval(1000).subscribe(() => {
            this.intervalTick();
          });
        }
      },
      error: (r: ApiResponseModel<void>) => {
        this.errors = r.errors;
        this.loading = false;
      }
    });
  }

  private intervalTick()
  {
    const secondsRemaining = Math.ceil((this.nextTrickOrTreater - Date.now()) / 1000);

    if(secondsRemaining >= 60)
    {
      const minutesRemaining = Math.floor(secondsRemaining / 60);

      this.timeRemaining = minutesRemaining + ' minute' + (minutesRemaining === 1 ? '' : 's') + ' and ' + (secondsRemaining % 60) + ' second' + (secondsRemaining === 1 ? '' : 's');

      return;
    }

    if(secondsRemaining > 0)
    {
      this.timeRemaining = secondsRemaining + ' second' + (secondsRemaining === 1 ? '' : 's');
      return;
    }

    this.intervalSubscription.unsubscribe();

    this.load();
  }

  doSelectCandy(candy: MyInventorySerializationGroup)
  {
    if(this.selected === candy)
      this.selected = null;
    else
      this.selected = candy;
  }

  doGiveCandy(toGivingTree: boolean)
  {
    if(this.givingCandy || !this.selected) return;

    this.givingCandy = true;

    this.api.post('/halloween/trickOrTreater/giveCandy', { candy: this.selected.id, toGivingTree: toGivingTree }).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.givingCandy = false;
      }
    });
  }
}

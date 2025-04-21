import {Component, EventEmitter, forwardRef, Input, Output} from '@angular/core';
import { PetChallenge, AdventureComponent } from "../adventure/adventure.component";
import { FormsModule } from '@angular/forms';
import { NgFor } from '@angular/common';

@Component({
  selector: 'app-adventure-pet-challenge',
  templateUrl: './adventure-pet-challenge.component.html',
  styleUrl: './adventure-pet-challenge.component.scss',
  standalone: true,
  imports: [FormsModule, NgFor, forwardRef(() => AdventureComponent)]
})
export class AdventurePetChallengeComponent {
  @Input() adventure: PetChallenge;
  @Output() adventureChange = new EventEmitter<PetChallenge>();

  allStatsAndSkills = [
    'strength', 'stamina', 'dexterity', 'perception', 'intelligence',
    'arcana', 'brawl', 'crafts', 'music', 'nature', 'science', 'stealth'
  ];

  doToggleStat(stat: string)
  {
    if(this.adventure.stats.includes(stat))
      this.adventure.stats.splice(this.adventure.stats.indexOf(stat), 1);
    else
      this.adventure.stats.push(stat);

    this.doUpdateAdventure();
  }

  doUpdateAdventure()
  {
    this.adventureChange.emit(this.adventure);
  }
}

import {Component, EventEmitter, Input, Output} from '@angular/core';
import {Onward} from "../adventure/adventure.component";
import {randomItemName} from "../../../helpful-functions";
import { FormsModule } from '@angular/forms';
import { NgIf, NgFor } from '@angular/common';

@Component({
  selector: 'app-adventure-onward',
  templateUrl: './adventure-onward.component.html',
  styleUrl: './adventure-onward.component.scss',
  standalone: true,
  imports: [FormsModule, NgIf, NgFor]
})
export class AdventureOnwardComponent {
  @Input() adventure: Onward;
  @Output() adventureChange = new EventEmitter<Onward>();

  allSkills = [
    'arcana', 'brawl', 'crafts', 'music', 'nature', 'science', 'stealth'
  ];

  doUpdateAdventure()
  {
    [ 'food', 'safety', 'love', 'esteem' ].forEach(need => {
      if(need in this.adventure && (this.adventure[need] == 0 || this.adventure[need] == null))
        delete this.adventure[need];
    });

    this.adventureChange.emit(this.adventure);
  }

  doTogglePetNeed(need: string)
  {
    if(need in this.adventure)
      delete this.adventure[need];
    else
      this.adventure[need] = 4;

    this.doUpdateAdventure();
  }

  doToggleExp()
  {
    if('exp' in this.adventure)
      delete this.adventure.exp;
    else
      this.adventure.exp = { amount: 1, stats: [] };

    this.doUpdateAdventure();
  }

  doToggleItems()
  {
    if('receiveItems' in this.adventure)
      delete this.adventure.receiveItems;
    else
      this.adventure.receiveItems = [ randomItemName() ];

    this.doUpdateAdventure();
  }

  doToggleStat(stat: string)
  {
    if(this.adventure.exp.stats.includes(stat))
      this.adventure.exp.stats.splice(this.adventure.exp.stats.indexOf(stat), 1);
    else
      this.adventure.exp.stats.push(stat);

    this.doUpdateAdventure();
  }

  doAddItem()
  {
    this.adventure.receiveItems.push(randomItemName());

    this.doUpdateAdventure();
  }

  doRemoveItem(index: number)
  {
    this.adventure.receiveItems.splice(index, 1);

    if(this.adventure.receiveItems.length == 0)
      delete this.adventure.receiveItems;

    this.doUpdateAdventure();
  }

  doUpdateItemName(index: number, event)
  {
    this.adventure.receiveItems[index] = event.target.value;

    this.doUpdateAdventure();
  }
}

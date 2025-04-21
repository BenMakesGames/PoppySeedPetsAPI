import {Component, EventEmitter, Input, OnChanges, Output, SimpleChanges} from '@angular/core';
import {randomItemName} from "../../../helpful-functions";
import { FormsModule } from '@angular/forms';
import { AdventureChooseOneComponent } from '../adventure-choose-one/adventure-choose-one.component';
import { AdventureMoveToComponent } from '../adventure-move-to/adventure-move-to.component';
import { AdventureOnwardComponent } from '../adventure-onward/adventure-onward.component';
import { AdventurePayItemComponent } from '../adventure-pay-item/adventure-pay-item.component';
import { AdventurePayMoneyComponent } from '../adventure-pay-money/adventure-pay-money.component';
import { AdventurePayItemAndMoneyComponent } from '../adventure-pay-item-and-money/adventure-pay-item-and-money.component';
import { AdventurePetChallengeComponent } from '../adventure-pet-challenge/adventure-pet-challenge.component';
import { AdventureIconPipe } from '../../pipe/adventure-icon.pipe';

@Component({
  selector: 'app-adventure',
  templateUrl: './adventure.component.html',
  styleUrl: './adventure.component.scss',
  standalone: true,
  imports: [FormsModule, AdventureChooseOneComponent, AdventureMoveToComponent, AdventureOnwardComponent, AdventurePayItemComponent, AdventurePayMoneyComponent, AdventurePayItemAndMoneyComponent, AdventurePetChallengeComponent, AdventureIconPipe]
})
export class AdventureComponent implements OnChanges {
  @Input() adventure: Adventure;
  @Output() adventureChange = new EventEmitter<Adventure>();

  @Input() hasParents = true;

  isOpen = true;
  adventureColor = '#ccc';

  ngOnChanges(changes: SimpleChanges) {
    if(changes['adventure'])
    {
      this.adventureColor = this.getAdventureColor(this.adventure);
    }
  }

  doCollapse()
  {
    this.isOpen = !this.isOpen;
  }

  doUpdateAdventure(adventure: Adventure)
  {
    this.adventure = { ... adventure };
    this.adventureColor = this.getAdventureColor(this.adventure);
    this.adventureChange.emit(this.adventure);
  }

  getAdventureColor(adventure: Adventure)
  {
    switch(adventure.type)
    {
      case 'onward':
        return '#43b041';

      case 'chooseOne':
        return '#7db7ff';

      case 'payItem?':
        return '#ae7d24';

      case 'payMoneys?':
        return '#db0';

      case 'payItemAndMoneys?':
        return '#ccc';

      case 'petChallenge':
        return '#b04141';

      default:
        return '#ccc';
    }
  }

  doChangeAdventureType()
  {
    this.adventure = { ... this.adventure };
    this.adventureColor = this.getAdventureColor(this.adventure);

    switch(this.adventure.type)
    {
      case 'onward':
        this.adventure = <Onward>{
          type: 'onward',
          description: this.adventure.description,
          buttonText: 'Onward!',
        };
        break;

      case 'chooseOne':
        this.adventure = <ChooseOne>{
          type: 'chooseOne',
          description: this.adventure.description,
          buttons: ['???', '???'],
          outcomes: [
            <Onward>{
              type: 'onward',
              description: '???',
              buttonText: 'Onward!',
            },
            <Onward>{
              type: 'onward',
              description: '???',
              buttonText: 'Onward!',
            }
          ]
        };
        break;

      /*
      case 'moveTo':
        this.adventure = <MoveTo>{
          type: 'moveTo',
          description: this.adventure.description,
          buttonText: 'Onward!',
          id: 0
        };
        break;
        */

      case 'payItem?':
        this.adventure = <PayItem>{
          type: 'payItem?',
          description: this.adventure.description,
          item: randomItemName(),
          ifPaid: <Onward>{
            type: 'onward',
            description: '???',
            buttonText: 'Onward!',
          }
        };
        break;

      case 'payMoneys?':
        this.adventure = <PayMoneys>{
          type: 'payMoneys?',
          description: this.adventure.description,
          amount: 1,
          ifPaid: <Onward>{
            type: 'onward',
            description: '???',
            buttonText: 'Onward!',
          }
        };
        break;

      case 'payItemAndMoneys?':
        this.adventure = <PayItemAndMoneys>{
          type: 'payItemAndMoneys?',
          description: this.adventure.description,
          amount: 1,
          item: randomItemName(),
          ifPaid: <Onward>{
            type: 'onward',
            description: '???',
            buttonText: 'Onward!',
          }
        };
        break;

      case 'petChallenge':
        this.adventure = <PetChallenge>{
          type: 'petChallenge',
          description: this.adventure.description,
          buttonText: 'Try it!',
          stats: [ 'perception' ],
          baseRoll: 20,
          requiredRoll: 15,
          ifSuccess: <Onward>{
            type: 'onward',
            description: '???',
            buttonText: 'Onward!',
          },
          ifFail: <Onward>{
            type: 'onward',
            description: '???',
            buttonText: 'Onward!',
          }
        };
        break;

      default:
        throw 'bad adventure type!';
    }

    this.adventureChange.emit(this.adventure);
  }
}

export interface Adventure {
  type?: string;
  description: string;
}

export interface Onward extends Adventure
{
  type: 'onward';
  buttonText: string;
  food?: number;
  safety?: number;
  love?: number;
  esteem?: number;
  exp?: { amount: number, stats: string[] };
  receiveItems?: string[];
}

export interface PetChallenge extends Adventure
{
  type: 'petChallenge';
  description: string;
  buttonText: string;
  stats: string[];
  baseRoll: number;
  requiredRoll: number;
  ifSuccess: Adventure;
  ifFail: Adventure;
}

export interface MoveTo extends Adventure
{
  type: 'moveTo';
  description: string;
  buttonText: string;
  id: number;
}

export interface PayItem extends Adventure
{
  type: 'payItem?';
  description: string;
  item: string;
  ifPaid: Adventure;
  ifNotPaid?: Adventure;
}

export interface PayMoneys extends Adventure
{
  type: 'payMoneys?';
  description: string;
  amount: number;
  ifPaid: Adventure;
  ifNotPaid?: Adventure;
}

export interface PayItemAndMoneys extends Adventure
{
  type: 'payItemAndMoneys?';
  description: string;
  amount: number;
  item: string;
  ifPaid: Adventure;
  ifNotPaid?: Adventure;
}

export interface ChooseOne extends Adventure
{
  type: 'chooseOne';
  description: string;
  buttons: string[];
  outcomes: Adventure[];
}

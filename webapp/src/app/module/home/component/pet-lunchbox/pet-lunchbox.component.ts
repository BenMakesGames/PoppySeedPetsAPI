import {Component, EventEmitter, Input, OnChanges, OnInit, Output} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import { InventoryItemComponent } from "../../../shared/component/inventory-item/inventory-item.component";
import { CommonModule } from "@angular/common";
import { HelpLinkComponent } from "../../../shared/component/help-link/help-link.component";

@Component({
    selector: 'app-pet-lunchbox',
    templateUrl: './pet-lunchbox.component.html',
    styleUrls: ['./pet-lunchbox.component.scss'],
    imports: [
        InventoryItemComponent, CommonModule, HelpLinkComponent
    ]
})
export class PetLunchboxComponent implements OnInit, OnChanges {

  @Input() pet: MyPetSerializationGroup;
  @Input() inventory: MyInventorySerializationGroup[];
  @Output() inventoryChanged = new EventEmitter();

  food: MyInventorySerializationGroup[];
  loading = false;
  noneText = 'None.';

  lunchboxSize = 4;

  constructor(private api: ApiService) { }

  ngOnInit(): void {
    if(Math.random() <= 0.01)
    {
      this.noneText = Math.randomFromList([
        'Zero. Zip. Zilch. Nada.',
        'None. There\'s none food in this lunchbox.',
      ]);
    }

    this.lunchboxSize = this.pet.merits.some(m => m.name === 'Bigger Lunchbox') ? 5 : 4;
  }

  ngOnChanges()
  {
    this.food = this.inventory
      .filter(i => i.item.food !== null)
      .sort((a, b) => a.item.name.localeCompare(b.item.name))
    ;
  }

  doRemoveFood(food: MyInventorySerializationGroup)
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/pet/' + this.pet.id + '/takeOutOfLunchbox/' + food.id).subscribe({
      next: () => {
        this.loading = false;

        this.pet.lunchboxItems = this.pet.lunchboxItems.filter(l => l.inventoryItem.id !== food.id);
        this.food.push(food);

        this.food = this.food.sort((a, b) => a.item.name.localeCompare(b.item.name));

        this.inventoryChanged.emit();
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  doAddFood(food: MyInventorySerializationGroup)
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/pet/' + this.pet.id + '/putInLunchbox/' + food.id).subscribe({
      next: () => {
        this.loading = false;

        food.sellPrice = null;

        this.food = this.food.filter(f => f.id !== food.id);
        this.pet.lunchboxItems.push({ inventoryItem: food });

        this.inventoryChanged.emit();
      },
      error: () => {
        this.loading = false;
      }
    });
  }
}
